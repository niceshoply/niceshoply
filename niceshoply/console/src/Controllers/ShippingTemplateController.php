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
use NiceShoply\Common\Models\ShippingTemplate;
use NiceShoply\Common\Repositories\ShippingTemplateRepo;
use NiceShoply\Common\Repositories\ShippingZoneRepo;

/**
 * 运费模板后台控制器。
 */
class ShippingTemplateController extends BaseController
{
    public function index(Request $request): mixed
    {
        $data = [
            'criteria'  => ShippingTemplateRepo::getCriteria(),
            'templates' => ShippingTemplateRepo::getInstance()->list($request->all()),
        ];

        return nice_view('console::shipping_templates.index', $data);
    }

    public function create(): mixed
    {
        return $this->form(new ShippingTemplate);
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            ShippingTemplateRepo::getInstance()->create($this->normalize($request));

            return redirect(console_route('shipping_templates.index'))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('shipping_templates.create'))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(ShippingTemplate $shippingTemplate): mixed
    {
        return $this->form($shippingTemplate);
    }

    public function form(ShippingTemplate $template): mixed
    {
        $data = [
            'template'        => $template,
            'calcTypeOptions' => ShippingTemplateRepo::getCalcTypeOptions(),
            'zones'           => ShippingZoneRepo::getInstance()->all(),
        ];

        return nice_view('console::shipping_templates.form', $data);
    }

    public function update(Request $request, ShippingTemplate $shippingTemplate): RedirectResponse
    {
        try {
            ShippingTemplateRepo::getInstance()->update($shippingTemplate, $this->normalize($request));

            return redirect(console_route('shipping_templates.index'))->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('shipping_templates.edit', [$shippingTemplate->id]))->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(ShippingTemplate $shippingTemplate): RedirectResponse
    {
        try {
            ShippingTemplateRepo::getInstance()->destroy($shippingTemplate);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 规整字段（rules 接受 JSON 文本或数组）。
     *
     * @param  Request  $request
     * @return array
     */
    private function normalize(Request $request): array
    {
        $rules = $request->input('rules', '');
        if (is_string($rules)) {
            $decoded = json_decode($rules, true);
            $rules   = is_array($decoded) ? $decoded : [];
        }

        return [
            'name'           => $request->input('name', ''),
            'zone_id'        => $request->input('zone_id') ?: null,
            'calc_type'      => $request->input('calc_type', 'flat'),
            'rules'          => $rules,
            'free_threshold' => (float) $request->input('free_threshold', 0),
            'priority'       => (int) $request->input('priority', 0),
            'active'         => (bool) $request->input('active', true),
        ];
    }
}
