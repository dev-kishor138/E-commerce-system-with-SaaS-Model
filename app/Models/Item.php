<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'created_by',
        'status',
        'item_type',
        'itemable_id',
        'itemable_type',
    ];

    protected $casts = [
        'status' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::creating(function ($item) {
            $item->id = (string) Str::uuid();
            Log::info('Item created: ' . $item->name);
        });

        static::updated(function ($item) {
            if ($item->wasChanged(['name', 'slug', 'status'])) {
                Log::info('Item updated: ' . $item->name);
            }
        });

        static::deleting(function ($item) {
            $item->attributes()->delete();
            if ($item->itemable) {
                $item->itemable->delete();
            }
        });
    }

    /**
     * Get the creator.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the attributes.
     */
    public function attributes()
    {
        return $this->hasMany(Attribute::class);
    }

    /**
     * Get the itemable (polymorphic relationship).
     */
    public function itemable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include active items.
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
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:items,slug',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
            'status' => 'required|boolean',
            'item_type' => 'required|string|in:product,combo',
        ];
    }

    /**
     * Generate a unique slug for the item.
     */
    public function generateSlug(): void
    {
        $this->slug = Str::slug($this->name);
        $originalSlug = $this->slug;
        $count = 1;
        while (self::where('slug', $this->slug)->where('id', '!=', $this->id)->exists()) {
            $this->slug = $originalSlug . '-' . $count++;
        }
    }

    /**
     * Get SEO data.
     */
    public function getSeoData(): array
    {
        return [
            'title' => $this->meta_title ?? $this->name,
            'description' => $this->meta_description ?? $this->description,
            'keywords' => $this->meta_keywords,
        ];
    }
}
