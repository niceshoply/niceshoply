<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use NiceShoply\Common\Exceptions\Unauthorized;
use NiceShoply\Common\Models\Checkout;
use NiceShoply\Common\Models\Customer;
use NiceShoply\Common\Repositories\AddressRepo;
use NiceShoply\Common\Repositories\CheckoutRepo;
use NiceShoply\Common\Repositories\Order\FeeRepo;
use NiceShoply\Common\Repositories\Order\HistoryRepo;
use NiceShoply\Common\Repositories\Order\ItemRepo;
use NiceShoply\Common\Repositories\OrderRepo;
use NiceShoply\Common\Resources\AddressListItem;
use NiceShoply\Common\Resources\CheckoutSimple;
use NiceShoply\Common\Services\Checkout\BillingService;
use NiceShoply\Common\Services\Checkout\FeeService;
use NiceShoply\Common\Services\Checkout\ShippingService;
use NiceShoply\Common\Services\Fee\Shipping;
use NiceShoply\Common\Services\Fee\Subtotal;
use NiceShoply\Common\Services\Promotion\CouponService;
use NiceShoply\Common\Services\Promotion\PromotionService;
use NiceShoply\Common\Services\Warehouse\AllocationService;
use Throwable;

class CheckoutService
{
    private static mixed $checkoutService = null;

    protected int $customerID;

    protected string $guestID;

    protected mixed $customer;

    protected array $cartList = [];

    private array $addressList = [];

    private array $feeList = [];

    private ?Checkout $checkout = null;

    private array $checkoutData = [];

    /**
     * 本次结账已应用的折扣明细（促销 + 优惠券），用于落单写流水与核销。
     *
     * @var array<int, array<string, mixed>>
     */
    private array $appliedDiscounts = [];

    /**
     * 是否命中免运费（由促销/优惠券设置，供 Shipping 费用项读取）。
     */
    private bool $freeShipping = false;

    /**
     * @param  int  $customerID
     * @param  string  $guestID
     * @throws Throwable
     */
    public function __construct(int $customerID = 0, string $guestID = '')
    {
        if (system_setting('disable_online_order')) {
            throw new Exception('The online order is disabled.');
        }

        if ($customerID) {
            $this->customerID = $customerID;
        } else {
            $this->customerID = current_customer_id();
        }

        if (empty($this->customerID) && system_setting('login_checkout')) {
            throw new Unauthorized('Please login first');
        }

        $this->customer = Customer::query()->find($this->customerID);

        if ($guestID) {
            $this->guestID = $guestID;
        } else {
            $this->guestID = current_guest_id();
        }

        $this->clearGuestAddresses();
    }

    /**
     * @param  int  $customerID
     * @param  string  $guestID
     * @return static
     * @throws Throwable
     */
    public static function getSingleton(int $customerID = 0, string $guestID = ''): static
    {
        if (self::$checkoutService !== null) {
            return self::$checkoutService;
        }

        return self::$checkoutService = new static($customerID, $guestID);
    }

    /**
     * @param  int  $customerID
     * @param  string  $guestID
     * @return static
     * @throws Throwable
     */
    public static function getInstance(int $customerID = 0, string $guestID = ''): static
    {
        return new static($customerID, $guestID);
    }

    /**
     * Get current cart item list.
     *
     * @return array
     */
    public function getCartList(): array
    {
        if ($this->cartList) {
            return $this->cartList;
        }

        $filters = [
            'selected' => true,
        ];

        $cartService = CartService::getInstance($this->customerID, $this->guestID);

        return $this->cartList = $cartService->getCartList($filters);
    }

