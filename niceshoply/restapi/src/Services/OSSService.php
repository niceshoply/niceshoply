<?php

namespace NiceShoply\RestAPI\Services;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OSSService implements FileManagerInterface
{
    protected Filesystem $disk;

    protected string $cdnDomain;

    public function __construct()
    {
        storage_service()->configureCloudDisk();
        $this->validateConfig();
        $this->disk      = Storage::disk('s3');
        $this->cdnDomain = plugin_setting('file_manager', 'cdn_domain', '');

        Log::info('OSS Service initialized with:', [
            'bucket'    => plugin_setting('file_manager', 'bucket', ''),
            'cdnDomain' => $this->cdnDomain,
            'endpoint'  => plugin_setting('file_manager', 'endpoint', ''),
        ]);
    }

    protected function validateConfig(): void
    {
        $required = [
            'key'      => 'Access Key',
            'secret'   => 'Secret Key',
            'region'   => 'Region',
            'bucket'   => 'Bucket',
            'endpoint' => 'Endpoint',
        ];

        $missing = [];
        foreach ($required as $field => $label) {
            $value = plugin_setting('file_manager', $field, '');
            if (empty($value)) {
                $missing[] = $label;
            }
        }
        if (! empty($missing)) {
            Log::warning('OSS configuration incomplete:', ['missing' => $missing]);
            throw new Exception('OSS 配置不完整，请检查以下配置：'.PHP_EOL.
                implode(PHP_EOL, array_map(fn ($key) => "- {$key}", $missing)));
        }
    }

    public function uploadFile($file, $savePath, $originName): string
    {
        try {
            $key = $this->getObjectKey($savePath, $originName);
            $this->disk->put($key, file_get_contents($file->getRealPath()), 'public');

            return storage_service()->url($key, 's3');
        } catch (Exception $e) {
            Log::error('OSS upload failed:', [
                'error' => $e->getMessage(),
                'file'  => $originName,
            ]);
            throw new Exception(trans('console::file_manager.upload_failed'));
        }
    }

