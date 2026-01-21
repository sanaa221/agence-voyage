<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Enums\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_admin', // Keep for compatibility
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
        'role' => UserRole::class,
    ];

    // Relationships
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Role check methods
    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    

    public function isClient(): bool
    {
        return $this->role === UserRole::CLIENT;
    }

    // For backward compatibility - maps role to is_admin
    public function getIsAdminAttribute()
    {
        return $this->role === UserRole::ADMIN;
    }

    public function setIsAdminAttribute($value)
    {
        $this->attributes['role'] = $value ? UserRole::ADMIN->value : UserRole::CLIENT->value;
    }

    // Scopes
    public function scopeClients($query)
    {
        return $query->where('role', UserRole::CLIENT->value);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', UserRole::ADMIN->value);
    }

    // Get all users with their booking count
    public function getBookingCountAttribute()
    {
        return $this->bookings()->count();
    }

    // Check if user has any active bookings
    public function hasActiveBookings(): bool
    {
        return $this->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }
}