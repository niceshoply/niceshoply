<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use NiceShoply\Common\Models\Currency;

class CurrencyRepo extends BaseRepo
{
    private static mixed $enabledCurrencies = null;

    public const CACHE_KEY = 'enabled_currencies';

    public const CACHE_TTL = 3600; // 1 hour

    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('console/currency.name')],
            ['name' => 'code', 'type' => 'input', 'label' => trans('console/currency.code')],
            ['name' => 'symbol_left', 'type' => 'input', 'label' => trans('console/currency.symbol_left')],
            ['name' => 'symbol_right', 'type' => 'input', 'label' => trans('console/currency.symbol_right')],
            ['name' => 'decimal_place', 'type' => 'input', 'label' => trans('console/currency.decimal_place')],
            ['name' => 'value', 'type' => 'input', 'label' => trans('console/currency.value')],
        ];
    }

    /**
     * @param  $filters
     * @return LengthAwarePaginator
     * @throws \Exception
     */
    public function list($filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->paginate();
    }

    /**
     * @return Collection
     */
    public function enabledList(): mixed
    {
        if (self::$enabledCurrencies !== null) {
            return self::$enabledCurrencies;
        }

        return self::$enabledCurrencies = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->withActive()->builder()->get();
        });
    }

    /**
     * Clear currency list cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        self::$enabledCurrencies = null;
    }

    /**
     * @return array
     */
    public function asOptions(): array
    {
        $currencies = [];
        foreach ($this->enabledList() as $item) {
            $currencies[] = [
                'value' => $item->code,
                'label' => $item->name,
            ];
        }

        return $currencies;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Currency::query();

        $filters = array_merge($this->filters, $filters);

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', "%$name%");
        }

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', 'like', "%$code%");
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        $keyword = $filters['keyword'] ?? '';
        if ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%$keyword%")
                    ->orWhere('code', 'like', "%$keyword%");
            });
        }

        return fire_hook_filter('repo.currency.builder', $builder);
    }

    /**
     * @param  $data
     * @return Currency
     * @throws \Exception|\Throwable
     */
    public function create($data): Currency
    {
        $data = $this->handleData($data);
        $item = new Currency($data);
        $item->saveOrFail();

        return $item;
    }

    /**
     * @param  $item
     * @param  $data
     * @return mixed
     */
    public function update($item, $data): mixed
    {
        $data = $this->handleData($data);

        $item->fill($data);
        $item->saveOrFail();

        return $item;
    }

    /**
     * @param  $item
     * @return void
     */
    public function destroy($item): void
    {
        $item->delete();
    }

    /**
     * @param  array  $requestData
     * @return array
     */
    private function handleData(array $requestData): array
    {
        return [
            'name'          => $requestData['name'],
            'code'          => $requestData['code'] ?? '',
            'symbol_left'   => $requestData['symbol_left'] ?? '',
            'symbol_right'  => $requestData['symbol_right'] ?? '',
            'decimal_place' => $requestData['decimal_place'] ?? 0,
            'value'         => $requestData['value'] ?? 1,
            'active'        => $requestData['active'] ?? true,
        ];
    }
}
