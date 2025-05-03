<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\SubscriptionCreatedNotification;
use App\Notifications\SubscriptionUpdatedNotification;
use Illuminate\Support\Facades\Log;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'cancelled_at',
        'payment_provider',
        'payment_provider_subscription_id',
        'currency',
    ];

    protected $casts = [
        'status' => 'string',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::created(function ($subscription) {
            Log::info('Subscription created for tenant: ' . $subscription->tenant_id);
            if ($subscription->tenant && $subscription->tenant->owner) {
                $subscription->tenant->owner->notify(new SubscriptionCreatedNotification($subscription));
            }
        });

        static::updated(function ($subscription) {
            if ($subscription->wasChanged(['status', 'ends_at', 'trial_ends_at'])) {
                Log::info('Subscription updated for tenant: ' . $subscription->tenant_id);
                if ($subscription->tenant && $subscription->tenant->owner) {
                    $subscription->tenant->owner->notify(new SubscriptionUpdatedNotification($subscription));
                }
            }
        });
    }

    /**
     * Get the tenant that owns the subscription.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    /**
     * Get the plan associated with the subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include subscriptions on trial.
     */
    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')->where('trial_ends_at', '>=', now());
    }

    /**
     * Scope a query to only include cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'tenant_id' => 'required|uuid|exists:tenants,id',
            'plan_id' => 'required|uuid|exists:plans,id',
            'status' => 'required|in:active,inactive,cancelled',
            'starts_at' => 'required|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'trial_ends_at' => 'nullable|date',
            'cancelled_at' => 'nullable|date',
            'payment_provider' => 'nullable|string|max:255',
            'payment_provider_subscription_id' => 'nullable|string|max:255',
            'currency' => 'required|string|size:3',
        ];
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    /**
     * Check if the subscription is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled' || $this->cancelled_at !== null;
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(): void
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->save();
    }

    /**
     * Renew the subscription.
     */
    public function renew(): void
    {
        $this->ends_at = now()->addMonth(); // অথবা প্ল্যানের বিলিং সাইকেল অনুযায়ী
        $this->status = 'active';
        $this->save();
    }
}