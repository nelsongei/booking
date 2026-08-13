<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles;

    protected $fillable = [
        'ulid', 'organization_id', 'name', 'email', 'password',
        'status', 'is_platform_admin', 'mfa_enabled', 'mfa_secret',
        'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token', 'mfa_secret'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_platform_admin' => 'boolean',
        'mfa_enabled'       => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function propertyAssignments()
    {
        return $this->hasMany(PropertyUserAssignment::class);
    }

    public function assignedProperties()
    {
        return $this->belongsToMany(Property::class, 'property_user_assignments')
                    ->withPivot('role_name', 'is_active', 'expires_at')
                    ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function canAccessProperty(Property $property): bool
    {
        if ($this->is_platform_admin) {
            return true;
        }
        if ($this->organization_id === $property->organization_id) {
            return $this->propertyAssignments()
                ->where('property_id', $property->id)
                ->where('is_active', true)
                ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->exists();
        }
        return false;
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->ulid)) {
                $user->ulid = (string) \Illuminate\Support\Str::ulid();
            }
        });
    }

    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }
}
