<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Booking\Controllers\Console;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Console\Controllers\BaseController;
use Plugin\Booking\Models\Booking;
use Plugin\Booking\Models\BookingService as ServiceModel;
use Plugin\Booking\Services\BookingService;

class BookingController extends BaseController
{
    protected string $modelClass = Booking::class;

    public function services(): mixed
    {
        $services = ServiceModel::query()->orderBy('sort')->orderByDesc('id')->get();

        return nice_view('Booking::console.services', compact('services'));
    }

    public function storeService(Request $request): mixed
    {
        try {
            $data = $request->validate([
                'id'                => 'nullable|integer',
                'name'              => 'required|string|max:191',
                'product_sku'       => 'nullable|string|max:64',
                'price'             => 'nullable|numeric|min:0',
                'duration_min'      => 'required|integer|min:5',
                'slot_interval_min' => 'required|integer|min:5',
                'capacity'          => 'required|integer|min:1',
                'open_time'         => 'required|string|max:5',
                'close_time'        => 'required|string|max:5',
                'open_weekdays'     => 'nullable|string|max:32',
                'is_active'         => 'nullable|boolean',
                'sort'              => 'nullable|integer|min:0',
            ]);
            $data['is_active']     = $request->boolean('is_active', true);
            $data['open_weekdays'] = $data['open_weekdays'] ?? '1,2,3,4,5,6,7';

            $id = (int) ($data['id'] ?? 0);
            unset($data['id']);

            if ($id > 0) {
                ServiceModel::query()->findOrFail($id)->update($data);
            } else {
                ServiceModel::query()->create($data);
            }

            return json_success(__('Booking::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function destroyService(int $id): mixed
    {
        try {
            ServiceModel::query()->findOrFail($id)->delete();

            return json_success(__('Booking::common.deleted'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    public function bookings(Request $request): mixed
    {
        $status   = (string) $request->query('status', '');
        $bookings = Booking::query()
            ->with('service')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->orderByDesc('id')
            ->paginate(30);

        return nice_view('Booking::console.bookings', compact('bookings', 'status'));
    }

    public function updateStatus(Request $request, int $id): mixed
    {
        try {
            $status = (string) $request->input('status');
            BookingService::getInstance()->setStatus($id, $status);

            return json_success(__('Booking::common.saved'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
