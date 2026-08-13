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
            ['key' => 'logo_dark_url',       'value' => '/images/logo-tembo-hotel-dark.png', 'type' => 'string'],
            ['key' => 'logo_light_url',      'value' => '/images/logo-tembo-hotel-light.png', 'type' => 'string'],
        ];
        foreach ($propASettings as $st) {
            \App\Infrastructure\Persistence\PropertySetting::updateOrCreate(
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
            ['key' => 'logo_dark_url',       'value' => '/images/logo-tembo-kiwengwa-dark.png', 'type' => 'string'],
            ['key' => 'logo_light_url',      'value' => '/images/logo-tembo-kiwengwa-light.png', 'type' => 'string'],
        ];
        foreach ($propBSettings as $st) {
            \App\Infrastructure\Persistence\PropertySetting::updateOrCreate(
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

        // ─── Room Types & Rooms for Tembo House Hotel (Stone Town) ──────────────
        $thhDeluxe = RoomType::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'THH-DLX'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Deluxe Suite',
                'description'     => 'Harmonious blend of modern luxury and timeless elegance, with stunning views of the Indian Ocean and Zanzibar heritage decor.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 2,
                'max_children'    => 1,
                'max_occupancy'   => 3,
                'size_sqm'        => 42,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $thhPres = RoomType::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'THH-PRES'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Presidential Suite',
                'description'     => 'Pinnacle of luxury and refinement with panoramic Indian Ocean views, expansive living spaces, and royal Zanzibari finishes.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 4,
                'max_children'    => 2,
                'max_occupancy'   => 6,
                'size_sqm'        => 110,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $thhDbl = RoomType::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'THH-DBL'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Double Room with Balcony & Sea View',
                'description'     => 'Romantic escape featuring a private balcony with sea views, plush double bed, and serene ocean breezes.',
                'bed_type'        => 'double',
                'base_occupancy'  => 2,
                'max_adults'      => 2,
                'max_children'    => 0,
                'max_occupancy'   => 2,
                'size_sqm'        => 34,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $thhTwin = RoomType::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'THH-TWN'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Twin Room with Balcony & Sea View',
                'description'     => 'Coastal charm featuring a private balcony with sea views, two cozy single beds, and thoughtful modern amenities.',
                'bed_type'        => 'twin',
                'base_occupancy'  => 2,
                'max_adults'      => 2,
                'max_children'    => 1,
                'max_occupancy'   => 3,
                'size_sqm'        => 36,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $thhTriple = RoomType::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'THH-TRP'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Triple Room with Balcony & Sea View',
                'description'     => 'Spacious retreat for families or groups, featuring private balcony with sea views, double and single bedding.',
                'bed_type'        => 'triple',
                'base_occupancy'  => 3,
                'max_adults'      => 3,
                'max_children'    => 1,
                'max_occupancy'   => 4,
                'size_sqm'        => 48,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $thhFreddie = RoomType::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'THH-FMA'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Freddie Mercury Apartment',
                'description'     => 'Inspired by Zanzibar-born icon Freddie Mercury, featuring eclectic artistic decor, spacious living room, kitchenette, and sea view balcony.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 3,
                'max_children'    => 1,
                'max_occupancy'   => 4,
                'size_sqm'        => 75,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        // Physical Rooms for Tembo House Hotel
        $thhRooms = [
            ['room_number' => '101', 'room_type_id' => $thhDbl->id, 'status' => 'clean'],
            ['room_number' => '102', 'room_type_id' => $thhTwin->id, 'status' => 'clean'],
            ['room_number' => '103', 'room_type_id' => $thhTriple->id, 'status' => 'inspected'],
            ['room_number' => '201', 'room_type_id' => $thhDeluxe->id, 'status' => 'clean'],
            ['room_number' => '202', 'room_type_id' => $thhDeluxe->id, 'status' => 'dirty'],
            ['room_number' => '301', 'room_type_id' => $thhPres->id, 'status' => 'clean'],
            ['room_number' => '302', 'room_type_id' => $thhFreddie->id, 'status' => 'clean'],
        ];

        foreach ($thhRooms as $rm) {
            Room::firstOrCreate(
                ['property_id' => $propA->id, 'room_number' => $rm['room_number']],
                ['ulid' => (string) Str::ulid(), 'room_type_id' => $rm['room_type_id'], 'status' => $rm['status']]
            );
        }

        // ─── Room Types & Rooms for Tembo Kiwengwa Resort ──────────────
        $tkrDblBal = RoomType::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'TKR-DBLBAL'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Double Room with Balcony - Pool & Sea View',
                'description'     => 'Panoramic views of the turquoise Indian Ocean and main pool from a private balcony, featuring a king-size bed and modern coastal resort styling.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 2,
                'max_children'    => 1,
                'max_occupancy'   => 3,
                'size_sqm'        => 40,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $tkrTwinBal = RoomType::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'TKR-TWNBAL'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Twin Room with Balcony - Pool & Sea View',
                'description'     => 'Bright and airy twin room with private balcony overlooking the main pool and ocean, two single beds, perfect for sharing.',
                'bed_type'        => 'twin',
                'base_occupancy'  => 2,
                'max_adults'      => 2,
                'max_children'    => 1,
                'max_occupancy'   => 3,
                'size_sqm'        => 40,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $tkrDblPat = RoomType::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'TKR-DBLPAT'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Double Room with Patio - Sea View & Walk-In Pool',
                'description'     => 'Step directly from your private patio into a refreshing shared walk-in pool with uninterrupted ocean views.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 2,
                'max_children'    => 1,
                'max_occupancy'   => 3,
                'size_sqm'        => 45,
                'view'            => 'pool',
                'status'          => 'active',
            ]
        );

        $tkrDblPrv = RoomType::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'TKR-DBLPRV'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Double Room with Patio - Sea View & Private Walk-In Pool',
                'description'     => 'Ultimate coastal privacy featuring your own exclusive walk-in pool just steps from your king bed.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 2,
                'max_children'    => 1,
                'max_occupancy'   => 3,
                'size_sqm'        => 52,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $tkrSteBal = RoomType::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'TKR-STEBAL'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Suite with Balcony - Pool & Sea View',
                'description'     => 'Expansive luxury suite featuring a separate living room, private balcony with ocean views, and premium resort amenities.',
                'bed_type'        => 'king',
                'base_occupancy'  => 2,
                'max_adults'      => 3,
                'max_children'    => 1,
                'max_occupancy'   => 4,
                'size_sqm'        => 70,
                'view'            => 'sea',
                'status'          => 'active',
            ]
        );

        $tkrFamJac = RoomType::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'TKR-FAMJAC'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Family Room with Jacuzzi & Garden View',
                'description'     => 'Spacious family sanctuary featuring a private Jacuzzi tub, two bedrooms, and lush tropical garden views.',
                'bed_type'        => 'family',
                'base_occupancy'  => 4,
                'max_adults'      => 4,
                'max_children'    => 2,
                'max_occupancy'   => 6,
                'size_sqm'        => 85,
                'view'            => 'garden',
                'status'          => 'active',
            ]
        );

        $tkrFamGdn = RoomType::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'TKR-FAMGDN'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Family Room with Garden View',
                'description'     => 'Surrounded by lush tropical gardens, designed for families with ample space and relaxing terrace.',
                'bed_type'        => 'family',
                'base_occupancy'  => 4,
                'max_adults'      => 4,
                'max_children'    => 1,
                'max_occupancy'   => 5,
                'size_sqm'        => 65,
                'view'            => 'garden',
                'status'          => 'active',
            ]
        );

        // Physical Rooms for Tembo Kiwengwa Resort
        $tkrRooms = [
            ['room_number' => '401', 'room_type_id' => $tkrDblBal->id, 'status' => 'clean'],
            ['room_number' => '402', 'room_type_id' => $tkrTwinBal->id, 'status' => 'clean'],
            ['room_number' => '403', 'room_type_id' => $tkrDblPat->id, 'status' => 'clean'],
            ['room_number' => '501', 'room_type_id' => $tkrDblPrv->id, 'status' => 'inspected'],
            ['room_number' => '502', 'room_type_id' => $tkrSteBal->id, 'status' => 'clean'],
            ['room_number' => '601', 'room_type_id' => $tkrFamJac->id, 'status' => 'clean'],
            ['room_number' => '602', 'room_type_id' => $tkrFamGdn->id, 'status' => 'dirty'],
        ];

        foreach ($tkrRooms as $rm) {
            Room::firstOrCreate(
                ['property_id' => $propB->id, 'room_number' => $rm['room_number']],
                ['ulid' => (string) Str::ulid(), 'room_type_id' => $rm['room_type_id'], 'status' => $rm['status']]
            );
        }

        // ─── Rate Plans & Daily Rates for Tembo House Hotel ──────────────────
        $barPlanA = RatePlan::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'BAR'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Best Available Rate',
                'description'     => 'Standard flexible direct booking rate with free cancellation up to 24h before arrival.',
                'currency'        => 'USD',
                'is_public'       => true,
                'is_refundable'   => true,
                'breakfast_included' => false,
                'is_active'       => true,
            ]
        );

        $bbPlanA = RatePlan::firstOrCreate(
            ['property_id' => $propA->id, 'code' => 'BARBB'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Bed & Breakfast Package',
                'description'     => 'Includes luxury buffet breakfast daily at Bahari Restaurant.',
                'currency'        => 'USD',
                'is_public'       => true,
                'is_refundable'   => true,
                'breakfast_included' => true,
                'is_active'       => true,
            ]
        );

        $thhTypes = [$thhDeluxe, $thhPres, $thhDbl, $thhTwin, $thhTriple, $thhFreddie];
        $barPlanA->roomTypes()->syncWithoutDetaching(array_column($thhTypes, 'id'));
        $bbPlanA->roomTypes()->syncWithoutDetaching(array_column($thhTypes, 'id'));

        // Rates mapping for THH (USD)
        $thhPrices = [
            $thhDeluxe->id => 220,
            $thhPres->id   => 450,
            $thhDbl->id    => 180,
            $thhTwin->id   => 180,
            $thhTriple->id => 240,
            $thhFreddie->id=> 350,
        ];

        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i)->toDateString();
            foreach ($thhPrices as $rtId => $basePrice) {
                RateDay::updateOrCreate(
                    ['property_id' => $propA->id, 'rate_plan_id' => $barPlanA->id, 'room_type_id' => $rtId, 'date' => $date],
                    ['amount_minor' => $basePrice * 100, 'currency' => 'USD']
                );
                RateDay::updateOrCreate(
                    ['property_id' => $propA->id, 'rate_plan_id' => $bbPlanA->id, 'room_type_id' => $rtId, 'date' => $date],
                    ['amount_minor' => ($basePrice + 25) * 100, 'currency' => 'USD']
                );
            }
        }

        // ─── Rate Plans & Daily Rates for Tembo Kiwengwa Resort ─────────────
        $barPlanB = RatePlan::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'BAR'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Best Available Rate',
                'description'     => 'Standard flexible direct booking rate with free cancellation.',
                'currency'        => 'USD',
                'is_public'       => true,
                'is_refundable'   => true,
                'breakfast_included' => false,
                'is_active'       => true,
            ]
        );

        $bbPlanB = RatePlan::firstOrCreate(
            ['property_id' => $propB->id, 'code' => 'BARBB'],
            [
                'ulid'            => (string) Str::ulid(),
                'organization_id' => $org->id,
                'name'            => 'Bed & Breakfast Package',
                'description'     => 'Includes luxury buffet breakfast daily at Sea Salt Restaurant.',
                'currency'        => 'USD',
                'is_public'       => true,
                'is_refundable'   => true,
                'breakfast_included' => true,
                'is_active'       => true,
            ]
        );

        $tkrTypes = [$tkrDblBal, $tkrTwinBal, $tkrDblPat, $tkrDblPrv, $tkrSteBal, $tkrFamJac, $tkrFamGdn];
        $barPlanB->roomTypes()->syncWithoutDetaching(array_column($tkrTypes, 'id'));
        $bbPlanB->roomTypes()->syncWithoutDetaching(array_column($tkrTypes, 'id'));

        // Rates mapping for TKR (USD)
        $tkrPrices = [
            $tkrDblBal->id => 250,
            $tkrTwinBal->id=> 250,
            $tkrDblPat->id => 320,
            $tkrDblPrv->id => 390,
            $tkrSteBal->id => 480,
            $tkrFamJac->id => 420,
            $tkrFamGdn->id => 310,
        ];

        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i)->toDateString();
            foreach ($tkrPrices as $rtId => $basePrice) {
                RateDay::updateOrCreate(
                    ['property_id' => $propB->id, 'rate_plan_id' => $barPlanB->id, 'room_type_id' => $rtId, 'date' => $date],
                    ['amount_minor' => $basePrice * 100, 'currency' => 'USD']
                );
                RateDay::updateOrCreate(
                    ['property_id' => $propB->id, 'rate_plan_id' => $bbPlanB->id, 'room_type_id' => $rtId, 'date' => $date],
                    ['amount_minor' => ($basePrice + 30) * 100, 'currency' => 'USD']
                );
            }
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

        // ─── Room Type Images ────────────────────────────────────────────────
        $roomTypeImages = [
            $thhDeluxe->id  => 'images/rooms/thh-deluxe.webp',
            $thhPres->id    => 'images/rooms/thh-presidential.webp',
            $thhDbl->id     => 'images/rooms/thh-double.webp',
            $thhTwin->id    => 'images/rooms/thh-twin.webp',
            $thhTriple->id  => 'images/rooms/thh-triple.webp',
            $thhFreddie->id => 'images/rooms/thh-freddie.webp',

            $tkrDblBal->id  => 'images/rooms/tkr-double-balcony.webp',
            $tkrTwinBal->id => 'images/rooms/tkr-twin-balcony.webp',
            $tkrDblPat->id  => 'images/rooms/tkr-double-patio.webp',
            $tkrDblPrv->id  => 'images/rooms/tkr-double-private.webp',
            $tkrSteBal->id  => 'images/rooms/tkr-suite-balcony.webp',
            $tkrFamJac->id  => 'images/rooms/tkr-family-jacuzzi.webp',
            $tkrFamGdn->id  => 'images/rooms/tkr-family-garden.webp',
        ];

        foreach ($roomTypeImages as $rtId => $imgPath) {
            \App\Infrastructure\Persistence\RoomTypeImage::updateOrCreate(
                ['room_type_id' => $rtId, 'is_primary' => true],
                ['path' => $imgPath, 'alt_text' => 'Suite Photo', 'sort_order' => 1]
            );
        }

        // ─── Demo Reservations (Phase 4) ──────────────────────────────────
        $createReservationAction = app(CreateReservationAction::class);

        if (\App\Infrastructure\Persistence\Reservation::where('property_id', $propA->id)->count() === 0) {
            $res1 = $createReservationAction->execute([
                'property_id'      => $propA->id,
                'room_type_id'     => $thhDeluxe->id,
                'rate_plan_id'     => $barPlanA->id,
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
                'room_type_id'     => $thhPres->id,
                'rate_plan_id'     => $bbPlanA->id,
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
