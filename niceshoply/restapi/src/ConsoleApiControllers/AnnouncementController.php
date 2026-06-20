<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\ConsoleApiControllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Models\Announcement;
use NiceShoply\Common\Repositories\AnnouncementRepo;

/**
 * 顶部公告后台 API 控制器（IMP-12）
 */
class AnnouncementController extends BaseController
{
    /**
     * 公告列表。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $list = AnnouncementRepo::getInstance()->list($request->all());

        return read_json_success($list);
    }

    /**
     * 新增公告。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        try {
            $announcement = AnnouncementRepo::getInstance()->create($request->all());

            return create_json_success($announcement->load('translations'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 更新公告。
     *
     * @param  Announcement  $announcement
     * @param  Request  $request
     * @return mixed
     */
    public function update(Announcement $announcement, Request $request): mixed
    {
        try {
            AnnouncementRepo::getInstance()->update($announcement, $request->all());

            return update_json_success($announcement->fresh()->load('translations'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 删除公告。
     *
     * @param  Announcement  $announcement
     * @return mixed
     */
    public function destroy(Announcement $announcement): mixed
    {
        try {
            AnnouncementRepo::getInstance()->destroy($announcement);

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
