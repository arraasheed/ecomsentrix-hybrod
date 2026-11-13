<?php

namespace Workdo\FraudPixelShield\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Workdo\FraudPixelShield\Models\CapiDispatchLog;

class FunnelController extends Controller
{
    /**
     * Show the 1-Click Funnel Landing Page
     * Route: /{storeSlug}/offer/{slug}
     */
    public function show(Request $request, $storeSlug, $slug)
    {
        $store = Store::where('slug', $storeSlug)->first();
        if (!$store) {
            abort(404);
        }

        // In a real scenario, we would have an Offer/Funnel model.
        // For now, we'll use Product as the offer source for the MVP.
        $product = Product::where('slug', $slug)->where('store_id', $store->id)->first();
        if (!$product) {
            abort(404);
        }

        // Mocking funnel data for now - this would eventually come from a dedicated table
        $funnel = [
            'title' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'sale_price' => $product->sale_price ?: $product->price,
            'video_url' => 'https://www.youtube.com/embed/dQw4w9WgXcQ', // Placeholder
            'countdown_end' => now()->addHours(12)->toDateTimeString(),
            'image' => $product->image_path ?: 'https://via.placeholder.com/600x400',
        ];

        return view('FraudPixelShield::funnel.landing', compact('store', 'product', 'funnel'));
    }

    /**
     * Handle 1-Click COD Order Submission
     */
    public function orderSubmit(Request $request, $storeSlug)
    {
        $store = Store::where('slug', $storeSlug)->first();
        if (!$store) {
            return response()->json(['status' => 'error', 'message' => 'Store not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'product_id' => 'required|exists:products,id',
            'shipping_method' => 'required|in:inside_dhaka,outside_dhaka',
        ]);

        $product = Product::find($request->product_id);
        $shippingFee = ($request->shipping_method === 'inside_dhaka') ? 70 : 130;
        $productPrice = $product->sale_price ?: $product->price;
        $total = $productPrice + $shippingFee;

        DB::beginTransaction();
        try {
            // Generate a unique order ID
            $orderID = '#' . strtoupper(substr(uniqid(), 7));

            // Create Order with correct eCommerceGo schema
            // Prepare Product JSON (Standard eCommerceGo format)
            $productPrice = $product->sale_price > 0 ? $product->sale_price : $product->price;
            $qty = 1;
            
            $productJson = [
                [
                    "product_id" => $product->id,
                    "id" => $product->id,
                    "name" => $product->name,
                    "image" => $product->cover_image_url ?? '',
                    "orignal_price" => $productPrice,
                    "total_orignal_price" => $productPrice * $qty,
                    "final_price" => $productPrice * $qty,
                    "price" => $productPrice,
                    "qty" => $qty,
                    "variant_id" => 0,
                    "variant_name" => "",
                    "return" => 0
                ]
            ];

            $order = new Order();
            $order->product_order_id = $orderID;
            $order->order_date = now()->format('Y-m-d H:i:s');
            $order->customer_id = 0; // Guest
            $order->is_guest = 1;
            $order->product_id = $product->id;
            $order->product_json = json_encode($productJson);
            $order->product_price = $productPrice;
            $order->coupon_price = 0;
            $order->delivery_price = $shippingFee;
            $order->tax_price = 0;
            $order->final_price = $productPrice + $shippingFee;
            $order->return_price = 0;
            $order->product_order_status = 'Pending';
            $order->payment_type = 'Cash On Delivery';
            $order->payment_status = 'Pending';
            $order->store_id = $store->id;
            
            // Meta Pixel Isolation Logic
            $pixelIsolationEnabled = \App\Models\Utility::GetValueByName('pixel_isolation_enabled', $store->id) === 'on';
            $order->pixel_status = $pixelIsolationEnabled ? 'hold' : 'direct';

            $order->save();

            // Save Billing Details using Model
            \App\Models\OrderBillingDetail::create([
                'order_id' => $order->id,
                'product_order_id' => $order->product_order_id,
                'first_name' => $request->name,
                'last_name' => '',
                'telephone' => $request->phone,
                'address' => $request->address,
                'city' => $request->shipping_method == 'inside_dhaka' ? 'Dhaka' : 'Outside Dhaka',
                'country' => 'Bangladesh',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'অর্ডারটি সফলভাবে গ্রহণ করা হয়েছে!',
                'order_id' => $orderID,
                'pixel_isolation' => $pixelIsolationEnabled
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Funnel Order Failed: " . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'দুঃখিত, অর্ডারটি প্রসেস করা সম্ভব হয়নি আবার চেষ্টা করুন'], 500);
        }
    }
}
