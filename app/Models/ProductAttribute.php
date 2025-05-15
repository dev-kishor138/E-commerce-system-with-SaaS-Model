<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'product_id',
        'attribute_name',
        'attribute_value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the product.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'product_id' => 'required|exists:products,id',
            'attribute_name' => 'required|string|max:100',
            'attribute_value' => 'required|string',
            'tenant_id' => 'nullable|uuid|exists:tenants,id',
        ];
    }
}
