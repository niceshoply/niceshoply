# PayPal 支付插件

基于 PayPal **Orders v2 API** 的官方收银台跳转支付插件，采用 [`srmklive/paypal`](https://github.com/srmklive/laravel-paypal) v3 客户端。

## 支付流程（跳转模式）

1. 顾客在结账页选择 **PayPal**，进入支付页点击「继续支付」。
2. 前端请求 `POST /paypal/create-order`，后端在 PayPal 创建订单并返回 `approve_url`。
3. 浏览器跳转至 PayPal 完成授权。
4. 授权后回跳 `GET /paypal/return`，后端 **服务端捕获扣款**（capture）；`status=COMPLETED` 即支付成功，订单经状态机流转为「已支付」并生成发货单。
5. 另由 **Webhook**（`POST /callback/paypal`）异步兜底确认，防止顾客中途关闭回跳页导致漏单。

## 目录结构

```
plugins/Paypal/
├── Boot.php                     # 注册 REST/移动端支付参数 Hook
├── Controllers/
│   └── PaypalController.php     # 创建订单 / 回跳捕获 / Webhook 回调
├── Lang/
│   ├── en/common.php
│   └── zh-cn/common.php
├── Routes/
│   └── front.php                # 前端路由
├── Services/
│   └── PaypalService.php        # PayPal API 封装（建客户端、下单、捕获、验签）
├── Views/
│   └── payment.blade.php        # 支付按钮页
├── composer.json                # 依赖 srmklive/paypal ^3.0
├── config.json                  # 插件元信息（code=paypal, type=billing）
├── fields.php                   # 后台可配置字段
└── README.zh-cn.md
```

## 后台配置字段

| 字段 | 说明 | 必填 |
|------|------|------|
| `mode` | 运行环境：`sandbox` 沙盒 / `live` 生产 | 是 |
| `client_id` | PayPal 应用 Client ID | 是 |
| `client_secret` | PayPal 应用 Client Secret | 是 |
| `webhook_id` | Webhook ID，用于回调签名校验 | **强烈推荐** |

> 同时需在插件设置的 **可用渠道（available）** 中勾选 `pc_web` / `mobile_web` / `app` 等，PayPal 才会出现在对应端的结账支付方式中。

## 获取 PayPal 凭证

1. 登录 [PayPal Developer Dashboard](https://developer.paypal.com/dashboard/)。
2. **Apps & Credentials** → 创建 App，获取 `Client ID` 与 `Secret`（注意区分 Sandbox / Live）。
3. **Webhooks** → 为本商城添加 Webhook：
   - 回调地址：`https://你的域名/callback/paypal`
   - 订阅事件：`PAYMENT.CAPTURE.COMPLETED`、`CHECKOUT.ORDER.COMPLETED`
   - 创建后复制 **Webhook ID** 填入后台。

## 安全设计

- **验签强制（fail closed）**：未配置 `webhook_id` 时，Webhook 一律拒绝（返回 400），避免伪造 `PAYMENT.CAPTURE.COMPLETED` 报文造成资损。
- **调用 PayPal 验签接口**：每条回调通过 `verify-webhook-signature` 校验传输签名，`verification_status != SUCCESS` 即拒绝。
- **幂等**：订单已处于「已支付」及之后状态时，回跳与回调均直接确认，不重复扣款 / 流转。
- **金额交叉校验**：回调金额与订单应付金额比对，异常写入 `payment` 日志并上报 Sentry。
- **失败重试**：Webhook 处理异常返回 500，由 PayPal 自动重试。

## 货币处理

- 订单总额按下单时汇率（`currency_value`）换算后提交 PayPal。
- 订单货币不在 PayPal 支持列表时自动回退为 `USD`。
- 零位小数货币（HUF / JPY / TWD）金额取整，其余保留两位小数。

## 测试

1. 后台将 `mode` 设为 `sandbox`，填入 Sandbox 凭证。
2. 使用 PayPal [Sandbox 测试账户](https://developer.paypal.com/dashboard/accounts) 下单支付。
3. 在 Developer Dashboard 的 Webhooks 页可重发事件，验证回调与状态流转。

## 许可证

OSL-3.0。本插件使用官方 PayPal Orders v2 API。
