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
use Illuminate\Support\Facades\Log;
use NiceShoply\Common\Requests\UploadFileRequest;
use NiceShoply\Console\Controllers\BaseController;
use NiceShoply\RestAPI\Requests\FileRequest;
use NiceShoply\RestAPI\Services\FileManagerInterface;
use NiceShoply\RestAPI\Services\FileManagerService;
use NiceShoply\RestAPI\Services\OSSService;

class FileManagerController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function getService(): FileManagerInterface
    {
        try {
            $driver = plugin_setting('file_manager', 'driver');

            if (in_array($driver, ['oss', 's3'])) {
                $service = new OSSService;

                return fire_hook_filter('file_manager.service', $service);
            }
        } catch (Exception $e) {
            Log::warning('Failed to initialize OSS service, falling back to local:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        // default local file service
        return fire_hook_filter('file_manager.service', new FileManagerService);
    }

    /**
     * 获取文件管理器的基础配置数据
     * Get basic configuration data for file manager
     *
     * @return array
     */
    private function getFileManagerData(): array
    {
        $uploadMaxFileSize = ini_get('upload_max_filesize');
        $postMaxSize       = ini_get('post_max_size');

        // Ensure we have valid values, provide defaults if empty
        if (empty($uploadMaxFileSize) || $uploadMaxFileSize === false) {
            $uploadMaxFileSize = '2M'; // Default fallback
        }
        if (empty($postMaxSize) || $postMaxSize === false) {
            $postMaxSize = '8M'; // Default fallback
        }

        $request = request();

        return [
            'isIframe'    => $request->header('X-Iframe') === '1',
            'multiple'    => $request->query('multiple') === '1',
            'type'        => $request->query('type', 'all'),
            'base_folder' => '/',
            'driver'      => plugin_setting('file_manager', 'driver', 'local'),
            'title'       => in_array(plugin_setting('file_manager', 'driver'), ['oss', 's3']) ? __('console/file_manager.cloud_file_manager') : __('console/file_manager.local_file_manager'),
            'config'      => [
                'driver'   => plugin_setting('file_manager', 'driver', 'local'),
                'endpoint' => plugin_setting('file_manager', 'endpoint', ''),
                'bucket'   => plugin_setting('file_manager', 'bucket', ''),
                'baseUrl'  => config('app.url'),
            ],
            'uploadMaxFileSize' => $uploadMaxFileSize,
            'postMaxSize'       => $postMaxSize,
        ];
    }

    /**
     * Display the file manager index view.
     *
     * @return mixed
     */
    public function index(): mixed
    {
        $data = $this->getFileManagerData();

        Log::info('File manager index:', [
            'data'   => $data,
            'config' => [
                'driver'   => plugin_setting('file_manager', 'driver'),
                'bucket'   => plugin_setting('file_manager', 'bucket'),
                'endpoint' => plugin_setting('file_manager', 'endpoint'),
            ],
        ]);

        return nice_view('console::file_manager.index', $data);
    }

    /**
     * Display the file manager iframe view.
     *
     * @return mixed
     */
    public function iframe(): mixed
    {
        $data = $this->getFileManagerData();

        // Override isIframe to true for iframe view
        $data['isIframe'] = true;

        return nice_view('console::file_manager.iframe', $data);
    }

    /**
     * Retrieve a list of files in a folder based on filters.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function getFiles(Request $request): mixed
    {
        try {
            $baseFolder = (string) $request->input('base_folder', '/');
            $page       = (int) $request->input('page', 1);
            $perPage    = (int) $request->input('per_page', 20);
            $keyword    = (string) $request->input('keyword', '');
            $sort       = (string) $request->input('sort', 'created');  // 默认按创建时间排序
            $order      = (string) $request->input('order', 'desc');    // 默认降序，最新的在前面

            $service = $this->getService();

            return $service->getFiles($baseFolder, $keyword, $sort, $order, $page, $perPage);

        } catch (Exception $e) {
            Log::error('Get files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Retrieve a list of directories.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function getDirectories(Request $request): mixed
    {
        $service    = $this->getService();
        $baseFolder = $request->get('base_folder', '/');

        // IMP-14：lazy=1 时仅返回直接子目录（按需展开），避免大目录整树递归
        $data = $request->boolean('lazy')
            ? $service->getDirectoriesLazy($baseFolder)
            : $service->getDirectories($baseFolder);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * 懒加载目录树：仅返回指定目录的直接子目录（IMP-14）。
     *
     * @param  Request  $request
     * @return mixed
     */
    public function getDirectoriesLazy(Request $request): mixed
    {
        $service    = $this->getService();
        $baseFolder = $request->get('base_folder', '/');
        $data       = $service->getDirectoriesLazy($baseFolder);

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * Create a new directory.
     *
     * @param  FileRequest  $request
     * @return mixed
     */
    public function createDirectory(FileRequest $request): mixed
    {
        try {
            $folderName = $request->get('name');
            $parentId   = $request->get('parent_id', '/');

            $fullPath = $parentId === '/' ? "/{$folderName}" : "{$parentId}/{$folderName}";

            $service = $this->getService();
            $service->createDirectory($fullPath);

            return create_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Rename a file or folder.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function rename(Request $request): mixed
    {
        try {
            $originName = $request->get('origin_name');
            $newName    = $request->get('new_name');

            $originName = $this->normalizePath($originName);

            $dirPath = dirname($originName);
            $newPath = $dirPath === '/' ? "/{$newName}" : "{$dirPath}/{$newName}";

            $service = $this->getService();
            $service->updateName($originName, $newPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Normalize file path
     *
     * @param  string  $path
     * @return string
     */
    private function normalizePath(string $path): string
    {
        $path = preg_replace('#/+#', '/', $path);

        return '/'.ltrim($path, '/');
    }

    /**
     * Delete specified files in a directory.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function destroyFiles(Request $request): mixed
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $basePath    = $requestData['path'] ?? '/';
            $files       = $requestData['files'] ?? [];

            if (empty($files)) {
                throw new Exception(trans('console::file_manager.no_files_selected'));
            }

            $service = $this->getService();
            $service->deleteFiles($basePath, $files);

            return json_success(trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Delete a specified directory.
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function destroyDirectories(Request $request): mixed
    {
        try {
            $folderName = $request->get('name');
            $service    = $this->getService();
            $service->deleteDirectoryOrFile($folderName);

            return json_success(trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Move a directory to a new location.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function moveDirectories(Request $request): mixed
    {
        try {
            $sourcePath = $request->get('source_path');
            $destPath   = $request->get('dest_path');
            $service    = $this->getService();
            $service->moveDirectory($sourcePath, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Move multiple image files to a new directory.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function moveFiles(Request $request): mixed
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $files       = $requestData['files'] ?? [];
            $destPath    = $requestData['dest_path'] ?? '';

            if (empty($files) || empty($destPath)) {
                throw new Exception(trans('console::file_manager.invalid_params'));
            }

            Log::info('Move files request:', [
                'files'    => $files,
                'destPath' => $destPath,
            ]);

            $service = $this->getService();
            $service->moveFiles($files, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            Log::error('Move files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Upload a file to the specified directory.
     *
     * @param  UploadFileRequest  $request
     * @return mixed
     */
    public function uploadFiles(UploadFileRequest $request): mixed
    {
        $service  = $this->getService();
        $file     = $request->file('file');
        $savePath = $request->get('path');

        $originName = $file->getClientOriginalName();
        $fileUrl    = $service->uploadFile($file, $savePath, $originName);

        $data = [
            'name' => $originName,
            'url'  => $fileUrl,
        ];

        return json_success('success', $data);
    }

    /**
     * Copy multiple files to a new directory.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function copyFiles(Request $request): mixed
    {
        try {
            $requestData = json_decode($request->getContent(), true);
            $files       = $requestData['files'] ?? [];
            $destPath    = $requestData['dest_path'] ?? '';

            if (empty($files) || empty($destPath)) {
                throw new Exception(trans('console::file_manager.invalid_params'));
            }

            Log::info('Copy files request:', [
                'files'    => $files,
                'destPath' => $destPath,
            ]);

            $service = $this->getService();
            $service->copyFiles($files, $destPath);

            return json_success(trans('common.updated_success'));
        } catch (Exception $e) {
            Log::error('Copy files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }
}
