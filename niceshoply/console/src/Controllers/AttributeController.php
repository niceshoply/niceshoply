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
use NiceShoply\Common\Models\Attribute;
use NiceShoply\Common\Repositories\Attribute\GroupRepo;
use NiceShoply\Common\Repositories\AttributeRepo;
use NiceShoply\Console\Requests\AttributeRequest;
use Throwable;

class AttributeController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        $data = [
            'criteria'   => AttributeRepo::getCriteria(),
            'attributes' => AttributeRepo::getInstance()->list($filters),
        ];

        return nice_view('console::attributes.index', $data);
    }

    /**
     * Attribute creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Attribute);
    }

    /**
     * @param  AttributeRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(AttributeRequest $request): RedirectResponse
    {
        try {
            $data      = $request->all();
            $attribute = AttributeRepo::getInstance()->create($data);

            return redirect(console_route('attributes.index'))
                ->with('instance', $attribute)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('attributes.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Attribute  $attribute
     * @return mixed
     * @throws Exception
     */
    public function edit(Attribute $attribute): mixed
    {
        return $this->form($attribute);
    }

    /**
     * @param  $attribute
     * @return mixed
     * @throws Exception
     */
    public function form($attribute): mixed
    {
        $data = [
            'attribute'        => $attribute,
            'attribute_values' => $attribute->values->pluck('translations')->toArray(),
            'attribute_groups' => GroupRepo::getInstance()->getOptions(),
        ];

        return nice_view('console::attributes.form', $data);
    }

    /**
     * @param  AttributeRequest  $request
     * @param  Attribute  $attribute
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(AttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        try {
            $data = $request->all();
            AttributeRepo::getInstance()->update($attribute, $data);

            return redirect(console_route('attributes.index'))
                ->with('instance', $attribute)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('attributes.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Attribute  $attribute
     * @return RedirectResponse
     */
    public function destroy(Attribute $attribute): RedirectResponse
    {
        try {
            AttributeRepo::getInstance()->destroy($attribute);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
