<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Front\Controllers;

use NiceShoply\Common\Requests\UploadFileRequest;
use NiceShoply\Common\Requests\UploadImageRequest;
use NiceShoply\RestAPI\Services\UploadService;

class UploadController
{
    /**
     * Upload images.
     *
     * @param  UploadImageRequest  $request
     * @return mixed
     */
    public function images(UploadImageRequest $request): mixed
    {
        $data = UploadService::getInstance()->images($request);

        return json_success(trans('common/upload.upload_success'), $data);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return mixed
     */
    public function docs(UploadFileRequest $request): mixed
    {
        $data = UploadService::getInstance()->files($request);

        return json_success(trans('common/upload.upload_success'), $data);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return mixed
     */
    public function files(UploadFileRequest $request): mixed
    {
        $data = UploadService::getInstance()->files($request);

        return json_success(trans('common/upload.upload_success'), $data);
    }
}
