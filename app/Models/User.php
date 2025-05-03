<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'google_id',
        'facebook_id',
        'status',
        'preferred_language',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model with tenant scoping.
     */
    protected static function booted()
    {
        static::addGlobalScope('tenant', function ($builder) {
            if (tenancy()->initialized) {
                $builder->where('tenant_id', tenancy()->tenant->id);
            }
        });
    }

    /**
     * Get the user detail associated with the user.
     */
    public function userDetail()
    {
        return $this->hasOne(UserDetail::class);
    }

    /**
     * Get the subscriptions associated with the user's tenant.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'tenant_id');
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include recently logged-in users.
     */
    public function scopeRecentlyLoggedIn($query, $days = 7)
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to only email verified users.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }
}