    public function getFiles(string $baseFolder, ?string $keyword = '', string $sort = 'name', string $order = 'asc', int $page = 1, int $perPage = 20): array
    {
        try {
            $prefix = trim($baseFolder, '/');
            $prefix = $prefix ? $prefix.'/' : '';

            // Get directories
            $rawDirs     = $this->disk->directories($prefix ? rtrim($prefix, '/') : '');
            $directories = array_map(function ($dir) {
                $name = basename($dir);

                return [
                    'name'          => $name,
                    'path'          => $dir.'/',
                    'is_dir'        => true,
                    'thumb'         => url('/images/icons/folder.png'),
                    'url'           => '',
                    'mime'          => 'directory',
                    'size'          => 0,
                    'last_modified' => null,
                ];
            }, $rawDirs);
            // Get files
            $rawFiles = $this->disk->files($prefix ? rtrim($prefix, '/') : '');
            $files    = array_map(function ($filePath) {
                $name = basename($filePath);
                $url  = storage_service()->url($filePath, 's3');

                return [
                    'name'          => $name,
                    'path'          => $filePath,
                    'is_dir'        => false,
                    'thumb'         => $this->isImagePath($filePath) ? $url : url('/images/icons/file.png'),
                    'url'           => $url,
                    'mime'          => $this->getMimeType($filePath) ?? 'application/octet-stream',
                    'size'          => $this->disk->size($filePath),
                    'last_modified' => $this->disk->lastModified($filePath),
                ];
            }, $rawFiles);

            $items = array_merge($directories, $files);

            if ($keyword) {
                $items = array_filter($items, function ($item) use ($keyword) {
                    return stripos($item['name'], $keyword) !== false;
                });
            }

            usort($items, function ($a, $b) use ($sort, $order) {
                if ($a['is_dir'] && ! $b['is_dir']) {
                    return -1;
                }
                if (! $a['is_dir'] && $b['is_dir']) {
                    return 1;
                }

                $result = 0;
                if ($sort === 'name') {
                    $result = strcmp($a['name'], $b['name']);
                } elseif ($sort === 'size') {
                    $result = ($a['size'] ?? 0) <=> ($b['size'] ?? 0);
                } elseif ($sort === 'created') {
                    $result = ($a['last_modified'] ?? 0) <=> ($b['last_modified'] ?? 0);
                }

                return $order === 'desc' ? -$result : $result;
            });

            $total  = count($items);
            $offset = ($page - 1) * $perPage;
            $items  = array_slice($items, $offset, $perPage);

            return [
                'images'         => $items,
                'image_total'    => $total,
                'image_page'     => $page,
                'image_per_page' => $perPage,
                'success'        => true,
            ];
        } catch (Exception $e) {
            Log::error('OSS get files failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    protected function getObjectKey($path, $filename): string
    {
        $path = trim($path, '/');

        return $path ? "{$path}/{$filename}" : $filename;
    }

    public function getDirectories(string $baseFolder = '/'): array
    {
        try {
            $prefix  = trim($baseFolder, '/');
            $rawDirs = $this->disk->directories($prefix ?: '');

            $directories = [
                [
                    'id'     => '/',
                    'name'   => '/',
                    'path'   => '/',
                    'parent' => null,
                    'isRoot' => true,
                ],
            ];

            foreach ($rawDirs as $dir) {
                $name   = basename($dir);
                $parent = dirname($dir);
                $parent = $parent === '.' ? '/' : $parent;

                $directories[] = [
                    'id'     => $dir,
                    'name'   => $name,
                    'path'   => $dir,
                    'parent' => $parent,
                    'isRoot' => false,
                ];
            }

            return $directories;
        } catch (Exception $e) {
            Log::error('OSS get directories failed:', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * 懒加载：仅返回指定目录的直接子目录，并标记是否还有下级目录（IMP-14）。
     *
     * @param  string  $baseFolder
     * @return array
     */
    public function getDirectoriesLazy(string $baseFolder = '/'): array
    {
        try {
            $prefix  = trim($baseFolder, '/');
            $rawDirs = $this->disk->directories($prefix ?: '');

            $result = [];
            foreach ($rawDirs as $dir) {
                $result[] = [
                    'name'         => basename($dir),
                    'path'         => '/'.ltrim($dir, '/'),
                    'has_children' => ! empty($this->disk->directories($dir)),
                ];
            }

            return $result;
        } catch (Exception $e) {
            Log::error('OSS get directories (lazy) failed:', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function createDirectory($path): bool
    {
        try {
            $path = trim($path, '/');
            $this->disk->makeDirectory($path);

            return true;
        } catch (Exception $e) {
            Log::error('OSS create directory failed:', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            throw new Exception(trans('console::file_manager.create_directory_failed'));
        }
    }

    public function moveFiles(array $files, string $destPath): bool
    {
        try {
            foreach ($files as $filePath) {
                $fileName = basename($filePath);
                $newKey   = trim($destPath, '/').'/'.$fileName;
                $this->disk->move($filePath, $newKey);
            }

            return true;
        } catch (Exception $e) {
            Log::error('OSS move files failed:', [
                'error'    => $e->getMessage(),
                'files'    => $files,
                'destPath' => $destPath,
            ]);
            throw new Exception(trans('console::file_manager.move_failed'));
        }
    }

    public function copyFiles(array $files, string $destPath): bool
    {
        try {
            foreach ($files as $filePath) {
                $fileName = basename($filePath);
                $newKey   = trim($destPath, '/').'/'.$fileName;
                $this->disk->copy($filePath, $newKey);
            }

            return true;
        } catch (Exception $e) {
            Log::error('OSS copy files failed:', [
                'error'    => $e->getMessage(),
                'files'    => $files,
                'destPath' => $destPath,
            ]);
            throw new Exception(trans('console::file_manager.copy_failed'));
        }
    }

    public function deleteFiles(string $basePath, array $files): bool
    {
        try {
            $paths = array_map(fn ($file) => ltrim($basePath.'/'.$file, '/'), $files);
            $this->disk->delete($paths);

            return true;
        } catch (Exception $e) {
            Log::error('OSS delete files failed:', [
                'error' => $e->getMessage(),
                'files' => $files,
            ]);
            throw new Exception(trans('console::file_manager.delete_failed'));
        }
    }

    public function deleteDirectoryOrFile(string $path): bool
    {
        try {
            $dirPath = trim($path, '/');
            $this->disk->deleteDirectory($dirPath);

            return true;
        } catch (Exception $e) {
            Log::error('OSS delete directory failed:', [
                'error' => $e->getMessage(),
                'path'  => $path,
            ]);
            throw new Exception(trans('console::file_manager.delete_failed'));
        }
    }

    public function moveDirectory(string $sourcePath, string $destPath): bool
    {
        try {
            $source = trim($sourcePath, '/');
            $dest   = trim($destPath, '/');

            $allFiles = $this->disk->allFiles($source);
            foreach ($allFiles as $filePath) {
                $relativePath = substr($filePath, strlen($source.'/'));
                $newPath      = $dest.'/'.$relativePath;

                if ($filePath === $newPath) {
                    continue;
                }

                $this->disk->move($filePath, $newPath);
            }

            return true;
        } catch (Exception $e) {
            Log::error('OSS move directory failed:', [
                'error'      => $e->getMessage(),
                'sourcePath' => $sourcePath,
                'destPath'   => $destPath,
            ]);
            throw new Exception(trans('console::file_manager.move_failed'));
        }
    }

    public function updateName(string $originPath, string $newPath): bool
    {
        try {
            if (substr($originPath, -1) === '/') {
                return $this->moveDirectory($originPath, $newPath);
            }

            $this->disk->move(ltrim($originPath, '/'), ltrim($newPath, '/'));

            return true;
        } catch (Exception $e) {
            Log::error('OSS rename failed:', [
                'error'      => $e->getMessage(),
                'originPath' => $originPath,
                'newPath'    => $newPath,
            ]);
            throw new Exception(trans('console::file_manager.rename_failed'));
        }
    }

    protected function isImagePath(string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    }

    protected function getMimeType(string $path): ?string
    {
        try {
            return $this->disk->mimeType($path) ?: null;
        } catch (Exception $e) {
            return null;
        }
    }
}
