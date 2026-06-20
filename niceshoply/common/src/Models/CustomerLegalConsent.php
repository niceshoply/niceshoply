<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 客户法律文档同意记录。
 */
class CustomerLegalConsent extends BaseModel
{
    protected $table = 'nice_customer_legal_consents';

    protected $fillable = [
        'customer_id', 'legal_document_id', 'document_version', 'document_type',
        'ip', 'user_agent', 'consented_at',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function legalDocument(): BelongsTo
    {
        return $this->belongsTo(LegalDocument::class, 'legal_document_id');
    }
}
