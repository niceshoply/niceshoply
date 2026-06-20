<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Console\Controllers;

use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\GdprRequestRepo;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * GDPR 申请后台列表与导出文件下载。
 */
class GdprRequestController extends BaseController
{
    public function index(Request $request): mixed
    {
        return nice_view('console::gdpr_requests.index', [
            'criteria' => GdprRequestRepo::getCriteria(),
            'requests' => GdprRequestRepo::getInstance()->list($request->all()),
        ]);
    }

    public function download(int $id): BinaryFileResponse
    {
        $request = GdprRequestRepo::getInstance()->detail($id);
        abort_if(! $request || $request->status !== 'completed' || $request->file_path === '', 404);

        $path = storage_path('app/'.$request->file_path);
        abort_if(! is_file($path), 404);

        return response()->download($path, 'gdpr-export-'.$request->customer_id.'.zip');
    }
}
