<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\RestAPI\Services;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use NiceShoply\Common\Services\FileSecurityValidator;

class FileManagerService implements FileManagerInterface
{
    protected Filesystem $disk;

    protected string $diskName = 'media';

    protected string $mediaDir = 'static/media';

    /**
     * Excluded files from listing
     */
    protected const EXCLUDED_FILES = ['index.html'];

    protected const SORT_FIELD_CREATED = 'created';

    protected const SORT_ORDER_DESC = 'desc';

    public function __construct()
    {
        $this->disk = Storage::disk($this->diskName);
    }

    /**
     * Retrieves directories within a base folder.
     */
    public function getDirectories(string $baseFolder = '/'): array
    {
        $baseFolder  = FileSecurityValidator::validateDirectoryPath($baseFolder);
        $path        = $this->normalizePath($baseFolder);
        $directories = $this->disk->directories($path);
        $result      = [];

        foreach ($directories as $directory) {
            $baseName = basename($directory);
            $dirPath  = '/'.$directory;

            $item = [
                'name' => $baseName,
                'path' => $dirPath,
            ];

            $subDirectories = $this->getDirectories($dirPath);
            if (! empty($subDirectories)) {
                $item['children'] = $subDirectories;
            }

            $result[] = $item;
        }

        return $result;
    }

    /**
     * 懒加载目录树：仅返回直接子目录（不递归），并标记是否还有下级目录。
     *
     * 相比 getDirectories 的整树递归，按层级返回可显著降低大目录场景的开销，
     * 前端目录树可在节点展开时再请求其子级。
     *
     * @param  string  $baseFolder
     * @return array
     */
    public function getDirectoriesLazy(string $baseFolder = '/'): array
    {
        $baseFolder  = FileSecurityValidator::validateDirectoryPath($baseFolder);
        $path        = $this->normalizePath($baseFolder);
        $directories = $this->disk->directories($path);
        $result      = [];

        foreach ($directories as $directory) {
            $dirPath = '/'.$directory;

            $result[] = [
                'name' => basename($directory),
                'path' => $dirPath,
                // 是否存在下级目录：用于前端决定是否显示展开箭头
                'has_children' => ! empty($this->disk->directories($directory)),
            ];
        }

        return $result;
    }

    /**
     * Get files list with pagination and filtering
     */
    public function getFiles(string $baseFolder, string $keyword = '', string $sort = self::SORT_FIELD_CREATED, string $order = self::SORT_ORDER_DESC, int $page = 1, int $perPage = 20): array
    {
        $baseFolder = FileSecurityValidator::validateDirectoryPath($baseFolder);
        $path       = $this->normalizePath($baseFolder);

        $folders = $this->collectFolders($path);
        $images  = $this->collectFiles($path, $keyword);

        $allItems = array_merge($folders, $images);
        $allItems = $this->sortItems($allItems, $sort, $order);
        $allItems = $this->removeTemporaryFields($allItems);

        return $this->paginateItems($allItems, $page, $perPage);
    }

