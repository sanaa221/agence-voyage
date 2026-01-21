<?php

// app/Models/Destination.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'city',
        'description',
        'featured_image',
        'gallery_images',
    ];

    protected $casts = [
        'gallery_images' => 'array',
    ];

    public function tours()
    {
        return $this->hasMany(Tour::class);
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        return $this->featured_image ? asset('storage/' . $this->featured_image) : null;
    }
}