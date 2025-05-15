<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'status',
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
        static::created(function ($unit) {
            Log::info('Unit created: ' . $unit->name);
        });

        static::updated(function ($unit) {
            if ($unit->wasChanged(['name', 'slug', 'status'])) {
                Log::info('Unit updated: ' . $unit->name);
            }
        });
    }

    /**
     * Generate a unique slug for the unit.
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
     * Scope a query to only include active units.
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
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:units,slug',
            'status' => 'required|boolean',
            'tenant_id' => 'nullable|uuid|exists:tenants,id',
        ];
    }
}
