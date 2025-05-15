<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'brand_name',
        'slug',
        'image_path',
        'status',
        'description',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'approved_by',
        'website_url',
        'social_links',
    ];

    protected $casts = [
        'status' => 'boolean',
        'social_links' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::created(function ($brand) {
            Log::info('Brand created: ' . $brand->brand_name);
        });

        static::updated(function ($brand) {
            if ($brand->wasChanged(['status', 'brand_name', 'slug'])) {
                Log::info('Brand updated: ' . $brand->brand_name);
            }
        });

        static::deleting(function ($brand) {
            $brand->products()->update(['brand_id' => null]);
        });
    }

    /**
     * Get the user who approved the brand.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the products associated with the brand.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'brand_id');
    }

    /**
     * Scope a query to only include active brands.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include approved brands.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by');
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'brand_name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:brands,slug',
            'image_path' => 'nullable|string',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'approved_by' => 'nullable|exists:users,id',
            'website_url' => 'nullable|url',
            'social_links' => 'nullable|array',
        ];
    }

    /**
     * Generate a unique slug for the brand.
     */
    public function generateSlug(): void
    {
        $this->slug = Str::slug($this->brand_name);
        $originalSlug = $this->slug;
        $count = 1;
        while (Brand::where('slug', $this->slug)->where('id', '!=', $this->id)->exists()) {
            $this->slug = $originalSlug . '-' . $count++;
        }
    }

    /**
     * Check if the brand is active.
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    /**
     * Get the formatted brand name.
     */
    public function getFormattedNameAttribute(): string
    {
        return ucfirst($this->brand_name);
    }
}