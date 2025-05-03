<?php

namespace App\Models;

use App\Notifications\ProfileUpdatedNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'full_name',
        'secondary_email',
        'address',
        'phone_number',
        'city',
        'postal_code',
        'police_station',
        'image',
        'country',
        'date_of_birth',
        'preferred_language',
        'gender',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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

        static::updated(function ($userDetail) {
            if ($userDetail->user) {
                $userDetail->user->notify(new ProfileUpdatedNotification($userDetail));
            }
        });
    }

    /**
     * Get the user associated with the user detail.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include users from a specific country.
     */
    public function scopeFromCountry($query, $country)
    {
        return $query->where('country', $country);
    }

    /**
     * Scope a query to only include users of a specific gender.
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Validation rules for the model.
     */
    public static function rules()
    {
        return [
            'full_name' => 'required|string|max:100',
            'secondary_email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'preferred_language' => 'nullable|string|max:5',
            'gender' => 'nullable|in:male,female,other',
        ];
    }
}
