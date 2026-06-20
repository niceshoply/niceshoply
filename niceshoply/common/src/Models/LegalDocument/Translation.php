<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Models\LegalDocument;

use NiceShoply\Common\Models\BaseModel;

class Translation extends BaseModel
{
    protected $table = 'nice_legal_document_translations';

    protected $fillable = [
        'legal_document_id', 'locale', 'title', 'content',
    ];
}
