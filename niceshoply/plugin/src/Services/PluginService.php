<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Plugin\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use NiceShoply\Plugin\Core\Plugin as CPlugin;
use NiceShoply\Plugin\Models\Plugin;
use NiceShoply\Plugin\Repositories\PluginRepo;
use NiceShoply\Plugin\Repositories\SettingRepo;

class PluginService
{
    private PluginRepo $pluginRepo;

    public function __construct()
    {
        $this->pluginRepo = PluginRepo::getInstance();
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Install plugin.
     *
     * @param  CPlugin  $CPlugin
     * @throws Exception
     */
    public function installPlugin(CPlugin $CPlugin): void
    {
        $this->migrateDatabase($CPlugin);
        $type = $CPlugin->getType();
        $code = $CPlugin->getCode();

        $params = [
            'type'     => $type,
            'code'     => $code,
            'priority' => 0,
        ];
        $plugin = $this->pluginRepo->getBuilder($params)->first();
        if (empty($plugin)) {
            Plugin::query()->create($params);
        }

        // Auto-enable plugin after installation
        SettingRepo::getInstance()->updatePluginValue($code, 'active', 1);
    }

    /**
     * Migrate plugin database.
     *
     * @param  CPlugin  $CPlugin
     * @return void
     */
    public function migrateDatabase(CPlugin $CPlugin): void
    {
        $migrationPath = "{$CPlugin->getPath()}/Migrations";
        if (is_dir($migrationPath)) {
            $files = glob($migrationPath.'/*');
            asort($files);

            foreach ($files as $file) {
                $file = str_replace(base_path(), '', $file);
                Artisan::call('migrate', [
                    '--force' => true,
                    '--step'  => 1,
                    '--path'  => $file,
                ]);
            }
        }
    }

    /**
     * Uninstall plugin
     *
     * @param  CPlugin  $CPlugin
     * @return void
     */
    public function uninstallPlugin(CPlugin $CPlugin): void
    {
        $this->rollbackDatabase($CPlugin);
        $code    = $CPlugin->getCode();
        $filters = [
            'type' => $CPlugin->getType(),
            'code' => $code,
        ];
        $this->pluginRepo->getBuilder($filters)->delete();

        // Clean up plugin settings
        SettingRepo::getInstance()->updatePluginValue($code, 'active', 0);
    }

    /**
     * @param  CPlugin  $CPlugin
     * @return void
     */
    public function rollbackDatabase(CPlugin $CPlugin): void
    {
        $migrationPath = "{$CPlugin->getPath()}/Migrations";
        if (! is_dir($migrationPath)) {
            return;
        }

        $files = glob($migrationPath.'/*');
        arsort($files);
        foreach ($files as $file) {
            $file = str_replace(base_path(), '', $file);
            Artisan::call('migrate:rollback', [
                '--force' => true,
                '--step'  => 1,
                '--path'  => $file,
            ]);
        }
    }

    /**
     * 重置插件数据库：回滚 → 迁移 → 填充。
     *
     * 用于后台「重置插件」操作，可在出现脏数据或升级后快速恢复到
     * 干净状态。clearData=true 时会把清空标志透传给 Seeder，由
     * Seeder 自行决定是否先清空旧数据再写入。
     *
     * @param  CPlugin  $CPlugin
     * @param  bool  $clearData  是否在填充前清空插件已有数据
     * @return void
     */
    public function resetPlugin(CPlugin $CPlugin, bool $clearData = false): void
    {
        $this->rollbackDatabase($CPlugin);
        $this->migrateDatabase($CPlugin);
        $this->runSeeders($CPlugin, $clearData);
    }

    /**
     * 手动执行插件的数据填充器（Seeders）。
     *
     * 约定：Seeder 文件位于 {plugin}/Seeders/*.php，命名空间为
     * Plugin\{Dirname}\Seeders\{ClassName}。若 Seeder 的 run() 方法
     * 声明了参数，则把 $clearData 透传过去（用于清空-重填场景）。
     *
     * @param  CPlugin  $CPlugin
     * @param  bool  $clearData  是否在填充前清空插件已有数据
     * @return void
     */
    public function runSeeders(CPlugin $CPlugin, bool $clearData = false): void
    {
        $seederPath = "{$CPlugin->getPath()}/Seeders";
        if (! is_dir($seederPath)) {
            return;
        }

        // 使用目录名拼接命名空间（与插件 PSR-4 前缀 Plugin\{Dirname} 对齐）
        $pluginCode = $CPlugin->getDirname();
        $files      = glob("$seederPath/*.php");
        sort($files);

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClass = "Plugin\\{$pluginCode}\\Seeders\\{$className}";

            if (class_exists($fullClass) && method_exists($fullClass, 'run')) {
                $ref  = new \ReflectionMethod($fullClass, 'run');
                $args = $ref->getNumberOfParameters();

                // 兼容两种 Seeder 签名：run() 与 run(bool $clearData)
                if ($args > 0) {
                    (new $fullClass)->run($clearData);
                } else {
                    (new $fullClass)->run();
                }
            }
        }
    }
}
