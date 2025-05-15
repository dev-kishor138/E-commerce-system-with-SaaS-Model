<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariantAttribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'variant_id',
        'attribute_name',
        'attribute_value',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the variant.
     */
    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'variant_id' => 'required|exists:variants,id',
            'attribute_name' => 'required|string|max:100',
            'attribute_value' => 'required|string|max:255',
            'tenant_id' => 'nullable|uuid|exists:tenants,id',
        ];
    }
}
