<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\PlanUpdatedNotification;
use Illuminate\Support\Facades\Log;

class Plan extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'price',
        'currency',
        'features',
        'billing_cycle',
        'status',
    ];

    protected $casts = [
        'features' => 'array',
        'billing_cycle' => 'string',
        'status' => 'string',
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Boot the model with event handling.
     */
    protected static function booted()
    {
        static::created(function ($plan) {
            Log::info('Plan created: ' . $plan->name);
            // অ্যাডমিনকে নোটিফাই করা যেতে পারে
        });

        static::updated(function ($plan) {
            if ($plan->wasChanged(['status', 'price', 'features'])) {
                Log::info('Plan updated: ' . $plan->name);
                foreach ($plan->tenants as $tenant) {
                    if ($tenant->owner) {
                        $tenant->owner->notify(new PlanUpdatedNotification($plan));
                    }
                }
            }
        });
    }

    /**
     * Get the tenants associated with the plan.
     */
    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'plan_id');
    }

    /**
     * Scope a query to only include active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include monthly plans.
     */
    public function scopeMonthly($query)
    {
        return $query->where('billing_cycle', 'monthly');
    }

    /**
     * Scope a query to only include yearly plans.
     */
    public function scopeYearly($query)
    {
        return $query->where('billing_cycle', 'yearly');
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:plans,name',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'features' => 'nullable|json',
            'billing_cycle' => 'required|in:monthly,yearly',
            'status' => 'required|in:active,inactive,archived',
        ];
    }

    /**
     * Check if the plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? []);
    }

    /**
     * Get the formatted price with currency.
     */
    public function getFormattedPrice(): string
    {
        return number_format($this->price, 2) . ' ' . $this->currency;
    }

    /**
     * Check if the plan is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the plan is yearly.
     */
    public function isYearly(): bool
    {
        return $this->billing_cycle === 'yearly';
    }
}