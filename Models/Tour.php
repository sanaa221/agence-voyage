<?php

// app/Models/Tour.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tour extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'destination_id',
        'description',
        'duration_days',
        'price_per_person',
        'discount_price',
        'max_group_size',
        'available_slots',
        'is_featured',

    ];

    protected $casts = [
  
        'price_per_person' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_featured' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($tour) {
            if (empty($tour->slug)) {
                $tour->slug = Str::slug($tour->title) . '-' . Str::random(6);
            }
        });
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function getFinalPriceAttribute(): float
    {
        return (float) ($this->discount_price ?? $this->price_per_person);
    }

    public function getDurationAttribute(): string
    {
        return "{$this->duration_days} Days {$this->duration_nights} Nights";
    }

    public function getAverageRatingAttribute(): ?float
    {
        return $this->reviews()->where('is_approved', true)->avg('rating');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_slots', '>', 0);
    }
}