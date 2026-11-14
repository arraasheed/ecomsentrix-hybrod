<?php

namespace Workdo\FraudPixelShield\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Store;

class Funnel extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'product_id',
        'slug',
        'title',
        'description',
        'video_url',
        'countdown_end',
        'review_gallery',
        'status',
        'theme_color',
    ];

    protected $casts = [
        'review_gallery' => 'array',
        'countdown_end' => 'datetime',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
