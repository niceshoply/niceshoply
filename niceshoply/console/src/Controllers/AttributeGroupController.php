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
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Attribute;
use NiceShoply\Common\Models\Attribute\Group;
use NiceShoply\Common\Repositories\Attribute\GroupRepo;
use Throwable;

class AttributeGroupController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $data = [
            'criteria'   => GroupRepo::getCriteria(),
            'attributes' => GroupRepo::getInstance()->list($request->all()),
        ];

        return nice_view('console::attribute_groups.index', $data);
    }

    /**
     * @param  Group  $attributeGroup
     * @return Group
     */
    public function show(Attribute\Group $attributeGroup): Group
    {
        return $attributeGroup->load(['translations']);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request): mixed
    {
        try {
            $attributeGroup = GroupRepo::getInstance()->create($request->all());

            return create_json_success($attributeGroup);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  Group  $attributeGroup
     * @return mixed
     * @throws Exception
     */
    public function update(Request $request, Attribute\Group $attributeGroup): mixed
    {
        try {
            $attributeGroup = GroupRepo::getInstance()->update($attributeGroup, $request->all());

            return update_json_success($attributeGroup);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Group  $attributeGroup
     * @return mixed
     */
    public function destroy(Attribute\Group $attributeGroup): mixed
    {
        $attributeGroup->translations()->delete();
        $attributeGroup->delete();

        return redirect(console_route('attribute_groups.index'))
            ->with('success', console_trans('common.deleted_success'));
    }
}
