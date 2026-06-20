<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Catalog;
use NiceShoply\Common\Repositories\CatalogRepo;
use NiceShoply\Console\Requests\CatalogRequest;

class CatalogController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria' => CatalogRepo::getCriteria(),
            'catalogs' => CatalogRepo::getInstance()->list($filters),
        ];

        return nice_view('console::catalogs.index', $data);
    }

    /**
     * Catalog creation page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function create(): mixed
    {
        return $this->form(new Catalog);
    }

    /**
     * @param  CatalogRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(CatalogRequest $request): RedirectResponse
    {
        try {
            $data    = $request->all();
            $catalog = CatalogRepo::getInstance()->create($data);

            return redirect(console_route('catalogs.index'))
                ->with('instance', $catalog)
                ->with('success', console_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect(console_route('catalogs.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Catalog  $catalog
     * @return mixed
     * @throws \Exception
     */
    public function edit(Catalog $catalog): mixed
    {
        return $this->form($catalog);
    }

    /**
     * @param  $catalog
     * @return mixed
     * @throws \Exception
     */
    public function form($catalog): mixed
    {
        $filters = ['active' => 1];

        // Exclude current catalog from parent options to prevent self-reference
        if ($catalog->id) {
            $filters['exclude_id'] = $catalog->id;
        }

        $hierarchicalCatalogs = CatalogRepo::getInstance()->getHierarchicalCatalogs($filters);

        // Convert hierarchical catalogs to the format expected by the select component
        $catalogs   = [];
        $catalogs[] = ['id' => 0, 'name' => __('console/catalog.root_catalog')];

        foreach ($hierarchicalCatalogs as $hierarchicalCatalog) {
            $catalogs[] = [
                'id'   => $hierarchicalCatalog['id'],
                'name' => $hierarchicalCatalog['title'],
            ];
        }

        $data = [
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
        ];

        return nice_view('console::catalogs.form', $data);
    }

    /**
     * @param  CatalogRequest  $request
     * @param  Catalog  $catalog
     * @return RedirectResponse
     */
    public function update(CatalogRequest $request, Catalog $catalog): RedirectResponse
    {
        try {
            $data = $request->all();
            CatalogRepo::getInstance()->update($catalog, $data);

            return redirect(console_route('catalogs.index'))
                ->with('instance', $catalog)
                ->with('success', console_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect(console_route('catalogs.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Catalog  $catalog
     * @return RedirectResponse
     */
    public function destroy(Catalog $catalog): RedirectResponse
    {
        try {
            CatalogRepo::getInstance()->destroy($catalog);

            return redirect(console_route('catalogs.index'))
                ->with('success', console_trans('common.deleted_success'));
        } catch (\Exception $e) {
            return redirect(console_route('catalogs.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
