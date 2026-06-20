<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Category;
use NiceShoply\Common\Repositories\CategoryRepo;
use NiceShoply\Common\Resources\CategorySimple;
use NiceShoply\Console\Requests\CategoryRequest;
use Throwable;

class CategoryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        $filters['parent_id'] = 0;

        $categories = CategoryRepo::getInstance()->all($filters);

        $data = [
            'categories' => CategorySimple::collection($categories)->jsonSerialize(),
        ];

        return nice_view('console::categories.index', $data);
    }

    /**
     * Category creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Category);
    }

    /**
     * @param  CategoryRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        try {
            $data     = $request->all();
            $category = CategoryRepo::getInstance()->create($data);

            return redirect(console_route('categories.index'))
                ->with('instance', $category)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Category  $category
     * @return mixed
     * @throws Exception
     */
    public function edit(Category $category): mixed
    {
        return $this->form($category);
    }

    /**
     * @param  Category  $category
     * @return mixed
     */
    public function form(Category $category): mixed
    {
        $childIDs = $category->children->pluck('id')->toArray();
        if ($category->id) {
            $childIDs = array_merge($childIDs, [$category->id]);
        }

        $excludeIDs = array_unique($childIDs);
        $filters    = [
            'active'      => 1,
            'exclude_ids' => $excludeIDs,
        ];

        $hierarchicalCategories = CategoryRepo::getInstance()->getHierarchicalCategories($filters);

        array_unshift($hierarchicalCategories, [
            'id'    => 0,
            'name'  => console_trans('category.root_category'),
            'level' => 0,
        ]);

        $data = [
            'category'   => $category,
            'categories' => $hierarchicalCategories,
        ];

        return nice_view('console::categories.form', $data);
    }

    /**
     * @param  CategoryRequest  $request
     * @param  Category  $category
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        try {
            $data = $request->all();
            CategoryRepo::getInstance()->update($category, $data);

            return redirect(console_route('categories.index'))
                ->with('instance', $category)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Category  $category
     * @return mixed
     */
    public function destroy(Category $category): mixed
    {
        try {
            if ($category->children()->count()) {
                throw new \Exception(console_trans('category.has_children'));
            }
            CategoryRepo::getInstance()->destroy($category);

            return json_success(console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
