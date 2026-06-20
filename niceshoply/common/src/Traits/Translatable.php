<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Translatable
{
    /**
     * 设置 Description model
     * @return string
     */
    public function getDescriptionModelClass(): string
    {
        return self::class.'\Translation';
    }

    /**
     * Define translations relationship
     *
     * @return HasMany
     */
    public function translations(): HasMany
    {
        $class = $this->getDescriptionModelClass();

        return $this->hasMany($class, $this->getForeignKey(), $this->getKeyName());
    }

    /**
     * Locale translation object
     *
     * @return mixed
     * @throws Exception
     */
    public function translation(): mixed
    {
        $class = $this->getDescriptionModelClass();

        return $this->hasOne($class, $this->getForeignKey(), $this->getKeyName())->where('locale', locale_code());
    }

    /**
     * Translate field by locale
     *
     * @param  $locale
     * @param  $field
     * @return string
     */
    public function translate($locale, $field): string
    {
        return $this->translations->where('locale', $locale)->first()?->{$field} ?? '';
    }

    /**
     * Get translated name.
     *
     * @param  string  $field
     * @return string
     */
    public function translatedName(string $field = 'name'): string
    {
        return $this->translation?->{$field} ?? '';
    }

    /**
     * 获取带回退的翻译字段值。
     * 回退顺序：1. 当前语言 → 2. 系统默认语言 → 3. 任意可用语言。
     *
     * @param  string  $field
     * @return string
     */
    public function fallbackName(string $field = 'name'): string
    {
        // 1. 当前语言
        $translatedName = $this->translatedName($field);
        if ($translatedName) {
            return $translatedName;
        }

        // 2. 系统默认语言
        $defaultName = $this->translate(setting_locale_code(), $field);
        if ($defaultName) {
            return $defaultName;
        }

        // 3. 任意可用语言（避免多语言字段空值时整段缺失）
        return $this->translations->first()?->{$field} ?? '';
    }
}
