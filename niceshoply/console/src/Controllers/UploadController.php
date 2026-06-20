<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

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
        $image = $request->file('image');
        $type  = $request->file('type', 'common');

        // Use unified upload service with security validation
        $data = UploadService::getInstance()->uploadForConsole($image, $type);

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
        $file = $request->file('file');
        $type = $request->file('type', 'files');

        // Use unified upload service with security validation
        $data = UploadService::getInstance()->uploadForConsole($file, $type);

        return json_success(trans('common/upload.upload_success'), $data);
    }
}
