<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'item_id',
        'category_id',
        'subcategory_id',
        'sub_subcategory_id',
        'brand_id',
        'unit_id',
        'sku',
        'shipping_charge',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::creating(function ($product) {
            $product->id = (string) Str::uuid();
        });

        static::deleting(function ($product) {
            $product->variants()->delete();
            $product->stocks()->delete();
            $product->attributes()->delete();
            $product->media()->delete();
        });
    }

    /**
     * Get the item.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the category.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the subcategory.
     */
    public function subcategory()
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    /**
     * Get the sub-subcategory.
     */
    public function subSubcategory()
    {
        return $this->belongsTo(Category::class, 'sub_subcategory_id');
    }

    /**
     * Get the brand.
     */
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the unit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the variants.
     */
    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    /**
     * Get the stocks.
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    /**
     * Get the combo products.
     */
    public function comboProducts()
    {
        return $this->hasMany(ComboProduct::class);
    }

    /**
     * Get the attributes.
     */
    public function attributes()
    {
        return $this->morphMany(Attribute::class, 'attributable');
    }

    /**
     * Get the media.
     */
    public function media()
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    /**
     * Get the primary image.
     */
    public function getPrimaryImageAttribute()
    {
        return $this->media()->primary()->first();
    }

    /**
     * Get product details by category.
     */
    public function getDetailsAttribute()
    {
        $categorySlug = $this->category->slug ?? 'general';
        return $this->attributes()->byCategory($categorySlug)->get()->pluck('attribute_value', 'attribute_name');
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'item_id' => 'required|uuid|exists:items,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:categories,id',
            'sub_subcategory_id' => 'nullable|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'unit_id' => 'nullable|exists:units,id',
            'sku' => 'required|string|max:100|unique:products,sku',
            'shipping_charge' => 'required|in:free,paid',
        ];
    }

    /**
     * Generate a unique SKU for the product.
     */
    public function generateSku(): void
    {
        $prefix = strtoupper(substr($this->item->name, 0, 3));
        $this->sku = $prefix . '-' . rand(1000, 9999);
        $count = 1;
        while (self::where('sku', $this->sku)->where('id', '!=', $this->id)->exists()) {
            $this->sku = $prefix . '-' . rand(1000, 9999) . '-' . $count++;
        }
    }
}
