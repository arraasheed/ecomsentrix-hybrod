<?php

namespace Workdo\FraudPixelShield\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Workdo\FraudPixelShield\Models\Funnel;
use App\Models\Product;
use App\Models\Store;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FunnelController extends Controller
{
    // ==================== ADMIN CRUD ====================

    public function index()
    {
        $storeId = getCurrentStore();
        $funnels = Funnel::where('store_id', $storeId)->with('product')->latest()->paginate(15);
        return view('fraud-pixel-shield::funnels.index', compact('funnels'));
    }

    public function create()
    {
        $storeId = getCurrentStore();
        $products = Product::where('store_id', $storeId)->where('status', 'active')->get();
        return view('fraud-pixel-shield::funnels.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|string',
            'countdown_end' => 'nullable|date',
            'theme_color' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        $storeId = getCurrentStore();
        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->title);

        // Ensure unique slug per store
        $originalSlug = $slug;
        $count = 1;
        while (Funnel::where('store_id', $storeId)->where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }

        $reviewGallery = $this->processReviewImages($request);

        Funnel::create([
            'store_id' => $storeId,
            'product_id' => $request->product_id,
            'slug' => $slug,
            'title' => $request->title,
            'description' => $request->description,
            'video_url' => $request->video_url,
            'countdown_end' => $request->countdown_end,
            'review_gallery' => $reviewGallery,
            'status' => $request->status,
            'theme_color' => $request->theme_color ?? '#4f46e5',
        ]);

        return redirect()->route('fraud.funnel.index')->with('success', __('Funnel offer created successfully.'));
    }

    public function edit($id)
    {
        $storeId = getCurrentStore();
        $funnel = Funnel::where('store_id', $storeId)->findOrFail($id);
        $products = Product::where('store_id', $storeId)->where('status', 'active')->get();
        return view('fraud-pixel-shield::funnels.edit', compact('funnel', 'products'));
    }

    public function update(Request $request, $id)
    {
        $storeId = getCurrentStore();
        $funnel = Funnel::where('store_id', $storeId)->findOrFail($id);

        $request->validate([
            'product_id' => 'required|exists:products,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|string',
            'countdown_end' => 'nullable|date',
            'theme_color' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        $slug = Str::slug($request->slug);
        if (Funnel::where('store_id', $storeId)->where('slug', $slug)->where('id', '!=', $funnel->id)->exists()) {
            return back()->with('error', __('Slug already exists for this store.'));
        }

        $reviewGallery = $this->processReviewImages($request, $funnel->review_gallery ?? []);

        $funnel->update([
            'product_id' => $request->product_id,
            'slug' => $slug,
            'title' => $request->title,
            'description' => $request->description,
            'video_url' => $request->video_url,
            'countdown_end' => $request->countdown_end,
            'review_gallery' => $reviewGallery,
            'status' => $request->status,
            'theme_color' => $request->theme_color ?? '#4f46e5',
        ]);

        return redirect()->route('fraud.funnel.index')->with('success', __('Funnel offer updated successfully.'));
    }

    public function destroy($id)
    {
        $storeId = getCurrentStore();
        $funnel = Funnel::where('store_id', $storeId)->findOrFail($id);
        
        // Delete review images
        if ($funnel->review_gallery) {
            $images = json_decode($funnel->review_gallery, true);
            foreach ($images as $img) {
                Storage::disk('public')->delete($img);
            }
        }
        
        $funnel->delete();
        return redirect()->route('fraud.funnel.index')->with('success', __('Funnel offer deleted successfully.'));
    }

    // ==================== PUBLIC LANDING PAGE ====================

    public function show($storeSlug, $slug)
    {
        $store = Store::where('slug', $storeSlug)->where('status', 'active')->firstOrFail();
        $funnel = Funnel::where('store_id', $store->id)->where('slug', $slug)->where('status', 'active')->firstOrFail();
        
        $product = Product::findOrFail($funnel->product_id);

        // Track view
        $funnel->increment('views_count');

        return view('fraud-pixel-shield::funnels.landing', compact('store', 'funnel', 'product'));
    }

    // ==================== ORDER SUBMIT ====================

    public function orderSubmit(Request $request, $storeSlug)
    {
        $store = Store::where('slug', $storeSlug)->where('status', 'active')->firstOrFail();
        
        $request->validate([
            'funnel_id' => 'required|exists:funnels,id',
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^01[3-9]\d{8}$/',
            'address' => 'required|string|max:1000',
            'city' => 'required|string|max:100',
            'area' => 'required|string|max:100',
            'payment_method' => 'required|in:cod,bkash',
        ]);

        $funnel = Funnel::where('store_id', $store->id)->where('id', $request->funnel_id)->where('status', 'active')->firstOrFail();
        $product = Product::findOrFail($request->product_id);

        // Get delivery charge from session or calculate
        $deliveryCharge = session("funnel_delivery_charge_{$funnel->id}_{$request->phone}", 0);

        DB::beginTransaction();
        try {
            // Create Order
            $order = new Order();
            $order->store_id = $store->id;
            $order->customer_id = 0; // Guest
            $order->product_order_id = 'FNL-' . strtoupper(Str::random(10));
            $order->order_date = now();
            $order->customer_name = $request->name;
            $order->customer_phone = $request->phone;
            $order->customer_address = $request->address . ', ' . $request->area . ', ' . $request->city;
            $order->payment_type = $request->payment_method;
            $order->payment_status = 'unpaid';
            $order->status = 'pending';
            $order->subtotal = $product->price;
            $order->delivery_charge = $deliveryCharge;
            $order->grand_total = $product->price + $deliveryCharge;
            $order->pixel_status = 'hold'; // Meta Pixel Isolation
            $order->source = 'funnel_landing';
            $order->save();

            // Create Order Item
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $product->id;
            $orderItem->product_name = $product->name;
            $orderItem->product_price = $product->price;
            $orderItem->quantity = 1;
            $orderItem->subtotal = $product->price;
            $orderItem->save();

            DB::commit();

            // Clear delivery charge session
            session()->forget("funnel_delivery_charge_{$funnel->id}_{$request->phone}");

            // Track funnel conversion
            $funnel->increment('orders_count');

            return response()->json([
                'success' => true,
                'message' => 'অর্ডার সফলভাবে নিশ্চিত হয়েছে!',
                'redirect_url' => route('funnel.order.success', ['storeSlug' => $storeSlug, 'order' => $order->product_order_id]),
                'order_id' => $order->product_order_id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Funnel order error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'অর্ডার প্রসেস করতে সমস্যা হয়েছে আবার চেষ্টা করুন'
            ], 500);
        }
    }

    // ==================== ORDER SUCCESS ====================

    public function orderSuccess($storeSlug, $order)
    {
        $store = Store::where('slug', $storeSlug)->firstOrFail();
        $orderModel = Order::where('store_id', $store->id)->where('product_order_id', $order)->firstOrFail();
        $funnel = null;
        
        if ($orderModel->source === 'funnel_landing') {
            // Find funnel from order items
            $productId = $orderModel->items->first()?->product_id;
            if ($productId) {
                $funnel = Funnel::where('store_id', $store->id)
                    ->where('product_id', $productId)
                    ->where('status', 'active')
                    ->first();
            }
        }

        $order = $orderModel;
        return view('fraud-pixel-shield::funnels.order-success', compact('store', 'funnel', 'order'));
    }

    // ==================== UPLOAD REVIEW IMAGE ====================

    public function uploadReviewImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $request->file('image')->store('funnel-reviews', 'public');
        
        return response()->json([
            'success' => true,
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    // ==================== HELPER METHODS ====================

    private function processReviewImages(Request $request, $existingGallery = [])
    {
        $reviewGallery = [];
        
        // Keep existing images that weren't removed
        if ($request->has('existing_reviews')) {
            $existingReviews = $request->existing_reviews;
            foreach ($existingReviews as $review) {
                if (!empty($review['image'])) {
                    $reviewGallery[] = $review['image'];
                }
            }
        }

        // Add new uploaded images
        if ($request->hasFile('review_images')) {
            foreach ($request->file('review_images') as $image) {
                $path = $image->store('funnel-reviews', 'public');
                $reviewGallery[] = $path;
            }
        }

        // Also handle legacy review data (name, rating, comment)
        if ($request->has('review_names')) {
            foreach ($request->review_names as $index => $name) {
                if (!empty($name)) {
                    // If there's an image at this index, use it
                    $imagePath = null;
                    if (isset($request->review_images[$index])) {
                        $imagePath = $request->review_images[$index]->store('funnel-reviews', 'public');
                    }
                    // Store as array with metadata for future flexibility
                    $reviewGallery[] = [
                        'name' => $name,
                        'rating' => $request->review_ratings[$index] ?? 5,
                        'comment' => $request->review_comments[$index] ?? '',
                        'image' => $imagePath,
                    ];
                }
            }
        }

        return $reviewGallery;
    }
}