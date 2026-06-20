<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'title'    => '系统更新',
    'subtitle' => '在线检查并安装来自官方的最新版本',

    'current_version' => '当前版本',
    'latest_version'  => '最新版本',
    'build'           => '构建号',
    'edition'         => '版本类型',
    'released_at'     => '发布时间',
    'changelog'       => '更新日志',
    'package_size'    => '安装包大小',

    'check_update'          => '检查更新',
    'checking'              => '正在检查…',
    'check_done'            => '检查完成',
    'check_failed'          => '检查更新失败（HTTP :code）',
    'up_to_date'            => '当前已是最新版本',
    'new_version_available' => '发现新版本 :version',

    'start_update'    => '立即更新',
    'start_queued'    => '升级任务已开始，请勿关闭页面',
    'updating'        => '正在升级…',
    'no_update'       => '没有可用的更新',
    'already_running' => '已有升级任务正在执行，请稍候',
    'disabled'        => '在线升级功能已被禁用',
    'no_domain_token' => '尚未绑定域名授权 Token，请先在「应用市场」完成授权',

    // 确认与提示
    'confirm_update'     => '确定要升级到 :version 吗？升级期间网站将进入维护模式。',
    'do_not_close'       => '升级进行中，请勿关闭或刷新此页面，也不要在此期间操作后台。',
    'backup_warning'     => '强烈建议升级前先备份数据库与源码。文件将自动备份以便回滚，但数据库不会自动备份。',
    'maintenance_notice' => '升级期间网站会短暂进入维护模式，前台访客将看到维护页面。',

    'last_upgrade'      => '上次升级',
    'last_upgrade_none' => '暂无升级记录',

    // 进度步骤
    'step_queued'      => '已加入升级队列',
    'step_start'       => '开始升级',
    'step_download'    => '正在下载升级包…',
    'step_verify'      => '正在校验升级包完整性…',
    'step_extract'     => '正在解压升级包…',
    'step_maintenance' => '正在进入维护模式…',
    'step_backup'      => '正在备份原文件…',
    'step_apply'       => '正在覆盖程序文件…',
    'step_migrate'     => '正在执行数据库迁移…',
    'step_cache'       => '正在重建缓存…',
    'step_reload'      => '正在重载运行时…',
    'step_done'        => '升级完成',

    'success_done' => '已成功升级到 :version',
    'rolling_back' => '升级失败，正在回滚到原版本…',

    // 日志
    'log_downloaded'  => '升级包下载完成（:size）',
    'log_checksum_ok' => '完整性校验通过',
    'log_extracted'   => '升级包解压完成',
    'log_backed_up'   => '已备份 :count 个文件',
    'log_applied'     => '已覆盖 :count 个文件',

    // 错误
    'download_failed'      => '升级包下载失败（HTTP :code）',
    'download_empty'       => '下载的升级包为空',
    'size_mismatch'        => '升级包大小与官方声明不一致',
    'checksum_failed'      => '升级包校验失败（SHA256 不匹配），可能已损坏或被篡改',
    'extract_failed'       => '升级包解压失败',
    'php_required'         => '该版本要求 PHP :require，当前为 :current',
    'min_version_required' => '该升级包要求当前版本不低于 :min，请先逐级升级',

    // 状态文案
    'status_idle'    => '空闲',
    'status_queued'  => '排队中',
    'status_running' => '升级中',
    'status_success' => '升级成功',
    'status_failed'  => '升级失败',

    'view_logs' => '查看日志',
    'refresh'   => '刷新',
];
