<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use NiceShoply\Common\Models\ReturnReason;

class ReturnReasonRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('console/return_reason.name')],
        ];
    }

    /**
     * @param  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list($filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderBy('sort_order')->orderByDesc('id')->paginate();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = ReturnReason::query();

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', "%$name%");
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        return $builder;
    }

    /**
     * @param  $data
     * @return ReturnReason
     */
    public function create($data): ReturnReason
    {
        $item = new ReturnReason;
        $item->fill($this->handleData($data));
        $item->save();

        return $item;
    }

    /**
     * @param  $item
     * @param  $data
     * @return ReturnReason
     */
    public function update($item, $data): ReturnReason
    {
        $item->fill($this->handleData($data));
        $item->save();

        return $item;
    }

    /**
     * @param  $data
     * @return array
     */
    private function handleData($data): array
    {
        return [
            'name'        => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'sort_order'  => $data['sort_order'] ?? 0,
            'active'      => ! empty($data['active']),
        ];
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
     * @return Collection
     */
    public function getActiveReasons(): Collection
    {
        return ReturnReason::query()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
