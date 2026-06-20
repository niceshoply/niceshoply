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
use NiceShoply\Common\Models\Warehouse;

class WarehouseRepo extends BaseRepo
{
    protected string $model = Warehouse::class;

    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'code', 'type' => 'input', 'label' => trans('console/warehouse.code')],
            ['name' => 'name', 'type' => 'input', 'label' => trans('console/warehouse.name')],
            [
                'name'    => 'active', 'type' => 'select', 'label' => trans('console/warehouse.active'),
                'options' => [
                    ['value' => 1, 'label' => trans('common.yes')],
                    ['value' => 0, 'label' => trans('common.no')],
                ],
            ],
        ];
    }

    /**
     * @param  array  $filters
     * @return LengthAwarePaginator
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderBy('priority')->orderByDesc('id')->paginate();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Warehouse::query();
        $filters = array_merge($this->filters, $filters);

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', 'like', "%{$code}%");
        }

        $name = $filters['name'] ?? '';
        if ($name) {
            $builder->where('name', 'like', "%{$name}%");
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $builder->where('active', (bool) $filters['active']);
        }

        return $builder;
    }

    /**
     * @return Warehouse|null
     */
    public function getDefaultWarehouse(): ?Warehouse
    {
        return Warehouse::query()->where('is_default', true)->where('active', true)->first();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getActiveWarehouses(): \Illuminate\Support\Collection
    {
        return Warehouse::query()->where('active', true)->orderBy('priority')->get();
    }

    /**
     * @param  $data
     * @return mixed
     */
    public function create($data): mixed
    {
        if (! empty($data['is_default'])) {
            Warehouse::query()->where('is_default', true)->update(['is_default' => false]);
        }

        $warehouse = Warehouse::query()->create($data);
        $this->syncServiceAreas($warehouse, $data['service_areas'] ?? []);

        return $warehouse;
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     */
    public function update(mixed $item, $data): mixed
    {
        if (is_int($item)) {
            $item = Warehouse::query()->find($item);
        }

        if ($item && ! empty($data['is_default'])) {
            Warehouse::query()->where('id', '!=', $item->id)->where('is_default', true)->update(['is_default' => false]);
        }

        if ($item) {
            $item->update($data);
            $this->syncServiceAreas($item, $data['service_areas'] ?? []);
        }

        return $item;
    }

    /**
     * @param  Warehouse  $warehouse
     * @param  array  $serviceAreas
     * @return void
     */
    protected function syncServiceAreas(Warehouse $warehouse, array $serviceAreas): void
    {
        $warehouse->serviceAreas()->delete();

        $validAreas = array_filter($serviceAreas, fn ($a) => ! empty($a['country_id']));
        if (! empty($validAreas)) {
            $warehouse->serviceAreas()->createMany(
                array_map(fn ($a) => [
                    'country_id' => (int) $a['country_id'],
                    'state_id'   => (int) ($a['state_id'] ?? 0),
                ], $validAreas)
            );
        }
    }
}
