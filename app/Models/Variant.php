<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Variant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'variant_name',
        'sku',
        'regular_price',
        'sale_price',
        'barcode',
        'unit_id',
        'weight',
        'image_path',
        'status',
        'expire_date',
        'manufacture_date',
    ];

    protected $casts = [
        'status' => 'boolean',
        'regular_price' => 'float',
        'sale_price' => 'float',
        'expire_date' => 'date',
        'manufacture_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::created(function ($variant) {
            Log::info('Variant created: ' . $variant->variant_name);
        });

        static::updated(function ($variant) {
            if ($variant->wasChanged(['variant_name', 'status', 'regular_price'])) {
                Log::info('Variant updated: ' . $variant->variant_name);
            }
        });

        static::deleting(function ($variant) {
            $variant->attributes()->delete();
            $variant->stock()->delete();
        });
    }

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the attributes.
     */
    public function attributes()
    {
        return $this->hasMany(VariantAttribute::class);
    }

    /**
     * Get the stock.
     */
    public function stock()
    {
        return $this->hasOne(Stock::class);
    }

    /**
     * Scope a query to only include active variants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'tenant_id' => 'nullable|uuid|exists:tenants,id',
            'product_id' => 'required|exists:products,id',
            'variant_name' => 'required|string|max:191',
            'sku' => 'required|string|max:100|unique:variants,sku',
            'regular_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'barcode' => 'nullable|string|max:100',
            'unit_id' => 'nullable|exists:units,id',
            'weight' => 'nullable|string|max:100',
            'image_path' => 'nullable|string',
            'status' => 'required|boolean',
            'expire_date' => 'nullable|date',
            'manufacture_date' => 'nullable|date',
        ];
    }

    /**
     * Generate a unique SKU for the variant.
     */
    public function generateSku(): void
    {
        $prefix = strtoupper(substr($this->variant_name, 0, 3));
        $this->sku = $prefix . '-' . rand(1000, 9999);
        $count = 1;
        while (self::where('sku', $this->sku)->where('id', '!=', $this->id)->exists()) {
            $this->sku = $prefix . '-' . rand(1000, 9999) . '-' . $count++;
        }
    }

    /**
     * Upload image for the variant.
     */
    public function uploadImage($file): void
    {
        $this->image_path = $file->store('variants', 'public');
        $this->save();
    }
}
