<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace NiceShoply\Common\Models;

use Exception;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;
use NiceShoply\Common\Models\Customer\Favorite;
use NiceShoply\Common\Models\Order\Item;
use NiceShoply\Common\Models\Product\Relation;
use NiceShoply\Common\Models\Product\Sku;
use NiceShoply\Common\Traits\HasPackageFactory;
use NiceShoply\Common\Traits\Replicate;
use NiceShoply\Common\Traits\Translatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends BaseModel
{
    use HasPackageFactory, LogsActivity, Replicate, Searchable, Translatable;

    public $timestamps = true;

    const TYPE_NORMAL = 'normal';

    const TYPE_BUNDLE = 'bundle';

    protected $fillable = [
        'type', 'brand_id', 'images', 'hover_image', 'video', 'price', 'tax_class_id', 'spu_code', 'slug', 'is_virtual', 'variables', 'position',
        'spu_code', 'active', 'weight', 'weight_class', 'sales', 'viewed', 'minimum',
    ];

    protected $casts = [
        'variables'  => 'array',
        'images'     => 'array',
        'video'      => 'json',
        'active'     => 'boolean',
        'is_virtual' => 'boolean',
    ];

    protected $appends = ['image'];

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            foreach (['weight_class', 'spu_code', 'hover_image', 'slug'] as $field) {
                if ($product->{$field} === null) {
                    $product->{$field} = '';
                }
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Product {$eventName}")
            ->useLogName('admin');
    }

    /**
     * Scout 索引名称（Meilisearch / Algolia 的 index 名）。
     *
     * @return string
     */
    public function searchableAs(): string
    {
        return 'products';
    }

    /**
     * 写入搜索引擎的数据结构。
     *
     * 聚合多语言名称、SKU 编码、品牌、分类与价格，使 Meilisearch
     * 能跨语言与变体进行相关性检索，替代数据库 LIKE。
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing(['translations', 'skus', 'brand', 'categories', 'masterSku']);

        return [
            'id'           => (int) $this->id,
            'names'        => $this->translations->pluck('name')->filter()->values()->all(),
            'descriptions' => $this->translations->pluck('summary')->filter()->map(fn ($v) => strip_tags((string) $v))->values()->all(),
            'spu_code'     => (string) $this->spu_code,
            'sku_codes'    => $this->skus->pluck('code')->filter()->values()->all(),
            'brand_id'     => (int) $this->brand_id,
            'brand_name'   => (string) ($this->brand?->name ?? ''),
            'category_ids' => $this->categories->pluck('id')->map(fn ($v) => (int) $v)->values()->all(),
            'price'        => (float) ($this->masterSku->price ?? $this->price),
            'sales'        => (int) $this->sales,
            'active'       => (bool) $this->active,
            'created_at'   => optional($this->created_at)->timestamp,
        ];
    }

    /**
     * 仅索引上架商品。
     *
     * @return bool
     */
    public function shouldBeSearchable(): bool
    {
        return (bool) $this->active;
    }

    /**
     * @return BelongsTo
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    /**
     * @return HasOne
     */
    public function masterSku(): HasOne
    {
        return $this->hasOne(Sku::class)->where('is_default', 1);
    }

    /**
     * Since the attribute is defined within the Laravel core,
     * Please see https://github.com/laravel/framework/blob/11.x/src/Illuminate/Database/Eloquent/Concerns/HasAttributes.php#L52
     * Consequently, the name of the relation is referred to as productAttributes.
     * @return HasMany
     */
    public function productAttributes(): HasMany
    {
        return $this->hasMany(\NiceShoply\Common\Models\Product\Attribute::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function relations(): HasMany
    {
        return $this->hasMany(Relation::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function skus(): HasMany
    {
        return $this->hasMany(Sku::class, 'product_id');
    }

    /**
     * @return BelongsTo
     */
    public function taxClass(): BelongsTo
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * @return BelongsTo
     */
    public function weightClass(): BelongsTo
    {
        return $this->belongsTo(WeightClass::class, 'weight_class', 'code');
    }

    /**
     * @return HasMany
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(Item::class, 'product_id');
    }

    /**
     * @return HasMany
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    /**
     * Bundle relation
     * @return HasMany
     */
    public function bundles(): HasMany
    {
        return $this->hasMany(Product\Bundle::class, 'product_id');
    }

    /**
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories', 'product_id', 'category_id');
    }

    /**
     * @return BelongsToMany
     */
    public function relationProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'relation_id');
    }

    /**
     * @return BelongsToMany
     */
    public function favCustomers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_favorites', 'product_id', 'customer_id');
    }

    /**
     * 获取产品的选项关联
     */
    public function productOptions(): HasMany
    {
        return $this->hasMany(\NiceShoply\Common\Models\Product\Option::class, 'product_id', 'id');
    }

    /**
     * 获取产品的选项值配置
     */
    public function productOptionValues(): HasMany
    {
        return $this->hasMany(\NiceShoply\Common\Models\Product\OptionValue::class, 'product_id', 'id');
    }

    /**
     * 获取产品关联的所有选项（通过产品选项值配置）
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'product_options', 'product_id', 'option_id')
            ->distinct();
    }

    /**
     * 获取产品关联的所有选项值（通过产品选项值配置）
     */
    public function optionValues(): BelongsToMany
    {
        return $this->belongsToMany(OptionValue::class, 'product_option_values', 'product_id', 'option_value_id')
            ->withPivot(['price_adjustment', 'quantity', 'subtract_stock', 'sku', 'weight_adjustment']);
    }

    /**
     * @return mixed
     */
    public function totalQuantity(): int
    {
        return (int) $this->skus->sum('quantity');
    }

    /**
     * @param  int  $customerId
     * @return mixed
     */
    public function hasFavorite(int $customerId = 0): mixed
    {
        if (empty($customerId)) {
            $customerId = current_customer_id();
        }
        if (empty($customerId)) {
            return false;
        }

        return $this->favorites->contains(function ($item) use ($customerId) {
            return $item->customer_id === $customerId;
        });
    }

    /**
     * @return array
     */
    public function groupedAttributes(): array
    {
        $this->loadMissing([
            'productAttributes.attribute.translation',
            'productAttributes.attribute.group.translation',
            'productAttributes.attributeValue.translation',
        ]);
        $attributes = [];
        foreach ($this->productAttributes as $productAttribute) {
            $attribute = $productAttribute->attribute;
            $groupID   = $attribute->attribute_group_id;
            if (! isset($attributes[$groupID]['attribute_group_name'])) {
                $attributes[$groupID]['attribute_group_name'] = $attribute->group->translation->name ?? 'default';
            }

            $attributes[$groupID]['attributes'][] = [
                'attribute'       => $attribute->translation->name ?? '',
                'attribute_value' => $productAttribute->attributeValue->translation->name ?? '',
            ];
        }

        return $attributes;
    }

    /**
     * Check product has many multiple variants.
     *
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->variables || $this->skus->count() > 1;
    }

    /**
     * @return string
     */
    public function getImageAttribute(): string
    {
        $images = $this->images ?? [];

        return $images[0] ?? '';
    }

    /**
     * Get URL
     *
     * @return string
     * @throws Exception
     */
    public function getUrlAttribute(): string
    {
        if ($this->slug) {
            return front_route('products.slug_show', ['slug' => $this->slug]);
        }

        return front_route('products.show', $this);
    }

    /**
     * Get edit URL
     *
     * @return string
     */
    public function getEditUrlAttribute(): string
    {
        return console_route('products.edit', $this);
    }

    /**
     * Get URL
     *
     * @return string
     * @throws Exception
     */
    public function getImageUrlAttribute(): string
    {
        return $this->getImageUrl();
    }

    /**
     * 获取hover图片URL
     *
     * @param  int  $width  图片宽度
     * @param  int  $height  图片高度
     * @return string
     * @throws Exception
     */
    public function getHoverImageUrl(int $width = 600, int $height = 600): string
    {
        if (empty($this->hover_image)) {
            return '';
        }

        return image_resize($this->hover_image, $width, $height);
    }

    /**
     * 检查是否有hover图片
     *
     * @return bool
     */
    public function hasHoverImage(): bool
    {
        return ! empty($this->hover_image);
    }

    /**
     * @param  int  $with
     * @param  int  $height
     * @return string
     * @throws Exception
     */
    public function getImageUrl(int $with = 600, int $height = 600): string
    {
        return image_resize($this->image ?? '', $with, $height);
    }
}
