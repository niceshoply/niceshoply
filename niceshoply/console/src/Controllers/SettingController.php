<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Console\Controllers;

use Exception;
use Illuminate\Http\Request;
use NiceShoply\Common\Repositories\CatalogRepo;
use NiceShoply\Common\Repositories\CategoryRepo;
use NiceShoply\Common\Repositories\CurrencyRepo;
use NiceShoply\Common\Repositories\MailRepo;
use NiceShoply\Common\Repositories\PageRepo;
use NiceShoply\Common\Repositories\SettingRepo;
use NiceShoply\Common\Repositories\SmsRepo;
use NiceShoply\Common\Repositories\WeightClassRepo;
use NiceShoply\Common\Services\AI\AIServiceManager;
use NiceShoply\Common\Services\SmsService;
use NiceShoply\Console\Repositories\ContentAIRepo;
use NiceShoply\Console\Requests\SettingRequest;
use Throwable;

class SettingController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $data = [
            'locales'        => locales()->toArray(),
            'currencies'     => CurrencyRepo::getInstance()->enabledList()->toArray(),
            'weight_classes' => WeightClassRepo::getInstance()->withActive()->all()->toArray(),
            'categories'     => CategoryRepo::getInstance()->getTwoLevelCategories(),
            'catalogs'       => CatalogRepo::getInstance()->getTopCatalogs(),
            'pages'          => PageRepo::getInstance()->withActive()->builder()->get(),
            'mail_engines'   => MailRepo::getInstance()->getEngines(),
            'sms_gateways'   => SmsRepo::getInstance()->getGateways(),
            'sms_repo'       => SmsRepo::getInstance(),
            'ai_models'      => AIServiceManager::getInstance()->getModelsForSelect(),
            'ai_prompts'     => ContentAIRepo::getInstance()->getPrompts(),
        ];

        return nice_view('console::settings.index', $data);
    }

    /**
     * @param  SettingRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function update(SettingRequest $request): mixed
    {
        $settings = $request->all();
        $tab      = $request->get('tab'); // Get current tab from request

        try {
            // Get old console_name before update
            $oldAdminName = console_name();

            // Get the new console_name from request (before update)
            $newAdminName = ! empty($settings['console_name']) ? $settings['console_name'] : 'console';

            // Save storage settings (plugin settings) separately
            $this->saveStorageSettings($settings);

            // Update settings
            SettingRepo::getInstance()->updateValues($settings);

            // Build redirect URL manually using the new console_name
            // Since routes are registered at boot time, we need to manually construct the URL
            $baseUrl    = request()->getSchemeAndHttpHost();
            $settingUrl = $baseUrl.'/'.$newAdminName.'/settings';

            // Add tab parameter if provided
            if ($tab) {
                $settingUrl .= '?tab='.$tab;
            }

            return redirect($settingUrl)
                ->with('instance', $settings)
                ->with('success', console_trans('common.updated_success'));
        } catch (Exception $e) {
            $errorUrl = console_route('settings.index');
            if ($tab) {
                $errorUrl .= '?tab='.$tab;
            }

            return redirect($errorUrl)->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Extract and save storage (file_manager) plugin settings from the form data.
     */
    private function saveStorageSettings(array &$settings): void
    {
        $map = [
            'fm_driver'     => 'driver',
            'fm_key'        => 'key',
            'fm_secret'     => 'secret',
            'fm_endpoint'   => 'endpoint',
            'fm_bucket'     => 'bucket',
            'fm_region'     => 'region',
            'fm_cdn_domain' => 'cdn_domain',
        ];

        foreach ($map as $formKey => $pluginKey) {
            if (array_key_exists($formKey, $settings)) {
                SettingRepo::getInstance()->updatePluginValue('file_manager', $pluginKey, $settings[$formKey] ?? '');
                unset($settings[$formKey]);
            }
        }
    }

    /**
     * Test SMS sending
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function testSms(Request $request): mixed
    {
        $request->validate([
            'calling_code' => 'required|string|max:10',
            'telephone'    => 'required|string|max:20',
            'type'         => 'required|string|in:register,login,reset',
        ]);

        try {
            $smsService = new SmsService;
            $smsService->sendVerificationCode(
                $request->input('calling_code'),
                $request->input('telephone'),
                $request->input('type')
            );

            return response()->json([
                'success' => true,
                'message' => console_trans('setting.sms_test_success'),
            ]);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            // Translate error message with specific error details
            // Use __() helper which handles translation better than trans()
            $translatedMessage = __('common/sms.send_failed', ['message' => $errorMessage]);

            return response()->json([
                'success' => false,
                'message' => $translatedMessage,
            ], 400);
        }
    }
}
