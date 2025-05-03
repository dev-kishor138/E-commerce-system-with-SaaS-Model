<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\TenantCreatedNotification;
use App\Notifications\TenantStatusUpdatedNotification;
use Illuminate\Support\Facades\Log;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'domain',
        'status',
        'owner_id',
        'plan_id',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::created(function ($tenant) {
            Log::info('Tenant created: ' . $tenant->name);
            if ($tenant->owner) {
                $tenant->owner->notify(new TenantCreatedNotification($tenant));
            }
        });

        static::updated(function ($tenant) {
            if ($tenant->wasChanged('status')) {
                if ($tenant->owner) {
                    $tenant->owner->notify(new TenantStatusUpdatedNotification($tenant));
                }
            }
        });
    }

    /**
     * Get the owner of the tenant.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the plan associated with the tenant.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the user who created the tenant.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the users associated with the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'tenant_id');
    }

    /**
     * Scope a query to only include active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include tenants with a specific plan.
     */
    public function scopeByPlan($query, $planId)
    {
        return $query->where('plan_id', $planId);
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:tenants,name',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'status' => 'required|in:active,inactive,suspended',
            'owner_id' => 'required|uuid|exists:users,id',
            'plan_id' => 'nullable|uuid|exists:plans,id',
            'created_by' => 'nullable|uuid|exists:users,id',
            'settings' => 'nullable|json',
        ];
    }

    /**
     * Check if the tenant is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get a setting value by key.
     */
    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    /**
     * Set a setting value by key.
     */
    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        $this->save();
    }
}
