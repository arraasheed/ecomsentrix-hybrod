<?php

use App\Models\Store;
use App\Models\Category;
use App\Models\ProductBrand;
use App\Models\Product;
use Illuminate\Support\Str;

$storeSlug = $slug ?? ($store->slug ?? request()->route('storeSlug') ?? 'stylique');
$currentStoreObj = isset($store) && is_object($store) ? $store : Store::where('slug', $storeSlug)->first();
$storeId = $currentStoreObj ? (int) $currentStoreObj->id : 2;
$createdBy = $currentStoreObj ? (int) ($currentStoreObj->created_by ?: 2) : 2;

$defaultCategoryMap = [
    'Electronics' => 'readycart/p-heater.png',
    'Fashion & Clothing' => 'readycart/p-coat.png',
    'Beauty & Personal Care' => 'readycart/p-serum.png',
    'Home & Living' => 'readycart/p-firstaid.png',
    'Grocery & Essentials' => 'readycart/p-grocery.png',
    'Toys & Games' => 'readycart/p-toys.png',
    'Sports & Outdoors' => 'readycart/p-helmet.png',
    'Jewelry & Accessories' => 'readycart/p-necklace.png',
];

$defaultBrands = ['Aerotech', 'Auyora', 'H&M', 'MediPro', 'Natura', 'Ridex'];

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
        'raw_image' => 'readycart/p-heater.png',
        'rating' => 5,
        'reviews' => 12,
        'sold' => 8,
        'badge' => '20% OFF',
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-fan.png',
        'rating' => 4.8,
        'reviews' => 21,
        'sold' => 31,
        'badge' => null,
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-necklace.png',
        'rating' => 5,
        'reviews' => 9,
        'sold' => 14,
        'badge' => '16% OFF',
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-coat.png',
        'rating' => 4.4,
        'reviews' => 14,
        'sold' => 27,
        'badge' => null,
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-firstaid.png',
        'rating' => 4.9,
        'reviews' => 33,
        'sold' => 55,
        'badge' => null,
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-serum.png',
        'rating' => 4.6,
        'reviews' => 48,
        'sold' => 120,
        'badge' => '18% OFF',
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-ring.png',
        'rating' => 4.7,
        'reviews' => 3,
        'sold' => 11,
        'badge' => '17% OFF',
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-helmet.png',
        'rating' => 4.2,
        'reviews' => 17,
        'sold' => 36,
        'badge' => null,
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-skates.png',
        'rating' => 4.5,
        'reviews' => 22,
        'sold' => 40,
        'badge' => '21% OFF',
        'is_demo' => false,
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
        'raw_image' => 'readycart/p-gel.png',
        'rating' => 4.8,
        'reviews' => 26,
        'sold' => 74,
        'badge' => null,
        'is_demo' => false,
    ],
];

// Auto-seed Ready Commerce Categories, Brands & Products into the Store Admin Database once per store
try {
    $hasReadyCartSeeded = Product::where('store_id', $storeId)
        ->where('slug', 'electric-oil-heater')
        ->exists();

    if (!$hasReadyCartSeeded) {
        // Hide old CLI test fixtures ("Test Cotton Shirt", "Test Apparel", etc.) so Admin Panel stays clean
        Category::where('store_id', $storeId)->where('name', 'like', 'Test %')->update(['status' => 0]);
        ProductBrand::where('store_id', $storeId)->where('name', 'like', 'Test %')->update(['status' => 0]);
        Product::where('store_id', $storeId)->where('name', 'like', 'Test %')->update(['status' => 0]);

        $catIdMap = [];
        foreach ($defaultCategoryMap as $catName => $catImg) {
            $cat = Category::firstOrCreate(
                ['store_id' => $storeId, 'name' => $catName],
                [
                    'slug' => Str::slug($catName),
                    'image_path' => $catImg,
                    'image_url' => $catImg,
                    'icon_path' => $catImg,
                    'parent_id' => 0,
                    'trending' => 1,
                    'status' => 1,
                ]
            );
            $catIdMap[$catName] = $cat->id;
        }

        $brandIdMap = [];
        foreach ($defaultBrands as $bName) {
            $brand = ProductBrand::firstOrCreate(
                ['store_id' => $storeId, 'name' => $bName],
                [
                    'slug' => Str::slug($bName),
                    'logo' => 'readycart/logo.svg',
                    'status' => 1,
                    'is_popular' => 1,
                    'created_by' => $createdBy,
                ]
            );
            $brandIdMap[$bName] = $brand->id;
        }

        foreach ($rcDemoProducts as $dp) {
            Product::firstOrCreate(
                ['store_id' => $storeId, 'slug' => $dp['slug']],
                [
                    'name' => $dp['name'],
                    'category_id' => $catIdMap[$dp['category']] ?? null,
                    'brand_id' => $brandIdMap[$dp['brand']] ?? null,
                    'price' => $dp['oldPrice'],
                    'sale_price' => $dp['price'],
                    'product_stock' => 50,
                    'track_stock' => 0,
                    'stock_status' => 'in_stock',
                    'cover_image_path' => $dp['raw_image'],
                    'cover_image_url' => $dp['raw_image'],
                    'trending' => 1,
                    'status' => 1,
                    'variant_product' => 0,
                    'average_rating' => $dp['rating'],
                    'description' => $dp['name'] . ' — High quality product available in store.',
                ]
            );
        }
    }
} catch (\Throwable $e) {
    // Non-blocking if DB is read-only
}

