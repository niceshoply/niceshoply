<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    'menu'           => '短信营销',
    'enabled'        => '启用短信营销',
    'batch_size'     => '每批发送数量',

    'disabled'       => '短信营销未启用',
    'no_sms'         => '短信通道不可用，请安装 easy-sms 并在通知中心配置短信网关',
    'saved'          => '已保存',
    'deleted'        => '已删除',
    'sent'           => '已发送 :sent 条，失败 :fail 条',
    'unsubscribed'   => '您已成功退订营销短信',

    'title'          => '短信营销活动',
    'tip'            => '创建活动并填写网关模板 ID 与 JSON 变量(如 {"code":"SALE2026"})。发送前请确保 NotifyCenter 已开启短信并配置密钥。退订链接：https://域名/sms/unsubscribe?mobile=手机号',
    'recipients'     => '可触达会员(有手机号且未退订)',
    'unsub_count'    => '退订人数',
    'name'           => '活动名称',
    'template_id'    => '短信模板ID',
    'template_data'  => '模板变量(JSON)',
    'status'         => '状态',
    'progress'       => '进度',
    'send'           => '发送',
    'save'           => '保存',
    'del'            => '删除',
    'no_data'        => '暂无活动',
    'confirm_send'   => '确认发送该活动？',
    'confirm_del'    => '确认删除？',
];