    /**
     * @return bool
     */
    public function checkIsVirtual(): bool
    {
        $cartList = $this->getCartList();
        foreach ($cartList as $product) {
            if (! $product['is_virtual']) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getCartWeight(): mixed
    {
        $weightTotal = 0;
        $cartList    = $this->getCartList();
        foreach ($cartList as $product) {
            $weightTotal += $product['weight'] * $product['quantity'];
        }

        return $weightTotal;
    }

    /**
     * Get current address list.
     *
     * @return array
     */
    public function getAddressList(): array
    {
        if ($this->addressList) {
            return $this->addressList;
        }

        // For logged-in users, only query by customer_id
        // For guest users, query by guest_id
        $filters = [];
        if ($this->customerID > 0) {
            $filters['customer_id'] = $this->customerID;
        } else {
            $filters['guest_id'] = $this->guestID;
        }

        $addresses = AddressRepo::getInstance()->builder($filters)->get();

        return $this->addressList = (AddressListItem::collection($addresses))->jsonSerialize();
    }

    /**
     * @return mixed
     */
    public function getDefaultAddress(): array
    {
        $addressList = $this->getAddressList();
        if (empty($addressList)) {
            return [];
        }

        $defaultAddress = collect($addressList)->where('default', 1)->first();

        return $defaultAddress ?: $addressList[0];
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function getShippingAddress(): array
    {
        $filters = [
            'customer_id' => $this->customerID,
            'guest_id'    => $this->guestID,
        ];
        $checkout = CheckoutRepo::getInstance()->builder($filters)->first();
        $address  = $checkout->shippingAddress ?? null;
        if (empty($address)) {
            return $this->getDefaultAddress();
        }

        return $address->toArray();
    }

    /**
     * @return float
     */
    public function getSubTotal(): float
    {
        return (new Subtotal($this))->getSubtotal();
    }

    /**
     * Get fee list.
     *
     * @return array
     * @throws Exception
     */
    public function getFeeList(): array
    {
        if ($this->feeList) {
            return $this->feeList;
        }

        FeeService::getInstance($this)->calculate();
        if (empty($this->feeList)) {
            throw new Exception('Empty checkout fee list !');
        }

        return $this->feeList;
    }

    /**
     * @param  array  $fee
     * @return $this
     */
    public function addFeeList(array $fee): static
    {
        $this->feeList[] = $fee;

        return $this;
    }

    /**
     * 获取当前客户模型（可能为游客 null）。
     *
     * @return mixed
     */
    public function getCustomer(): mixed
    {
        return $this->customer;
    }

    /**
     * 获取当前客户ID（0 表示游客）。
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return $this->customerID;
    }

    /**
     * 标记本次结账免运费（由促销/优惠券触发）。
     *
     * @return void
     */
    public function markFreeShipping(): void
    {
        $this->freeShipping = true;
    }

    /**
     * 本次结账是否免运费。
     *
     * @return bool
     */
    public function isFreeShipping(): bool
    {
        return $this->freeShipping;
    }

    /**
     * 记录一条已应用的折扣明细（促销/优惠券费用项计算时回填）。
     *
     * @param  array  $entry
     * @return void
     */
    public function recordAppliedDiscount(array $entry): void
    {
        $this->appliedDiscounts[] = $entry;
    }

    /**
     * 获取已应用的折扣明细。
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAppliedDiscounts(): array
    {
        return $this->appliedDiscounts;
    }

    /**
     * 小计扣除「已应用折扣」后的剩余可抵额（下限 0）。
     *
     * 供优惠券费用项封顶使用，避免促销叠加券后出现负数小计。
     *
     * @return float
     */
    public function getDiscountedSubtotalRemaining(): float
    {
        $subtotal = $this->getSubTotal();
        $applied  = collect($this->appliedDiscounts)->sum(fn ($entry) => (float) ($entry['amount'] ?? 0));

        return max(0.0, round($subtotal - $applied, currency_decimal_place()));
    }

    /**
     * 读取已应用到结账的券码（存于 checkout.reference）。
     *
     * @return string
     * @throws Throwable
     */
    public function getAppliedCouponCode(): string
    {
        $reference = $this->getCheckout()->reference ?? [];

        return (string) ($reference['coupon_code'] ?? '');
    }

    /**
     * 应用券码：先校验，通过后写入 checkout.reference 持久化。
     *
     * @param  string  $code
     * @return array{valid: bool, message: string, discount: float, free_shipping: bool}
     * @throws Throwable
     */
    public function applyCoupon(string $code): array
    {
        $result = CouponService::getInstance()->validate($code, $this);

        if ($result['valid']) {
            $checkout                 = $this->getCheckout();
            $reference                = $checkout->reference ?? [];
            $reference['coupon_code'] = strtoupper(trim($code));
            $this->updateValues(['reference' => $reference]);
        }

        return [
            'valid'         => $result['valid'],
            'message'       => $result['message'],
            'discount'      => $result['discount'],
            'free_shipping' => $result['free_shipping'],
        ];
    }

    /**
     * 移除已应用的券码。
     *
     * @return void
     * @throws Throwable
     */
    public function removeCoupon(): void
    {
        $checkout  = $this->getCheckout();
        $reference = $checkout->reference ?? [];
        unset($reference['coupon_code']);
        $this->updateValues(['reference' => $reference]);
    }

    /**
     * 应用积分抵现：校验通过后写入 checkout.reference。
     *
     * @return array{valid: bool, message: string, points: int, amount: float}
     * @throws Throwable
     */
    public function applyPoints(int $points): array
    {
        $result = \NiceShoply\Common\Services\Member\PointService::getInstance()->validateRedeem($this, $points);

        if ($result['valid']) {
            $checkout                   = $this->getCheckout();
            $reference                  = $checkout->reference ?? [];
            $reference['points_to_use'] = $result['points'];
            $this->updateValues(['reference' => $reference]);
        }

        return $result;
    }

    /**
     * 移除已应用的积分抵现。
     *
     * @return void
     * @throws Throwable
     */
    public function removePoints(): void
    {
        $checkout  = $this->getCheckout();
        $reference = $checkout->reference ?? [];
        unset($reference['points_to_use']);
        $this->updateValues(['reference' => $reference]);
    }

    /**
     * 读取已应用积分数量。
     *
     * @return int
     * @throws Throwable
     */
    public function getAppliedPoints(): int
    {
        $reference = $this->getCheckout()->reference ?? [];

        return (int) ($reference['points_to_use'] ?? 0);
    }

    /**
     * @return float
     * @throws Exception
     */
    public function getAmount(): float
    {
        $feeList = $this->getFeeList();
        $decimal = currency_decimal_place();

        return round(collect($feeList)->sum('total'), $decimal);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function getTotalNumber(): int
    {
        $cartList = $this->getCartList();

        return collect($cartList)->sum('quantity');
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function getCheckoutData(): array
    {
        if ($this->checkoutData) {
            $this->validateCheckoutData();

            return $this->checkoutData;
        }

        return $this->checkoutData = $this->freshCheckoutData();
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function freshCheckoutData(): array
    {
        $checkout     = $this->getCheckout();
        $checkoutData = (new CheckoutSimple($checkout))->jsonSerialize();

        $checkoutData['shipping_quote_name'] = Shipping::getInstance($this)->getShippingQuoteName($checkout->shipping_method_code);

        return $checkoutData;
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function validateCheckoutData(): void
    {
        $checkout              = $this->getCheckout();
        $skipAddressValidation = ($checkout->reference['skip_address_validation'] ?? false) && $this->checkIsVirtual();

        // Skip address validation for virtual product quick checkout
        if ($skipAddressValidation) {
            $billingMethods = BillingService::getInstance()->getMethods();
            $billingCodes   = collect($billingMethods)->pluck('code')->toArray();
            if (! in_array($this->checkoutData['billing_method_code'], $billingCodes)) {
                $this->updateValues(['billing_method_code' => $billingMethods[0]['code'] ?? '']);
            }
            $this->checkoutData = $this->freshCheckoutData();

            return;
        }

        $shippingService    = ShippingService::getInstance()->setCheckoutService($this);
        $shippingMethods    = $shippingService->getMethods();
        $shippingQuoteCodes = $shippingService->getQuoteCodes();
        $defaultAddress     = $this->getDefaultAddress();

        if (! in_array($this->checkoutData['shipping_method_code'], $shippingQuoteCodes) && $defaultAddress) {
            $defaultShippingCode = $shippingMethods[0]['quotes'][0]['code'] ?? '';
            $this->updateValues(['shipping_method_code' => $defaultShippingCode]);
        }

        $billingMethods = BillingService::getInstance()->getMethods();
        $billingCodes   = collect($billingMethods)->pluck('code')->toArray();
        if (! in_array($this->checkoutData['billing_method_code'], $billingCodes)) {
            $this->updateValues(['billing_method_code' => $billingMethods[0]['code'] ?? '']);
        }

        $addressList = $this->getAddressList();
        if (! collect($addressList)->contains('id', $this->checkoutData['shipping_address_id'])) {
            $this->updateValues(['shipping_address_id' => 0]);
        }

        if (! collect($addressList)->contains('id', $this->checkoutData['billing_address_id'])) {
            $this->updateValues(['billing_address_id' => 0]);
        }

        $this->checkoutData = $this->freshCheckoutData();
    }

    /**
     * @return Checkout|null
     * @throws Throwable
     */
    public function getCheckout(): ?Checkout
    {
        if ($this->checkout) {
            return $this->checkout;
        }

        $data = [
            'customer_id' => $this->customerID,
            'guest_id'    => $this->guestID,
        ];
        $checkout = CheckoutRepo::getInstance()->builder($data)->first();

        if (empty($checkout)) {
            $checkout = $this->createCheckout($data);
        }

        return $this->checkout = $checkout;
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function createCheckout($data): mixed
    {
        $shippingMethods = $billingMethods = [];

        $defaultAddress   = $this->getDefaultAddress();
        $defaultAddressID = $defaultAddress['id'] ?? 0;

        if ($defaultAddressID) {
            $shippingMethods = ShippingService::getInstance()->setCheckoutService($this)->getMethods();
            $billingMethods  = BillingService::getInstance()->getMethods();
        }

        $data['shipping_address_id']  = $defaultAddressID;
        $data['shipping_method_code'] = $shippingMethods[0]['quotes'][0]['code'] ?? '';
        $data['billing_address_id']   = $defaultAddressID;
        $data['billing_method_code']  = $billingMethods[0]['code'] ?? '';

        return CheckoutRepo::getInstance()->create($data);
    }

    /**
     * Get checkout result.
     *
     * @return array
     * @throws Exception|Throwable
     */
    public function getCheckoutResult(): array
    {
        $this->checkCartStockEnough();

        $cartAmount    = $this->getAmount();
        $balanceAmount = $this->getBalanceAmount();

        $result = [
            'cart_list'             => $this->getCartList(),
            'address_list'          => $this->getAddressList(),
            'shipping_methods'      => ShippingService::getInstance()->setCheckoutService($this)->getMethods(),
            'billing_methods'       => BillingService::getInstance()->getMethods(),
            'checkout'              => $this->getCheckoutData(),
            'fee_list'              => $this->getFeeList(),
            'amount'                => $cartAmount,
            'amount_format'         => currency_format($cartAmount),
            'total_number'          => $this->getTotalNumber(),
            'is_virtual'            => $this->checkIsVirtual(),
            'balance_amount'        => $this->getBalanceAmount(),
            'balance_amount_format' => currency_format($balanceAmount, setting_currency_code()),
        ];

        // Add warehouse allocation preview if enabled
        if (system_setting('warehouse_enabled', false)) {
            $skuQuantities = [];
            foreach ($this->getCartList() as $item) {
                $skuQuantities[] = ['sku_code' => $item['sku_code'], 'quantity' => $item['quantity']];
            }
            $destAddress          = $this->getShippingAddress();
            $allocation           = AllocationService::getInstance()->preview($skuQuantities, $destAddress);
            $result['allocation'] = $allocation->toArray();
        }

        return fire_hook_filter('service.checkout.checkout.result', $result);
    }

    /**
     * @return float
     */
    public function getBalanceAmount(): float
    {
        return (float) ($this->customer->balance ?? 0);
    }

    /**
     * @return void
     */
    private function clearGuestAddresses(): void
    {
        AddressRepo::getInstance()->clearExpiredAddresses();
    }

    /**
     * @param  $values
     * @return mixed
     * @throws Throwable
     */
    public function updateValues($values): mixed
    {
        $checkout = $this->getCheckout();

        // If skip address validation flag is set, save it to checkout's reference
        if (isset($values['skip_address_validation']) && $values['skip_address_validation']) {
            $reference                            = $checkout->reference ?? [];
            $reference['skip_address_validation'] = true;
            $values['reference']                  = $reference;
        }

        return CheckoutRepo::getInstance()->update($checkout, $values);
    }

    /**
     * Check if all cart items have enough stock
     *
     * @throws Exception
     */
    protected function checkCartStockEnough(): void
    {
        $cartList = $this->getCartList();
        foreach ($cartList as $item) {
            if (isset($item['is_stock_enough']) && ! $item['is_stock_enough']) {
                throw new Exception(trans('front/common.stock_not_enough'));
            }
        }
    }

    /**
     * Quick confirm checkout for virtual products (skip address validation)
     * This is the core method for virtual product quick checkout
     *
     * @param  array  $checkoutData
     * @return mixed
     * @throws Exception|Throwable
     */
    public function quickConfirmVirtualProduct(array $checkoutData = []): mixed
    {
        // Verify that all products in cart are virtual products
        if (! $this->checkIsVirtual()) {
            throw new Exception('Only virtual products support quick checkout');
        }

        // Update checkout data (skip address validation)
        if (! empty($checkoutData)) {
            $checkoutData['skip_address_validation'] = true;
            $this->updateValues($checkoutData);
        }

        // Confirm order
        return $this->confirm();
    }

    /**
     * Confirm checkout and place order.
     *
     * @return mixed
     * @throws Exception|Throwable
     */
    public function confirm(): mixed
    {
        $this->checkCartStockEnough();

        // 下单频控（客户/IP 窗口内次数限制）
        \NiceShoply\Common\Services\Compliance\LoginSecurityService::getInstance()
            ->assertOrderRateAllowed($this->customerID, (string) request()->ip());

        DB::beginTransaction();

        try {
            $checkoutData = $this->getCheckoutData();

            $checkoutData['total'] = $this->getAmount();

            $order = OrderRepo::getInstance()->create($checkoutData);

            ItemRepo::getInstance()->createItems($order, $this->cartList);
            FeeRepo::getInstance()->createItems($order, $this->feeList);
            HistoryRepo::getInstance()->initItem($order);

            // 促销/优惠券：事务内落库应用流水并核销（含并发不超发复核）
            $this->persistAppliedDiscounts($order);

            // 跨境：写入目的国税费快照与形式发票号
            $this->applyCrossBorderOrderMeta($order, $checkoutData);

            // Warehouse allocation: create shipments and reserve stock
            if (system_setting('warehouse_enabled', false)) {
                $this->allocateWarehouseStock($order);
            }

            DB::commit();

            $data = [
                'cart_list' => $this->getCartList(),
                'checkout'  => $checkoutData,
                'order'     => $order,
            ];
            fire_hook_action('service.checkout.confirm.after', $data);

            // 领域事件：与 Hook 互补，供应用层异步监听（通知 / 统计 / 风控等）
            event(new \NiceShoply\Common\Events\OrderPlaced($order));

            $this->checkout->delete();
            CartService::getInstance($this->customerID)->getCartBuilder(['selected' => true])->delete();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * 落库本次结账应用的促销/优惠券（订单事务内）。
     *
     * - 促销：写 nice_promotion_order_logs 并原子累加 used_count；
     * - 优惠券：写核销记录（唯一约束防重）+ 原子累加 + 复核上限（不超发）。
     *
     * @param  mixed  $order
     * @return void
     * @throws Exception
     */
    private function persistAppliedDiscounts($order): void
    {
        if (empty($this->appliedDiscounts)) {
            return;
        }

        // 促销流水与用量
        PromotionService::getInstance()->persistOrderLogs($order, $this->appliedDiscounts);

        // 优惠券核销
        foreach ($this->appliedDiscounts as $entry) {
            if (($entry['type'] ?? '') !== 'coupon' || empty($entry['coupon_id'])) {
                continue;
            }

            $coupon = \NiceShoply\Common\Models\Coupon::query()->find($entry['coupon_id']);
            if (! $coupon) {
                continue;
            }

            CouponService::getInstance()->redeem(
                $coupon,
                $order,
                (int) ($entry['customer_id'] ?? $this->customerID),
                (float) ($entry['amount'] ?? 0)
            );
        }
    }

    /**
     * 跨境订单元数据：目的国税费、形式发票号。
     *
     * @param  mixed  $order
     * @param  array  $checkoutData
     * @return void
     */
    private function applyCrossBorderOrderMeta(mixed $order, array $checkoutData): void
    {
        $storeCountry    = (int) system_setting('country_id');
        $shippingCountry = (int) ($checkoutData['shipping_country_id'] ?? 0);
        if (! $shippingCountry || ! $storeCountry || $shippingCountry === $storeCountry) {
            return;
        }

        $taxTotal = (float) collect($this->feeList)->where('code', 'tax')->sum('total');
        $updates  = [];
        if ($taxTotal > 0) {
            $updates['destination_tax_amount'] = $taxTotal;
        }
        if (empty($order->proforma_number)) {
            $updates['proforma_number'] = 'PF-'.$order->number;
        }

        if ($updates) {
            $order->update($updates);
        }
    }

    /**
     * Allocate warehouse stock and create shipment records for the order.
     *
     * @param  $order
     * @return void
     * @throws Exception
     */
    private function allocateWarehouseStock($order): void
    {
        $skuQuantities = [];
        foreach ($this->cartList as $item) {
            $skuQuantities[] = [
                'sku_code' => $item['sku_code'],
                'quantity' => $item['quantity'],
            ];
        }

        $destAddress = $this->getShippingAddress();
        $result      = AllocationService::getInstance()->allocate($order, $skuQuantities, $destAddress);

        // Create shipment records for each warehouse package
        $order->loadMissing('items');
        foreach ($result->allocations as $warehouseId => $items) {
            $warehouse = $result->warehouseGroups[$warehouseId] ?? null;
            $shipment  = $order->shipments()->create([
                'warehouse_id'   => $warehouseId,
                'warehouse_name' => $warehouse->name ?? '',
                'status'         => 'pending',
            ]);

            foreach ($items as $allocItem) {
                $orderItem = $order->items->firstWhere('product_sku', $allocItem['sku_code']);
                $shipment->items()->create([
                    'order_item_id' => $orderItem->id ?? 0,
                    'sku_code'      => $allocItem['sku_code'],
                    'quantity'      => $allocItem['quantity'],
                ]);
            }
        }
    }
}
