<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoryName',
        'slug',
        'image_path',
        'parent_id',
        'approved_by',
        'status',
        'description',
        'sort_order',
        'meta_title',
        'meta_description',
        'meta_keywords',
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
        static::created(function ($category) {
            Log::info('Category created: ' . $category->categoryName);
        });

        static::updated(function ($category) {
            if ($category->wasChanged(['status', 'categoryName', 'slug'])) {
                Log::info('Category updated: ' . $category->categoryName);
            }
        });

        static::deleting(function ($category) {
            $category->children()->update(['parent_id' => null]);
        });
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the user who approved the category.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the products associated with the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /**
     * Scope a query to only include active categories.
     */
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Scope a query to only include root categories.
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope a query to only include approved categories.
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
            'categoryName' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'image_path' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'approved_by' => 'nullable|exists:users,id',
            'status' => 'required|boolean',
            'description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:100',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ];
    }

    /**
     * Generate a unique slug for the category.
     */
    public function generateSlug(): void
    {
        $this->slug = Str::slug($this->categoryName);
        $originalSlug = $this->slug;
        $count = 1;
        while (Category::where('slug', $this->slug)->where('id', '!=', $this->id)->exists()) {
            $this->slug = $originalSlug . '-' . $count++;
        }
    }

    /**
     * Check if the category is active.
     */
    public function isActive(): bool
    {
        return $this->status === true;
    }

    /**
     * Get the category hierarchy tree.
     */
    public function getCategoryTree(): array
    {
        $tree = [];
        $tree[] = $this->categoryName;
        $parent = $this->parent;
        while ($parent) {
            $tree[] = $parent->categoryName;
            $parent = $parent->parent;
        }
        return array_reverse($tree);
    }

    /**
     * Get the image URL.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    /**
     * Get the formatted category name.
     */
    public function getFormattedNameAttribute(): string
    {
        return ucfirst($this->categoryName);
    }
}
