<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use NiceShoply\Common\Traits\Translatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * 法律文档（隐私政策、服务条款等）。
 */
class LegalDocument extends BaseModel
{
    use LogsActivity;
    use Translatable;

    public const TYPE_PRIVACY = 'privacy';

    public const TYPE_TERMS = 'terms';

    public const TYPE_COOKIE = 'cookie';

    protected $table = 'nice_legal_documents';

    protected $fillable = [
        'type', 'version', 'active', 'require_reconsent',
    ];

    protected $casts = [
        'active'            => 'boolean',
        'require_reconsent' => 'boolean',
    ];

    public function consents(): HasMany
    {
        return $this->hasMany(CustomerLegalConsent::class, 'legal_document_id');
    }

    /**
     * @return class-string
     */
    public function getDescriptionModelClass(): string
    {
        return LegalDocument\Translation::class;
    }

    /**
     * ActivityLog 审计配置（富文本内容经 TranslationHandler + HtmlPurify 净化，此处仅审计元数据）。
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'version', 'active', 'require_reconsent'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "LegalDocument {$eventName}")
            ->useLogName('admin');
    }
}
