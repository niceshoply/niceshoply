<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'           => '数据备份',
    'keep_count'     => '保留份数',
    'mysqldump_path' => 'mysqldump 路径（留空用系统 PATH）',

    'title'          => '数据库备份',
    'tip'            => '点击「立即备份」执行 mysqldump 并 gzip 压缩，文件存于 storage/app/backups。也可加入 cron：php artisan backup:run',
    'run'            => '立即备份',
    'created'        => '备份成功：:file',
    'deleted'        => '已删除',
    'delete_failed'  => '删除失败',
    'confirm_del'    => '确认删除该备份？',

    'name'           => '文件名',
    'size'           => '大小',
    'time'           => '时间',
    'download'       => '下载',
    'del'            => '删除',
    'no_data'        => '暂无备份',
];
