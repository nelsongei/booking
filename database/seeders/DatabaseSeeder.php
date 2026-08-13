<?php

namespace Database\Seeders;

use App\Domain\Reservation\CreateReservationAction;
use App\Infrastructure\Persistence\Amenity;
use App\Infrastructure\Persistence\MealPlan;
use App\Infrastructure\Persistence\Organization;
use App\Infrastructure\Persistence\Property;
use App\Infrastructure\Persistence\RateDay;
use App\Infrastructure\Persistence\RatePlan;
use App\Infrastructure\Persistence\Room;
use App\Infrastructure\Persistence\RoomType;
use App\Infrastructure\Persistence\Tax;
use App\Infrastructure\Persistence\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Seed Roles ────────────────────────────────────────────────────
        $roles = [
            'platform-admin', 'org-admin', 'general-manager', 'revenue-manager',
            'reservation-agent', 'front-desk-agent', 'housekeeping-supervisor',
            'housekeeper', 'finance-cashier', 'auditor',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // ─── Meal Plans ───────────────────────────────────────────────────
        $mealPlans = [
            ['code' => 'RO', 'name' => 'Room Only', 'description' => 'No meals included'],
            ['code' => 'BB', 'name' => 'Bed & Breakfast', 'description' => 'Buffet breakfast included daily'],
            ['code' => 'HB', 'name' => 'Half Board', 'description' => 'Breakfast and dinner included'],
            ['code' => 'FB', 'name' => 'Full Board', 'description' => 'Breakfast, lunch, and dinner included'],
            ['code' => 'AI', 'name' => 'All Inclusive', 'description' => 'All meals and selected beverages included'],
        ];

        foreach ($mealPlans as $mp) {
            MealPlan::firstOrCreate(['code' => $mp['code']], $mp);
        }

        // ─── Amenities ─────────────────────────────────────────────────────
        $amenities = [
            ['name' => 'Free High-Speed Wi-Fi', 'slug' => 'wifi', 'category' => 'tech', 'icon' => 'bi-wifi'],
            ['name' => 'Air Conditioning', 'slug' => 'ac', 'category' => 'comfort', 'icon' => 'bi-snow'],
            ['name' => 'Flat Screen TV', 'slug' => 'tv', 'category' => 'tech', 'icon' => 'bi-tv'],
            ['name' => 'Mini Bar', 'slug' => 'minibar', 'category' => 'food', 'icon' => 'bi-cup-straw'],
            ['name' => 'Private Balcony', 'slug' => 'balcony', 'category' => 'view', 'icon' => 'bi-sun'],
            ['name' => 'Ocean View', 'slug' => 'ocean-view', 'category' => 'view', 'icon' => 'bi-water'],
            ['name' => 'King Bed', 'slug' => 'king-bed', 'category' => 'comfort', 'icon' => 'bi-door-closed'],
            ['name' => 'Ensuite Bathroom', 'slug' => 'ensuite', 'category' => 'bathroom', 'icon' => 'bi-droplet'],
            ['name' => 'Work Desk', 'slug' => 'desk', 'category' => 'tech', 'icon' => 'bi-laptop'],
            ['name' => 'In-Room Safe', 'slug' => 'safe', 'category' => 'security', 'icon' => 'bi-shield-lock'],
        ];

        foreach ($amenities as $am) {
            Amenity::firstOrCreate(['slug' => $am['slug']], $am);
        }

        // ─── Demo Organization ──────────────────────────────────────────────
        $org = Organization::firstOrCreate(
            ['slug' => 'tembo-hotels'],
            [
                'ulid'             => (string) Str::ulid(),
                'name'             => 'Tembo Hotel',
                'legal_name'       => 'Tembo Hotel Limited',
                'default_currency' => 'USD',
                'default_timezone' => 'Africa/Nairobi',
                'default_locale'   => 'en',
                'country'          => 'KE',
                'email'            => 'admin@tembohotel.com',
                'status'           => 'active',
            ]
        );

        // ─── Demo Properties ────────────────────────────────────────────────
        $propA = Property::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'TH001'],
            [
                'ulid'                 => (string) Str::ulid(),
                'name'                 => 'Tembo House Hotel',
                'slug'                 => 'tembo-hotel',
                'type'                 => 'hotel',
                'description'          => 'As the first beachfront hotel within the UNESCO Heritage Site of Stone Town, Tembo House Hotel combines Swahili heritage, beachfront elegance, and world-class luxury.',
                'address_line1'        => 'P.O. BOX 3974 Shangani, Stone Town',
                'city'                 => 'Zanzibar',
                'country'              => 'TZ',
                'website'              => 'https://tembohotel.com/',
                'currency'             => 'USD',
                'timezone'             => 'Africa/Dar_es_Salaam',
                'locale'               => 'en',
                'star_rating'          => 5,
                'email'                => 'reservations@tembohotel.com',
                'phone'                => '+255 24 223 3005',
                'status'               => 'active',
                'booking_engine_enabled' => true,
                'booking_engine_slug'  => 'tembo-hotel',
                'check_in_out_times'   => ['check_in' => '14:00', 'check_out' => '12:00'],
            ]
        );

        $propB = Property::firstOrCreate(
            ['organization_id' => $org->id, 'code' => 'TH002'],
            [
                'ulid'                 => (string) Str::ulid(),
                'name'                 => 'Tembo Kiwengwa Resort',
                'slug'                 => 'tembo-kiwengwa',
                'type'                 => 'resort',
                'description'          => 'An exclusive beachfront escape on the pristine coastline of Kiwengwa where turquoise waters, 5 swimming pools, Zuri Rituals Spa, and Swahili hospitality create the ultimate island retreat.',
                'address_line1'        => 'Kiwengwa Beach Road',
                'city'                 => 'Kiwengwa, Zanzibar',
                'country'              => 'TZ',
                'website'              => 'https://tembokiwengwaresort.com/',
                'currency'             => 'USD',
                'timezone'             => 'Africa/Dar_es_Salaam',
                'locale'               => 'en',
                'star_rating'          => 5,
                'email'                => 'reservations@tembokiwengwaresort.com',
                'phone'                => '+255 678 413 348',
                'status'               => 'active',
                'booking_engine_enabled' => true,
                'booking_engine_slug'  => 'tembo-kiwengwa',
                'check_in_out_times'   => ['check_in' => '15:00', 'check_out' => '11:00'],
            ]
        );

        // ─── Property Settings / Custom Branding ───────────────────────────
        $propASettings = [
            ['key' => 'theme_primary_color', 'value' => '#c5a059', 'type' => 'string'],
            ['key' => 'theme_accent_color',  'value' => '#d4af37', 'type' => 'string'],
            ['key' => 'theme_dark_color',    'value' => '#0f172a', 'type' => 'string'],
            ['key' => 'tagline',             'value' => 'Seafront Boutique Hotel in Stone Town, Zanzibar', 'type' => 'string'],
            ['key' => 'restaurant_name',     'value' => 'Bahari Restaurant', 'type' => 'string'],
            ['key' => 'hero_badge',          'value' => 'UNESCO Stone Town Heritage', 'type' => 'string'],
            ['key' => 'logo_url',            'value' => '/images/logo-tembo-hotel.png', 'type' => 'string'],
        ];
        foreach ($propASettings as $st) {
            \App\Infrastructure\Persistence\PropertySetting::firstOrCreate(
                ['property_id' => $propA->id, 'key' => $st['key']],
                ['value' => $st['value'], 'type' => $st['type']]
            );
        }

        $propBSettings = [
            ['key' => 'theme_primary_color', 'value' => '#0d9488', 'type' => 'string'],
            ['key' => 'theme_accent_color',  'value' => '#06b6d4', 'type' => 'string'],
            ['key' => 'theme_dark_color',    'value' => '#0a2540', 'type' => 'string'],
            ['key' => 'tagline',             'value' => 'Seafront Luxury Beach Resort in Kiwengwa, Zanzibar', 'type' => 'string'],
            ['key' => 'restaurant_name',     'value' => 'Sea Salt Restaurant', 'type' => 'string'],
            ['key' => 'hero_badge',          'value' => 'Exclusive Kiwengwa Beach Lagoon', 'type' => 'string'],
            ['key' => 'logo_url',            'value' => '/images/logo-tembo-kiwengwa.png', 'type' => 'string'],
        ];
        foreach ($propBSettings as $st) {
            \App\Infrastructure\Persistence\PropertySetting::firstOrCreate(
                ['property_id' => $propB->id, 'key' => $st['key']],
                ['value' => $st['value'], 'type' => $st['type']]
            );
        }

        // ─── Users ─────────────────────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@platform.com'],
            [
                'ulid'             => (string) Str::ulid(),
                'name'             => 'Platform Admin',
                'password'         => Hash::make('password'),
                'organization_id'  => $org->id,
                'status'           => 'active',
                'is_platform_admin'=> true,
            ]
        );
        $admin->syncRoles(['platform-admin']);

        $orgAdmin = User::firstOrCreate(
            ['email' => 'manager@tembohotel.com'],
            [
                'ulid'             => (string) Str::ulid(),
                'name'             => 'Sarah Manager',
                'password'         => Hash::make('password'),
                'organization_id'  => $org->id,
                'status'           => 'active',
                'is_platform_admin'=> false,
            ]
        );
        $orgAdmin->syncRoles(['org-admin']);

        $frontDesk = User::firstOrCreate(
            ['email' => 'reception@tembohotel.com'],
            [
                'ulid'             => (string) Str::ulid(),
                'name'             => 'James Receptionist',
                'password'         => Hash::make('password'),
                'organization_id'  => $org->id,
                'status'           => 'active',
                'is_platform_admin'=> false,
            ]
        );
        $frontDesk->syncRoles(['front-desk-agent']);

        \App\Infrastructure\Persistence\PropertyUserAssignment::firstOrCreate(
            ['user_id' => $frontDesk->id, 'property_id' => $propA->id, 'role_name' => 'front-desk-agent'],
            ['organization_id' => $org->id, 'is_active' => true]
        );

        // ─── Room Types & Rooms for Nairobi ────────────────────────────────
        $deluxeKing = RoomType::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'DLXK'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Deluxe King Room',
                'description'     => 'Spacious room with a plush king bed and city view.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 2,
                'max_children'    => 1,
                'max_occupancy'   => 3,
                'size_sqm'        => 38,
                'view'            => 'city',
                'status'          => 'active',
            ]
        );

        $executiveSuite = RoomType::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'EXEC'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Executive Suite',
                'description'     => 'Luxurious suite with separate living room and lounge access.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 3,
                'max_children'    => 2,
                'max_occupancy'   => 4,
                'size_sqm'        => 65,
                'view'            => 'garden',
                'status'          => 'active',
            ]
        );

        // Create Physical Rooms for Nairobi
        $nairobiRooms = [
            ['room_number' => '101', 'room_type_id' => $deluxeKing->id, 'status' => 'clean'],
            ['room_number' => '102', 'room_type_id' => $deluxeKing->id, 'status' => 'clean'],
            ['room_number' => '103', 'room_type_id' => $deluxeKing->id, 'status' => 'inspected'],
            ['room_number' => '104', 'room_type_id' => $deluxeKing->id, 'status' => 'dirty'],
            ['room_number' => '201', 'room_type_id' => $executiveSuite->id, 'status' => 'clean'],
            ['room_number' => '202', 'room_type_id' => $executiveSuite->id, 'status' => 'clean'],
        ];

        foreach ($nairobiRooms as $rm) {
            Room::firstOrCreate(
                ['property_id' => $propA->id, 'room_number' => $rm['room_number']],
                [
                    'ulid'         => (string) Str::ulid(),
                    'room_type_id' => $rm['room_type_id'],
                    'status'       => $rm['status'],
                ]
            );
        }

        // ─── Rate Plans & Daily Rates for Nairobi ──────────────────────────
        $barPlan = RatePlan::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'BAR'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Best Available Rate',
                'description'     => 'Standard flexible rate with free cancellation up to 24h before arrival.',
                'currency'        => 'KES',
                'is_public'       => true,
                'is_refundable'   => true,
                'breakfast_included' => false,
                'is_active'       => true,
            ]
        );

        $bbPlan = RatePlan::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'BARBB'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Bed & Breakfast Package',
                'description'     => 'Includes full buffet breakfast daily for all registered guests.',
                'currency'        => 'KES',
                'meal_plan_id'    => MealPlan::where('code', 'BB')->first()?->id,
                'is_public'       => true,
                'is_refundable'   => true,
                'breakfast_included' => true,
                'is_active'       => true,
            ]
        );

        $barPlan->roomTypes()->syncWithoutDetaching([$deluxeKing->id, $executiveSuite->id]);
        $bbPlan->roomTypes()->syncWithoutDetaching([$deluxeKing->id, $executiveSuite->id]);

        // Seed 30 days of rates
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i)->toDateString();

            // Deluxe King: 15,000 KES BAR, 18,000 KES BB
            RateDay::updateOrCreate(
                ['property_id' => $propA->id, 'rate_plan_id' => $barPlan->id, 'room_type_id' => $deluxeKing->id, 'date' => $date],
                ['amount_minor' => 1500000, 'currency' => 'KES']
            );
            RateDay::updateOrCreate(
                ['property_id' => $propA->id, 'rate_plan_id' => $bbPlan->id, 'room_type_id' => $deluxeKing->id, 'date' => $date],
                ['amount_minor' => 1800000, 'currency' => 'KES']
            );

            // Executive Suite: 30,000 KES BAR, 33,000 KES BB
            RateDay::updateOrCreate(
                ['property_id' => $propA->id, 'rate_plan_id' => $barPlan->id, 'room_type_id' => $executiveSuite->id, 'date' => $date],
                ['amount_minor' => 3000000, 'currency' => 'KES']
            );
            RateDay::updateOrCreate(
                ['property_id' => $propA->id, 'rate_plan_id' => $bbPlan->id, 'room_type_id' => $executiveSuite->id, 'date' => $date],
                ['amount_minor' => 3300000, 'currency' => 'KES']
            );
        }

        // ─── Taxes for Nairobi ─────────────────────────────────────────────
        Tax::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'VAT16'],
            [
                'organization_id'     => $org->id,
                'name'                => 'Value Added Tax (VAT 16%)',
                'type'                => 'percentage',
                'rate'                => 16.0000,
                'currency'            => 'KES',
                'is_included_in_rate' => false,
                'applies_to_extras'    => true,
                'is_active'            => true,
            ]
        );

        Tax::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'Catering2'],
            [
                'organization_id'     => $org->id,
                'name'                => 'Catering Levy (2%)',
                'type'                => 'percentage',
                'rate'                => 2.0000,
                'currency'            => 'KES',
                'is_included_in_rate' => false,
                'applies_to_extras'    => true,
                'is_active'            => true,
            ]
        );

        // ─── Demo Reservations (Phase 4) ──────────────────────────────────
        $createReservationAction = app(CreateReservationAction::class);

        if (\App\Infrastructure\Persistence\Reservation::where('property_id', $propA->id)->count() === 0) {
            $res1 = $createReservationAction->execute([
                'property_id'      => $propA->id,
                'room_type_id'     => $deluxeKing->id,
                'rate_plan_id'     => $barPlan->id,
                'check_in'         => now()->addDays(2)->toDateString(),
                'check_out'        => now()->addDays(5)->toDateString(),
                'adults'           => 2,
                'children'         => 0,
                'guest_first_name' => 'Alice',
                'guest_last_name'  => 'Johnson',
                'guest_email'      => 'alice.johnson@example.com',
                'guest_phone'      => '+254 700 123 456',
                'special_requests' => 'High floor, quiet corner room if possible.',
                'booking_channel'  => 'staff',
            ]);

            $res2 = $createReservationAction->execute([
                'property_id'      => $propA->id,
                'room_type_id'     => $executiveSuite->id,
                'rate_plan_id'     => $bbPlan->id,
                'check_in'         => now()->addDays(4)->toDateString(),
                'check_out'        => now()->addDays(7)->toDateString(),
                'adults'           => 2,
                'children'         => 1,
                'guest_first_name' => 'David',
                'guest_last_name'  => 'Smith',
                'guest_email'      => 'david.smith@company.com',
                'guest_phone'      => '+254 711 987 654',
                'special_requests' => 'Airport transfer needed.',
                'booking_channel'  => 'staff',
            ]);
        }

        $this->command->info('✅  Seeded: Platform Admin (admin@platform.com / password)');
        $this->command->info('✅  Seeded: Org Admin (manager@tembohotel.com / password)');
        $this->command->info('✅  Seeded: Front Desk (reception@tembohotel.com / password)');
        $this->command->info('✅  Seeded: Organization & Properties');
        $this->command->info('✅  Seeded: Room Types & 6 Physical Rooms');
        $this->command->info('✅  Seeded: Rate Plans & Daily Rates');
        $this->command->info('✅  Seeded: Taxes (VAT 16%, Catering 2%)');
        $this->command->info('✅  Seeded: Demo Reservations & Guest Profiles (Alice Johnson, David Smith)');
    }
}
