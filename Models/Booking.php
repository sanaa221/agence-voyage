<?php

// app/Models/Booking.php
namespace App\Models;

use App\Models\Enums\BookingStatus;
use App\Models\Enums\PaymentStatus;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_reference',
        'user_id',
        'tour_id',
        'start_date',
        'number_of_guests',
        'total_price',
        'status',
        'payment_status',
        'admin_notes',
    ];

    protected $casts = [
    'start_date' => 'date',
    'total_price' => 'decimal:2',
    'status' => BookingStatus::class,
    'payment_status' => PaymentStatus::class,
];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BOOK-' . Str::upper(Str::random(8)) . '-' . now()->format('Ymd');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}