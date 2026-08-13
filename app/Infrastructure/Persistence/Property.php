<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ulid', 'organization_id', 'name', 'code', 'slug', 'type',
        'description', 'address_line1', 'address_line2', 'city', 'state',
        'postal_code', 'country', 'latitude', 'longitude',
        'phone', 'email', 'website', 'currency', 'timezone', 'locale',
        'star_rating', 'status', 'booking_engine_enabled', 'booking_engine_slug',
        'check_in_out_times',
    ];

    protected $casts = [
        'booking_engine_enabled' => 'boolean',
        'check_in_out_times'     => 'array',
        'latitude'               => 'decimal:7',
        'longitude'              => 'decimal:7',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function settings()
    {
        return $this->hasMany(PropertySetting::class);
    }

    public function roomTypes()
    {
        return $this->hasMany(RoomType::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function ratePlans()
    {
        return $this->hasMany(RatePlan::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    public function getCheckInTime(): string
    {
        return $this->check_in_out_times['check_in'] ?? '14:00';
    }

    public function getCheckOutTime(): string
    {
        return $this->check_in_out_times['check_out'] ?? '12:00';
    }

    public function getPrimaryColor(): string
    {
        return $this->getSetting('theme_primary_color', str_contains($this->slug, 'kiwengwa') ? '#0d9488' : '#c5a059');
    }

    public function getAccentColor(): string
    {
        return $this->getSetting('theme_accent_color', str_contains($this->slug, 'kiwengwa') ? '#06b6d4' : '#d4af37');
    }

    public function getDarkColor(): string
    {
        return $this->getSetting('theme_dark_color', str_contains($this->slug, 'kiwengwa') ? '#0a2540' : '#0f172a');
    }

    public function getTagline(): string
    {
        return $this->getSetting(
            'tagline',
            str_contains($this->slug, 'kiwengwa')
                ? 'Seafront Luxury Beach Resort in Kiwengwa, Zanzibar'
                : 'Seafront Boutique Hotel in Stone Town, Zanzibar'
        );
    }

    public function getOfficialWebsite(): string
    {
        return $this->website ?? (str_contains($this->slug, 'kiwengwa') ? 'https://tembokiwengwaresort.com/' : 'https://tembohotel.com/');
    }

    public function getRestaurantName(): string
    {
        return $this->getSetting('restaurant_name', str_contains($this->slug, 'kiwengwa') ? 'Sea Salt Restaurant' : 'Bahari Restaurant');
    }

    public function getHeroBadge(): string
    {
        return $this->getSetting('hero_badge', str_contains($this->slug, 'kiwengwa') ? 'Exclusive Kiwengwa Beach Lagoon' : 'UNESCO Stone Town Heritage');
    }

    public function getLogoUrl(): string
    {
        return $this->getSetting(
            'logo_url',
            str_contains($this->slug, 'kiwengwa')
                ? asset('images/logo-tembo-kiwengwa.png')
                : asset('images/logo-tembo-hotel.png')
        );
    }
}
