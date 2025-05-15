<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Combo extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'item_id',
        'category_id',
        'regular_price',
        'offered_price',
        'start_date',
        'end_date',
        'stock_status',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'offered_price' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'stock_status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::creating(function ($combo) {
            $combo->id = (string) Str::uuid();
        });

        static::deleting(function ($combo) {
            $combo->products()->delete();
            $combo->media()->delete();
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
     * Get the combo products.
     */
    public function products()
    {
        return $this->hasMany(ComboProduct::class);
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
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'item_id' => 'required|uuid|exists:items,id',
            'category_id' => 'nullable|exists:categories,id',
            'regular_price' => 'nullable|numeric|min:0',
            'offered_price' => 'required|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'stock_status' => 'required|in:available,out_of_stock,low_stock',
        ];
    }

    /**
     * Calculate stock status based on combo products.
     */
    public function calculateStockStatus(): string
    {
        $products = $this->products()->with('variant.stock')->get();
        $hasLowStock = false;
        foreach ($products as $comboProduct) {
            $stock = $comboProduct->variant->stock;
            if (!$stock || $stock->stock_quantity < $comboProduct->quantity) {
                return 'out_of_stock';
            }
            if ($stock->stock_quantity <= $stock->low_stock_threshold) {
                $hasLowStock = true;
            }
        }
        return $hasLowStock ? 'low_stock' : 'available';
    }
}
