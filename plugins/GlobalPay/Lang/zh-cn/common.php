<?php
return [
    'provider' => '支付渠道', 'stripe' => 'Stripe', 'paypal' => 'PayPal',
    'stripe_secret' => 'Stripe Secret Key', 'stripe_webhook' => 'Stripe Webhook Secret',
    'paypal_client' => 'PayPal Client ID', 'paypal_secret' => 'PayPal Secret',
    'title' => '海外支付', 'redirect_tip' => '即将跳转到支付页面…', 'continue_pay' => '继续支付',
    'no_url' => '无法创建支付链接', 'no_stripe' => '请配置 Stripe Secret', 'no_paypal' => '请配置 PayPal 凭证', 'pay_failed' => '创建支付失败',
];
