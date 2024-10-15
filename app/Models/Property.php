<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'area',
        'status',
        'property_type',
        'address',
        'city',
        'state',
        'zip',
        'latitude',
        'longitude',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImages::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'property_favorites');
    }

    /*
    SCOPE
    */

    public function scopeFilterByStatus($query, $status)
    {
        return $query->when($status, function ($query, $status) {
            $query->where('status', $status);
        });
    }

    public function scopeFilterByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->when($minPrice, function ($query, $minPrice) {
            $query->where('price', '>=', $minPrice);
        })
            ->when($maxPrice, function ($query, $maxPrice) {
                $query->where('price', '<=', $maxPrice);
            });
    }

    public function scopeFilterByType($query, $type)
    {
        return $query->when($type, function ($query, $type) {
            $query->where('property_type', $type);
        });
    }

    public function scopeFilterByCity($query, $city)
    {
        return $query->when($city, function ($query, $city) {
            $query->where('city', 'like', '%' . $city . '%');
        });
    }

    public function scopeFilterByAreaRange($query, $minArea, $maxArea)
    {
        return $query->when($minArea, function ($query, $minArea) {
            $query->where('area', '>=', $minArea);
        })
            ->when($maxArea, function ($query, $maxArea) {
                $query->where('area', '<=', $maxArea);
            });
    }

    public function scopeFilterByAddress($query, $address)
    {
        return $query->when($address, function ($query, $address) {
            $query->where(function ($query) use ($address) {
                $query->where('address', 'like', '%' . $address . '%')
                    ->orWhere('city', 'like', '%' . $address . '%')
                    ->orWhere('state', 'like', '%' . $address . '%')
                    ->orWhere('zip', 'like', '%' . $address . '%');
            });
        });
    }

    public function scopeNearLocation($query, $latitude, $longitude, $radius = 10)
    {
        return $query->when($latitude && $longitude, function ($query) use ($latitude, $longitude, $radius) {
            // Haversine formula for calculating distance in kilometers
            $query->selectRaw(
                '*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',
                [$latitude, $longitude, $latitude]
            )
                ->having('distance', '<=', $radius)
                ->orderBy('distance');
        });
    }
}
