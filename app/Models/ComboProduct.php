<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComboProduct extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'combo_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'discount',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the combo.
     */
    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant.
     */
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Scope a query to only include active combo products.
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
            'combo_id' => 'required|exists:combos,id',
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'required|exists:variants,id',
            'quantity' => 'required|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'status' => 'required|boolean',
        ];
    }
}