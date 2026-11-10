@php
    $storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');

    $rcCategories = [
        'Electronics',
        'Fashion & Clothing',
        'Beauty & Personal Care',
        'Home & Living',
        'Grocery & Essentials',
        'Toys & Games',
        'Sports & Outdoors',
        'Jewelry & Accessories',
    ];

    $rcCategoryImages = [
        'Electronics' => asset('readycart/p-heater.png'),
        'Fashion & Clothing' => asset('readycart/p-coat.png'),
        'Beauty & Personal Care' => asset('readycart/p-serum.png'),
        'Home & Living' => asset('readycart/p-firstaid.png'),
        'Grocery & Essentials' => asset('readycart/p-grocery.png'),
        'Toys & Games' => asset('readycart/p-toys.png'),
        'Sports & Outdoors' => asset('readycart/p-helmet.png'),
        'Jewelry & Accessories' => asset('readycart/p-necklace.png'),
    ];

    $rcBrands = ['Aerotech', 'Auyora', 'H&M', 'MediPro', 'Natura', 'Ridex'];

    $rcDemoProducts = [
        [
            'id' => 1,
            'name' => 'Electric Oil Heater',
            'slug' => 'electric-oil-heater',
            'brand' => 'Aerotech',
            'category' => 'Electronics',
            'price' => 111.00,
            'oldPrice' => 139.00,
            'image' => asset('readycart/p-heater.png'),
            'rating' => 5,
            'reviews' => 12,
            'sold' => 8,
            'badge' => '20% OFF',
            'is_demo' => true,
        ],
        [
            'id' => 2,
            'name' => 'Mini Charging Table Fan',
            'slug' => 'mini-charging-table-fan',
            'brand' => 'Aerotech',
            'category' => 'Electronics',
            'price' => 39.00,
            'oldPrice' => 49.00,
            'image' => asset('readycart/p-fan.png'),
            'rating' => 4.8,
            'reviews' => 21,
            'sold' => 31,
            'badge' => null,
            'is_demo' => true,
        ],
        [
            'id' => 3,
            'name' => 'Gold Necklace Set (22 Karat)',
            'slug' => 'gold-necklace-set-22-karat',
            'brand' => 'Auyora',
            'category' => 'Jewelry & Accessories',
            'price' => 83.03,
            'oldPrice' => 99.00,
            'image' => asset('readycart/p-necklace.png'),
            'rating' => 5,
            'reviews' => 9,
            'sold' => 14,
            'badge' => '16% OFF',
            'is_demo' => true,
        ],
        [
            'id' => 4,
            'name' => 'Classic Winter Trench Coat',
            'slug' => 'classic-winter-trench-coat',
            'brand' => 'H&M',
            'category' => 'Fashion & Clothing',
            'price' => 68.02,
            'oldPrice' => 68.02,
            'image' => asset('readycart/p-coat.png'),
            'rating' => 4.4,
            'reviews' => 14,
            'sold' => 27,
            'badge' => null,
            'is_demo' => true,
        ],
        [
            'id' => 5,
            'name' => 'First Aid Kit 185-Piece',
            'slug' => 'first-aid-kit-185-piece',
            'brand' => 'MediPro',
            'category' => 'Home & Living',
            'price' => 40.99,
            'oldPrice' => 40.99,
            'image' => asset('readycart/p-firstaid.png'),
            'rating' => 4.9,
            'reviews' => 33,
            'sold' => 55,
            'badge' => null,
            'is_demo' => true,
        ],
        [
            'id' => 6,
            'name' => 'Vitamin C Brightening Serum',
            'slug' => 'vitamin-c-brightening-serum',
            'brand' => 'Natura',
            'category' => 'Beauty & Personal Care',
            'price' => 26.24,
            'oldPrice' => 32.00,
            'image' => asset('readycart/p-serum.png'),
            'rating' => 4.6,
            'reviews' => 48,
            'sold' => 120,
            'badge' => '18% OFF',
            'is_demo' => true,
        ],
        [
            'id' => 7,
            'name' => 'Regal Eternity Moissanite Gold Ring',
            'slug' => 'regal-eternity-moissanite-gold-ring',
            'brand' => 'H&M',
            'category' => 'Jewelry & Accessories',
            'price' => 69.34,
            'oldPrice' => 84.00,
            'image' => asset('readycart/p-ring.png'),
            'rating' => 4.7,
            'reviews' => 3,
            'sold' => 11,
            'badge' => '17% OFF',
            'is_demo' => true,
        ],
        [
            'id' => 8,
            'name' => 'Full Face Flip-Up Motorcycle Helmet',
            'slug' => 'full-face-flip-up-motorcycle-helmet',
            'brand' => 'Ridex',
            'category' => 'Sports & Outdoors',
            'price' => 32.95,
            'oldPrice' => 32.95,
            'image' => asset('readycart/p-helmet.png'),
            'rating' => 4.2,
            'reviews' => 17,
            'sold' => 36,
            'badge' => null,
            'is_demo' => true,
        ],
        [
            'id' => 9,
            'name' => 'Retro Quad Roller Skates',
            'slug' => 'retro-quad-roller-skates',
            'brand' => 'Ridex',
            'category' => 'Sports & Outdoors',
            'price' => 49.00,
            'oldPrice' => 62.00,
            'image' => asset('readycart/p-skates.png'),
            'rating' => 4.5,
            'reviews' => 22,
            'sold' => 40,
            'badge' => '21% OFF',
            'is_demo' => true,
        ],
        [
            'id' => 10,
            'name' => 'Coconut Oil Shower Gel (300 ml)',
            'slug' => 'coconut-oil-shower-gel-300-ml',
            'brand' => 'Natura',
            'category' => 'Beauty & Personal Care',
            'price' => 27.00,
            'oldPrice' => 27.00,
            'image' => asset('readycart/p-gel.png'),
            'rating' => 4.8,
            'reviews' => 26,
            'sold' => 74,
            'badge' => null,
            'is_demo' => true,
        ],
    ];

    // Check if the store has real non-regression-test products with valid product images
    $rawDbProducts = collect($all_products ?? ($products ?? []));
    $realDbProducts = $rawDbProducts->filter(function ($p) {
        $name = (string) data_get($p, 'name', '');
        $img = (string) data_get($p, 'cover_image_path', '');
        if (str_starts_with($name, 'Test ') || str_contains($img, 'themes/stylique/assets/images/service')) {
            return false;
        }
        return !empty($img) && (is_file(public_path($img)) || is_file(base_path($img)));
    })->map(function ($p, $idx) use ($rcBrands, $rcCategories) {
        $sale = (float) (data_get($p, 'sale_price') ?: data_get($p, 'final_price') ?: data_get($p, 'price', 0));
        $orig = (float) (data_get($p, 'price') ?: $sale);
        if ($orig < $sale) {
            $orig = $sale;
        }
        $discPct = ($orig > $sale && $orig > 0) ? (int) round((($orig - $sale) / $orig) * 100) : 0;
        return [
            'id' => (int) data_get($p, 'id', $idx + 100),
            'name' => (string) data_get($p, 'name', 'Product'),
            'slug' => (string) data_get($p, 'slug', ''),
            'brand' => (string) (data_get($p, 'brand.name') ?: $rcBrands[$idx % count($rcBrands)]),
            'category' => (string) (data_get($p, 'ProductData.name') ?: $rcCategories[$idx % count($rcCategories)]),
            'price' => $sale,
            'oldPrice' => $orig,
            'image' => get_file(data_get($p, 'cover_image_path')),
            'rating' => (float) (data_get($p, 'average_rating') ?: 4.8),
            'reviews' => (int) (data_get($p, 'review_count') ?: 15),
            'sold' => 18 + ($idx * 7),
            'badge' => $discPct > 0 ? ($discPct . '% OFF') : null,
            'is_demo' => false,
            'model' => $p,
        ];
    })->values()->all();

    $rcProducts = count($realDbProducts) >= 6 ? $realDbProducts : $rcDemoProducts;

    $rcRotate = function (array $items, int $by): array {
        $count = count($items);
        if ($count === 0) return [];
        $by = $by % $count;
        return array_merge(array_slice($items, $by), array_slice($items, 0, $by));
    };

    $rcFormatPrice = function ($val): string {
        return '$' . number_format((float) $val, 2, '.', ',');
    };
@endphp

