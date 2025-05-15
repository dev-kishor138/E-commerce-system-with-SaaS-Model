<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Attribute extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'attributable_id',
        'attributable_type',
        'attribute_category',
        'attribute_name',
        'attribute_value',
        'attribute_type',
        'is_filterable',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::creating(function ($attribute) {
            $attribute->id = (string) Str::uuid();
        });
    }

    /**
     * Get the attributable (polymorphic relationship).
     */
    public function attributable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include filterable attributes.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope a query to only include attributes of a specific category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('attribute_category', $category);
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'tenant_id' => 'nullable|uuid|exists:tenants,id',
            'attributable_id' => 'required|uuid',
            'attributable_type' => 'required|string',
            'attribute_category' => 'nullable|string|max:100',
            'attribute_name' => 'required|string|max:100',
            'attribute_value' => 'required|string',
            'attribute_type' => 'required|string|in:text,number,boolean,enum,date',
            'is_filterable' => 'required|boolean',
        ];
    }
}
