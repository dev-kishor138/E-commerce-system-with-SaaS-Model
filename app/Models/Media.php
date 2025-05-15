<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'tenant_id',
        'mediable_id',
        'mediable_type',
        'media_type',
        'path',
        'alt_text',
        'is_primary',
        'sort_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::creating(function ($media) {
            $media->id = (string) Str::uuid();
        });

        static::saving(function ($media) {
            // Ensure only one primary image per mediable
            if ($media->is_primary) {
                Media::where('mediable_id', $media->mediable_id)
                    ->where('mediable_type', $media->mediable_type)
                    ->where('id', '!=', $media->id)
                    ->update(['is_primary' => false]);
            }
        });
    }

    /**
     * Get the mediable (polymorphic relationship).
     */
    public function mediable()
    {
        return $this->morphTo();
    }

    /**
     * Scope a query to only include primary media.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'tenant_id' => 'nullable|uuid|exists:tenants,id',
            'mediable_id' => 'required|uuid',
            'mediable_type' => 'required|string',
            'media_type' => 'required|string|in:image,video',
            'path' => 'required|string',
            'alt_text' => 'nullable|string|max:255',
            'is_primary' => 'required|boolean',
            'sort_order' => 'required|integer|min:0',
        ];
    }

    /**
     * Get the media URL.
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }

    /**
     * Upload media file.
     */
    public function uploadFile($file): void
    {
        $this->path = $file->store('media', 'public');
        $this->save();
    }
}
