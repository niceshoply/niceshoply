<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 */

namespace NiceShoply\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use NiceShoply\Common\Handlers\TranslationHandler;
use NiceShoply\Common\Models\CustomerLegalConsent;
use NiceShoply\Common\Models\LegalDocument;

/**
 * 法律文档数据访问层。
 */
class LegalDocumentRepo extends BaseRepo
{
    protected string $model = LegalDocument::class;

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'type', 'type' => 'select', 'label' => trans('console/legal.type'), 'options' => self::getTypeOptions(), 'options_key' => 'code', 'options_label' => 'label'],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function getTypeOptions(): array
    {
        return [
            ['code' => LegalDocument::TYPE_PRIVACY, 'label' => trans('console/legal.type_privacy')],
            ['code' => LegalDocument::TYPE_TERMS, 'label' => trans('console/legal.type_terms')],
            ['code' => LegalDocument::TYPE_COOKIE, 'label' => trans('console/legal.type_cookie')],
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function builder(array $filters = []): Builder
    {
        $builder = LegalDocument::query()->with(['translation', 'translations']);

        if (! empty($filters['type'])) {
            $builder->where('type', $filters['type']);
        }

        return $builder->orderByDesc('id');
    }

    /**
     * 获取某类型当前生效文档（最新 active 版本）。
     */
    public function getActiveByType(string $type): ?LegalDocument
    {
        return LegalDocument::query()
            ->where('type', $type)
            ->where('active', true)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * 客户是否已同意当前版本。
     */
    public function hasConsented(int $customerId, LegalDocument $document): bool
    {
        if ($customerId <= 0) {
            return false;
        }

        return CustomerLegalConsent::query()
            ->where('customer_id', $customerId)
            ->where('legal_document_id', $document->id)
            ->where('document_version', $document->version)
            ->exists();
    }

    /**
     * 记录客户同意。
     */
    public function recordConsent(int $customerId, LegalDocument $document, string $ip = '', string $userAgent = ''): CustomerLegalConsent
    {
        return CustomerLegalConsent::query()->create([
            'customer_id'       => $customerId,
            'legal_document_id' => $document->id,
            'document_version'  => $document->version,
            'document_type'     => $document->type,
            'ip'                => $ip,
            'user_agent'        => $userAgent,
            'consented_at'      => Carbon::now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create($data): mixed
    {
        $document = LegalDocument::query()->create([
            'type'              => $data['type'],
            'version'           => $data['version'] ?? '1.0',
            'active'            => (bool) ($data['active'] ?? true),
            'require_reconsent' => (bool) ($data['require_reconsent'] ?? true),
        ]);

        $this->syncTranslations($document, $data['translations'] ?? []);

        return $document->refresh();
    }

    /**
     * @param  mixed  $item
     * @param  array<string, mixed>  $data
     */
    public function update(mixed $item, $data): mixed
    {
        if (is_int($item)) {
            $item = LegalDocument::query()->findOrFail($item);
        }

        $item->update([
            'type'              => $data['type'] ?? $item->type,
            'version'           => $data['version'] ?? $item->version,
            'active'            => (bool) ($data['active'] ?? $item->active),
            'require_reconsent' => (bool) ($data['require_reconsent'] ?? $item->require_reconsent),
        ]);

        if (isset($data['translations'])) {
            $this->syncTranslations($item, $data['translations']);
        }

        return $item->refresh();
    }

    /**
     * @param  array<string, mixed>  $translations
     */
    private function syncTranslations(LegalDocument $document, array $translations): void
    {
        if ($translations === []) {
            return;
        }

        $items = [];
        foreach ($translations as $locale => $fields) {
            if (is_array($fields) && ! isset($fields['locale'])) {
                $items[] = array_merge(['locale' => (string) $locale], $fields);
            } else {
                $items[] = $fields;
            }
        }

        $rows = TranslationHandler::process($items, [
            'title' => ['content'],
        ]);

        if ($rows) {
            $document->translations()->delete();
            $document->translations()->createMany($rows);
        }
    }
}
