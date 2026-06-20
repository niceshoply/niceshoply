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
use NiceShoply\Common\Models\Promotion;
use NiceShoply\Common\Repositories\PromotionRepo;

/**
 * 促销活动后台控制器（列表/筛选/CRUD/启停）。
 */
class PromotionController extends BaseController
{
    /**
     * 促销活动列表。
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria'   => PromotionRepo::getCriteria(),
            'promotions' => PromotionRepo::getInstance()->list($filters),
        ];

        return nice_view('console::promotions.index', $data);
    }

    /**
     * 新建页面。
     *
     * @return mixed
     */
    public function create(): mixed
    {
        return $this->form(new Promotion);
    }

    /**
     * 保存新建。
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            PromotionRepo::getInstance()->create($this->normalize($request));

            return redirect(console_route('promotions.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('promotions.create'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 编辑页面。
     *
     * @param  Promotion  $promotion
     * @return mixed
     */
    public function edit(Promotion $promotion): mixed
    {
        return $this->form($promotion);
    }

    /**
     * 表单页面（新建/编辑共用）。
     *
     * @param  Promotion  $promotion
     * @return mixed
     */
    public function form(Promotion $promotion): mixed
    {
        $data = [
            'promotion'            => $promotion,
            'scopeOptions'         => PromotionRepo::getScopeOptions(),
            'actionTypeOptions'    => PromotionRepo::getActionTypeOptions(),
            'conditionTypeOptions' => PromotionRepo::getConditionTypeOptions(),
        ];

        return nice_view('console::promotions.form', $data);
    }

    /**
     * 保存编辑。
     *
     * @param  Request  $request
     * @param  Promotion  $promotion
     * @return RedirectResponse
     */
    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        try {
            PromotionRepo::getInstance()->update($promotion, $this->normalize($request));

            return redirect(console_route('promotions.index'))
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(console_route('promotions.edit', [$promotion->id]))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 删除。
     *
     * @param  Promotion  $promotion
     * @return RedirectResponse
     */
    public function destroy(Promotion $promotion): RedirectResponse
    {
        try {
            PromotionRepo::getInstance()->destroy($promotion);

            return back()->with('success', console_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * 把扁平表单字段规整为仓库可用的结构（含 conditions/actions JSON 与翻译）。
     *
     * @param  Request  $request
     * @return array
     */
    private function normalize(Request $request): array
    {
        $conditionType = $request->input('condition_type', 'none');
        $actionType    = $request->input('action_type', 'fixed');

        // 组装条件
        $conditions = [];
        if ($conditionType === 'min_amount') {
            $conditions['min_amount'] = (float) $request->input('condition_value', 0);
        } elseif ($conditionType === 'min_qty') {
            $conditions['min_qty'] = (int) $request->input('condition_value', 0);
        } elseif ($conditionType === 'tiered') {
            $conditions['tiers'] = $this->parseTiers($request->input('tiers', ''));
        }

        // 组装优惠动作
        $actions = [];
        if ($actionType !== 'free_shipping') {
            $actions['value'] = (float) $request->input('action_value', 0);
            $max              = (float) $request->input('action_max', 0);
            if ($max > 0) {
                $actions['max'] = $max;
            }
        }
        if ($conditionType === 'tiered') {
            $actions['tiers'] = $conditions['tiers'];
        }

        // 当前语言文案
        $translations = [];
        $label        = $request->input('label', '');
        if ($label !== '') {
            $translations[locale_code()] = [
                'label'       => $label,
                'description' => $request->input('description', ''),
            ];
        }

        return [
            'name'               => $request->input('name', ''),
            'scope'              => $request->input('scope', 'cart'),
            'condition_type'     => $conditionType,
            'conditions'         => $conditions,
            'action_type'        => $actionType,
            'actions'            => $actions,
            'priority'           => (int) $request->input('priority', 0),
            'exclusive'          => (bool) $request->input('exclusive', false),
            'usage_limit'        => (int) $request->input('usage_limit', 0),
            'per_customer_limit' => (int) $request->input('per_customer_limit', 0),
            'customer_group_ids' => (array) $request->input('customer_group_ids', []),
            'starts_at'          => $request->input('starts_at') ?: null,
            'ends_at'            => $request->input('ends_at') ?: null,
            'active'             => (bool) $request->input('active', true),
            'translations'       => $translations,
        ];
    }

    /**
     * 解析阶梯文本（每行「门槛:优惠值」）为结构化数组。
     *
     * @param  string  $raw
     * @return array
     */
    private function parseTiers(string $raw): array
    {
        $tiers = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($raw)) as $line) {
            $line = trim($line);
            if ($line === '' || ! str_contains($line, ':')) {
                continue;
            }
            [$min, $value] = explode(':', $line, 2);
            $tiers[]       = [
                'min'   => (float) trim($min),
                'value' => (float) trim($value),
            ];
        }

        return $tiers;
    }
}