// 1. Live Categories from Store Admin Panel Database
$dbCategories = Category::where('store_id', $storeId)
    ->where('status', 1)
    ->where('name', 'not like', 'Test %')
    ->orderBy('id', 'asc')
    ->get();

$rcCategories = [];
$rcCategoryImages = [];

if ($dbCategories->isNotEmpty()) {
    $fallbackImgs = array_values($defaultCategoryMap);
    foreach ($dbCategories as $idx => $cat) {
        $cName = (string) $cat->name;
        $rcCategories[] = $cName;
        $imgPath = (string) ($cat->image_path ?: $cat->icon_path ?: '');
        if (isset($defaultCategoryMap[$cName]) && (empty($imgPath) || str_contains($imgPath, 'default') || str_contains($imgPath, 'dot.png'))) {
            $rcCategoryImages[$cName] = asset($defaultCategoryMap[$cName]);
        } elseif (!empty($imgPath) && (str_starts_with($imgPath, 'readycart/') || str_starts_with($imgPath, 'http'))) {
            $rcCategoryImages[$cName] = str_starts_with($imgPath, 'http') ? $imgPath : asset($imgPath);
        } elseif (!empty($imgPath) && function_exists('get_file')) {
            $rcCategoryImages[$cName] = get_file($imgPath);
        } else {
            $rcCategoryImages[$cName] = asset($fallbackImgs[$idx % count($fallbackImgs)]);
        }
    }
} else {
    $rcCategories = array_keys($defaultCategoryMap);
    foreach ($defaultCategoryMap as $k => $v) {
        $rcCategoryImages[$k] = asset($v);
    }
}

// 2. Live Brands from Store Admin Panel Database
$dbBrands = ProductBrand::where('store_id', $storeId)
    ->where('status', 1)
    ->where('name', 'not like', 'Test %')
    ->orderBy('id', 'asc')
    ->pluck('name')
    ->filter()
    ->values()
    ->all();

$rcBrands = !empty($dbBrands) ? $dbBrands : $defaultBrands;

// 3. Live Products from Store Admin Panel Database
$dbProducts = Product::where('store_id', $storeId)
    ->where('status', 1)
    ->where('name', 'not like', 'Test %')
    ->with(['ProductData', 'brand'])
    ->orderBy('id', 'asc')
    ->get();

$realDbProducts = $dbProducts->map(function ($p, $idx) use ($rcBrands, $rcCategories, $rcDemoProducts) {
    $rawSale = (float) ($p->sale_price ?: 0);
    $rawPrice = (float) ($p->price ?: 0);

    if ($rawSale > 0 && $rawPrice > 0) {
        $sale = min($rawSale, $rawPrice);
        $orig = max($rawSale, $rawPrice);
    } else {
        $sale = $rawSale > 0 ? $rawSale : $rawPrice;
        $orig = $rawPrice > 0 ? $rawPrice : $sale;
    }

    $discPct = ($orig > $sale && $orig > 0) ? (int) round((($orig - $sale) / $orig) * 100) : 0;

    $imgPath = (string) ($p->cover_image_path ?: $p->cover_image_url ?: '');
    if (str_starts_with($imgPath, 'readycart/')) {
        $imgUrl = asset($imgPath);
    } elseif (str_starts_with($imgPath, 'http')) {
        $imgUrl = $imgPath;
    } elseif (!empty($imgPath) && function_exists('get_file')) {
        $imgUrl = get_file($imgPath);
    } else {
        $imgUrl = asset('readycart/p-heater.png');
    }

    // Match demo metadata (reviews/sold) if this is one of the 10 seeded ReadyCart items
    $demoMatch = collect($rcDemoProducts)->firstWhere('slug', $p->slug);

    return [
        'id' => (int) $p->id,
        'name' => (string) $p->name,
        'slug' => (string) $p->slug,
        'brand' => (string) (data_get($p, 'brand.name') ?: ($demoMatch['brand'] ?? ($rcBrands[$idx % max(1, count($rcBrands))] ?? 'Brand'))),
        'category' => (string) (data_get($p, 'ProductData.name') ?: ($demoMatch['category'] ?? ($rcCategories[$idx % max(1, count($rcCategories))] ?? 'General'))),
        'price' => $sale,
        'oldPrice' => $orig,
        'image' => $imgUrl,
        'rating' => (float) ($p->average_rating ?: ($demoMatch['rating'] ?? 4.8)),
        'reviews' => (int) ($demoMatch['reviews'] ?? (12 + ($idx * 3))),
        'sold' => (int) ($demoMatch['sold'] ?? (18 + ($idx * 7))),
        'badge' => $discPct > 0 ? ($discPct . '% OFF') : ($demoMatch['badge'] ?? null),
        'is_demo' => false,
        'model' => $p,
    ];
})->values()->all();

$rcProducts = !empty($realDbProducts) ? $realDbProducts : $rcDemoProducts;

$rcRotate = function (array $items, int $by): array {
    $count = count($items);
    if ($count === 0) return [];
    $by = $by % $count;
    return array_merge(array_slice($items, $by), array_slice($items, 0, $by));
};

$rcFormatPrice = function ($val): string {
    return '$' . number_format((float) $val, 2, '.', ',');
};

return compact('storeSlug', 'rcCategories', 'rcCategoryImages', 'rcBrands', 'rcDemoProducts', 'rcProducts', 'rcRotate', 'rcFormatPrice');
