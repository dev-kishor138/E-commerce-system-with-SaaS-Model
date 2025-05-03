<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\DomainCreatedNotification;
use App\Notifications\DomainUpdatedNotification;
use Illuminate\Support\Facades\Log;

class Domain extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'domain',
        'tenant_id',
        'is_primary',
        'ssl_enabled',
        'type',
        'ssl_expires_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'ssl_enabled' => 'boolean',
        'type' => 'string',
        'ssl_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::created(function ($domain) {
            Log::info('Domain created: ' . $domain->domain);
            if ($domain->tenant && $domain->tenant->owner) {
                $domain->tenant->owner->notify(new DomainCreatedNotification($domain));
            }
        });

        static::updated(function ($domain) {
            if ($domain->wasChanged(['is_primary', 'ssl_enabled', 'type'])) {
                if ($domain->tenant && $domain->tenant->owner) {
                    $domain->tenant->owner->notify(new DomainUpdatedNotification($domain));
                }
            }
        });
    }

    /**
     * Get the tenant that owns the domain.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Scope a query to only include primary domains.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope a query to only include SSL-enabled domains.
     */
    public function scopeSslEnabled($query)
    {
        return $query->where('ssl_enabled', true);
    }

    /**
     * Scope a query to only include custom domains.
     */
    public function scopeCustom($query)
    {
        return $query->where('type', 'custom');
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'domain' => 'required|string|max:255|unique:domains,domain',
            'tenant_id' => 'required|uuid|exists:tenants,id',
            'is_primary' => 'boolean',
            'ssl_enabled' => 'boolean',
            'type' => 'required|in:custom,subdomain',
            'ssl_expires_at' => 'nullable|date',
        ];
    }

    /**
     * Check if SSL is active and valid.
     */
    public function isSslActive(): bool
    {
        return $this->ssl_enabled && $this->ssl_expires_at && $this->ssl_expires_at->isFuture();
    }

    /**
     * Check if the domain is a custom domain.
     */
    public function isCustomDomain(): bool
    {
        return $this->type === 'custom';
    }

    /**
     * Mark the domain as primary and unset other primary domains for the tenant.
     */
    public function markAsPrimary(): void
    {
        Domain::where('tenant_id', $this->tenant_id)->where('is_primary', true)->update(['is_primary' => false]);
        $this->is_primary = true;
        $this->save();
    }
}