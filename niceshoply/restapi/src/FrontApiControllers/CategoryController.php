<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use NiceShoply\Common\Repositories\Category\TreeRepo;
use NiceShoply\Common\Repositories\CategoryRepo;
use NiceShoply\Common\Resources\CategoryFrontend;

class CategoryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();
        $perPage = $request->get('per_page', 15);

        $categories = CategoryRepo::getInstance()->withActive()->builder($filters)->paginate($perPage);

        return CategoryFrontend::collection($categories);
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function tree(): mixed
    {
        $categoryTree = TreeRepo::getInstance()->getCategoryTree();

        return read_json_success($categoryTree);
    }
}