    /**
     * Creates a new directory.
     */
    public function createDirectory(string $path): bool
    {
        try {
            $path       = FileSecurityValidator::validateDirectoryPath($path);
            $normalized = $this->normalizePath($path);

            if ($this->disk->exists($normalized)) {
                throw new Exception(trans('console/file_manager.directory_already_exist'));
            }

            $this->disk->makeDirectory($normalized);

            return true;
        } catch (Exception $e) {
            Log::error('Create directory failed:', ['path' => $path, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Moves a directory to a new path.
     */
    public function moveDirectory(string $sourcePath, string $destPath): bool
    {
        try {
            $this->validatePathsNotEmpty($sourcePath, $destPath);
            $this->validateNotMovingToSubdirectory($sourcePath, $destPath);

            $source   = $this->normalizePath($sourcePath);
            $destDir  = $this->normalizePath($destPath);
            $destFull = rtrim($destDir, '/').'/'.basename($source);

            if (! $this->disk->exists($source)) {
                throw new Exception(trans('console/file_manager.target_not_exist'));
            }

            // Move all files within the directory
            $allFiles = $this->disk->allFiles($source);
            foreach ($allFiles as $file) {
                $relativePath = substr($file, strlen($source.'/'));
                $newPath      = $destFull.'/'.$relativePath;
                $this->disk->move($file, $newPath);
            }

            // Clean up empty source directory
            $this->disk->deleteDirectory($source);

            return true;
        } catch (Exception $e) {
            $this->logError('Move directory failed', $e, ['source' => $sourcePath, 'destination' => $destPath]);
            throw $e;
        }
    }

    /**
     * Moves multiple files to a new directory.
     */
    public function moveFiles(array $files, string $destPath): bool
    {
        try {
            $this->validateFilesNotEmpty($files);
            $destPath = FileSecurityValidator::validateDirectoryPath($destPath);
            $files    = $this->validateFilePaths($files);
            $dest     = $this->normalizePath($destPath);

            foreach ($files as $fileName) {
                $source  = $this->normalizePath($fileName);
                $newPath = rtrim($dest, '/').'/'.basename($source);

                if ($this->disk->exists($newPath)) {
                    $this->disk->delete($newPath);
                }

                $this->disk->move($source, $newPath);
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Move files failed', $e, ['files' => $files, 'destination' => $destPath]);
            throw $e;
        }
    }

    /**
     * Deletes a file or folder.
     */
    public function deleteDirectoryOrFile(string $path): bool
    {
        try {
            $path       = FileSecurityValidator::validateDirectoryPath($path);
            $normalized = $this->normalizePath($path);

            // Check if it's a directory (has files/subdirs or ends with /)
            $dirs  = $this->disk->directories($normalized);
            $files = $this->disk->files($normalized);

            if (! empty($dirs) || ! empty($files)) {
                // It's a non-empty directory
                $this->disk->deleteDirectory($normalized);
            } elseif ($this->disk->exists($normalized)) {
                // It's a file
                $this->disk->delete($normalized);
            } else {
                // Try as empty directory
                $this->disk->deleteDirectory($normalized);
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Delete path failed', $e, ['path' => $path]);
            throw $e;
        }
    }

    /**
     * Delete multiple files.
     */
    public function deleteFiles(string $basePath, array $files): bool
    {
        try {
            $this->validateFilesNotEmpty($files);

            foreach ($files as $file) {
                $filePath = $this->normalizePath("$basePath/$file");
                if ($this->disk->exists($filePath)) {
                    $this->disk->delete($filePath);
                }
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Delete files failed', $e, ['files' => $files]);
            throw $e;
        }
    }

    /**
     * Renames a file or folder.
     */
    public function updateName(string $originPath, string $newPath): bool
    {
        try {
            $originPath = FileSecurityValidator::validateDirectoryPath($originPath);
            $newPath    = FileSecurityValidator::validateDirectoryPath($newPath);

            $newFileName = basename($newPath);
            FileSecurityValidator::validateFileName($newFileName);

            if (pathinfo($newFileName, PATHINFO_EXTENSION)) {
                FileSecurityValidator::validateFileExtension($newFileName);
            }

            $source = $this->normalizePath($originPath);
            $target = $this->normalizePath($newPath);

            if (! $this->disk->exists($source)) {
                throw new Exception(trans('console/file_manager.target_not_exist'));
            }

            if ($this->disk->exists($target)) {
                $dirPath = dirname($newPath);
                $newName = $this->getUniqueFileName($dirPath, basename($newPath));
                $target  = $this->normalizePath($dirPath === '/' ? "/$newName" : "$dirPath/$newName");
            }

            $this->disk->move($source, $target);

            return true;
        } catch (Exception $e) {
            Log::error('Rename failed:', ['error' => $e->getMessage(), 'origin_path' => $originPath, 'new_path' => $newPath]);
            throw $e;
        }
    }

    /**
     * Uploads a file to a specified path.
     */
    public function uploadFile(UploadedFile $file, string $savePath, string $originName): string
    {
        FileSecurityValidator::validateFile($originName);
        $savePath   = FileSecurityValidator::validateDirectoryPath($savePath);
        $originName = $this->getUniqueFileName($savePath, $originName);
        $filePath   = $file->storeAs($this->normalizePath($savePath), $originName, $this->diskName);

        return asset($this->mediaDir.'/'.$filePath);
    }

    /**
     * Generates a unique file name to avoid conflicts.
     */
    public function getUniqueFileName(string $savePath, string $originName): string
    {
        $path = $this->normalizePath("$savePath/$originName");
        if ($this->disk->exists($path)) {
            $originName = $this->getNewFileName($originName);

            return $this->getUniqueFileName($savePath, $originName);
        }

        return $originName;
    }

    /**
     * Generates a new file name by appending an incremented index.
     */
    public function getNewFileName(string $originName): string
    {
        $extension = pathinfo($originName, PATHINFO_EXTENSION);
        $name      = pathinfo($originName, PATHINFO_FILENAME);

        if (preg_match('/(.+?)\((\d+)\)$/', $name, $matches)) {
            $index = (int) $matches[2] + 1;
            $name  = "{$matches[1]}({$index})";
        } else {
            $name .= '(1)';
        }

        return "{$name}.{$extension}";
    }

    /**
     * Copies multiple files to a new directory.
     */
    public function copyFiles(array $files, string $destPath): bool
    {
        try {
            $this->validateFilesNotEmpty($files);
            $destPath = FileSecurityValidator::validateDirectoryPath($destPath);
            $files    = $this->validateFilePaths($files);
            $dest     = $this->normalizePath($destPath);

            foreach ($files as $fileName) {
                $source  = $this->normalizePath($fileName);
                $newPath = rtrim($dest, '/').'/'.basename($source);

                if ($this->disk->exists($newPath)) {
                    $newName = $this->getUniqueFileName($destPath, basename($fileName));
                    $newPath = rtrim($dest, '/').'/'.$newName;
                }

                $this->disk->copy($source, $newPath);
            }

            return true;
        } catch (Exception $e) {
            $this->logError('Copy files failed', $e, ['files' => $files, 'destination' => $destPath]);
            throw $e;
        }
    }
    // ==================== Helper Methods ====================

    /**
     * Normalize a path: strip leading slashes for Storage facade compatibility.
     */
    protected function normalizePath(string $path): string
    {
        return ltrim($path, '/');
    }

    /**
     * Processes an image file and returns its metadata.
     */
    protected function handleImage(string $relativePath, string $baseName): array
    {
        $storagePath = $this->mediaDir.'/'.$relativePath;
        $thumbPath   = $storagePath;

        try {
            $mime = $this->disk->mimeType($relativePath);
            if (str_starts_with($mime, 'application/')) {
                $thumbPath = 'images/console/doc.png';
            } elseif (str_starts_with($mime, 'video/')) {
                $thumbPath = 'images/console/video.png';
            }
        } catch (\Exception $e) {
            $mime = '';
        }

        return [
            'id'         => '/'.$relativePath,
            'path'       => '/'.$storagePath,
            'name'       => $baseName,
            'origin_url' => image_origin($storagePath),
            'url'        => image_resize($thumbPath),
            'mime'       => $mime,
            'selected'   => false,
        ];
    }

    /**
     * Collect folders from a directory path.
     */
    protected function collectFolders(string $path): array
    {
        $directories = $this->disk->directories($path);
        $folders     = [];

        foreach ($directories as $directory) {
            $baseName  = basename($directory);
            $dirPath   = '/'.$directory;
            $folders[] = [
                'id'           => $dirPath,
                'name'         => $baseName,
                'path'         => $dirPath,
                'is_dir'       => true,
                'thumb'        => asset('images/icons/folder.png'),
                'url'          => '',
                'mime'         => 'directory',
                'created_time' => $this->disk->lastModified($directory),
            ];
        }

        return $folders;
    }

    /**
     * Collect files from a directory path.
     */
    protected function collectFiles(string $path, string $keyword = ''): array
    {
        $files  = $this->disk->files($path);
        $images = [];

        foreach ($files as $file) {
            $baseName = basename($file);
            if ($this->shouldSkipFile($baseName, $keyword)) {
                continue;
            }

            $fileInfo                 = $this->handleImage($file, $baseName);
            $fileInfo['created_time'] = $this->disk->lastModified($file);
            $images[]                 = $fileInfo;
        }

        return $images;
    }

    protected function shouldSkipFile(string $baseName, string $keyword): bool
    {
        if (in_array($baseName, self::EXCLUDED_FILES, true)) {
            return true;
        }

        return $keyword !== '' && ! str_contains($baseName, $keyword);
    }

    protected function sortItems(array $items, string $sort, string $order): array
    {
        if ($sort === self::SORT_FIELD_CREATED) {
            usort($items, function ($a, $b) use ($order) {
                $timeA = $a['created_time'] ?? 0;
                $timeB = $b['created_time'] ?? 0;

                return ($order === self::SORT_ORDER_DESC) ? $timeB - $timeA : $timeA - $timeB;
            });
        } else {
            usort($items, function ($a, $b) use ($order) {
                if (($a['is_dir'] ?? false) && ! ($b['is_dir'] ?? false)) {
                    return -1;
                }
                if (! ($a['is_dir'] ?? false) && ($b['is_dir'] ?? false)) {
                    return 1;
                }

                return ($order === self::SORT_ORDER_DESC) ?
                    strcasecmp($b['name'], $a['name']) :
                    strcasecmp($a['name'], $b['name']);
            });
        }

        return $items;
    }

    protected function removeTemporaryFields(array $items): array
    {
        return array_map(function ($item) {
            unset($item['created_time']);

            return $item;
        }, $items);
    }

    protected function paginateItems(array $items, int $page, int $perPage): array
    {
        $collection   = collect($items);
        $currentItems = $collection->forPage($page, $perPage);

        return [
            'images'      => $currentItems->values(),
            'image_total' => $collection->count(),
            'image_page'  => $page,
        ];
    }

    protected function validateFilesNotEmpty(array $files): void
    {
        if (empty($files)) {
            throw new Exception(trans('console/file_manager.no_files_selected'));
        }
    }

    protected function validateFilePaths(array $files): array
    {
        $validatedFiles = [];
        foreach ($files as $file) {
            $validatedFiles[] = FileSecurityValidator::validateDirectoryPath($file);
        }

        return $validatedFiles;
    }

    protected function validatePathsNotEmpty(string $sourcePath, string $destPath): void
    {
        if (empty($sourcePath) || empty($destPath)) {
            throw new Exception(trans('console/file_manager.empty_path'));
        }
    }

    protected function validateNotMovingToSubdirectory(string $sourcePath, string $destPath): void
    {
        if (str_starts_with($destPath, $sourcePath.'/')) {
            throw new Exception(trans('console/file_manager.cannot_move_to_subdirectory'));
        }
    }

    protected function logError(string $message, Exception $exception, array $context = []): void
    {
        Log::error($message, array_merge(['error' => $exception->getMessage()], $context));
    }
}
