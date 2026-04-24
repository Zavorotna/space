<?php

namespace App\Http\Controllers;

use App\Models\{ShopProduct, ShopOrder};
use App\Services\{WalletService, LiqPayService};
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $products = ShopProduct::available()->with('media')->paginate(12);
        return view('shop.index', compact('products'));
    }

    public function show(ShopProduct $product)
    {
        $product->load('media');
        return view('shop.show', compact('product'));
    }

    public function purchase(Request $request, ShopProduct $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'payment_method' => 'required|in:coins,card',
        ]);

        if ($product->stock < $validated['quantity']) {
            return back()->with('error', 'Недостатньо товару на складі.');
        }

        $user = $request->user();

        if ($validated['payment_method'] === 'coins') {
            $totalCoins = $product->price_coins * $validated['quantity'];
            try {
                app(WalletService::class)->deduct($user, $totalCoins, 'purchase', "Покупка: {$product->title}");
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }

            $order = ShopOrder::create([
                'user_id' => $user->id,
                'product_id' => $product->id,
                'quantity' => $validated['quantity'],
                'payment_method' => 'coins',
                'total_coins' => $totalCoins,
                'status' => 'paid',
            ]);

            $product->decrement('stock', $validated['quantity']);
            return redirect()->route('shop.index')->with('success', 'Покупку здійснено!');
        }

        // Card payment via LiqPay
        $totalUah = $product->price_uah * $validated['quantity'];
        $orderId = 'shop_' . $product->id . '_user_' . $user->id . '_' . time();

        $order = ShopOrder::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => $validated['quantity'],
            'payment_method' => 'card',
            'total_uah' => $totalUah,
            'status' => 'pending',
            'liqpay_order_id' => $orderId,
        ]);

        $liqpay = new LiqPayService();
        $paymentData = $liqpay->createPayment(
            (int) ($totalUah * 100) / 100,
            $orderId,
            "Покупка: {$product->title}",
            route('shop.index'),
            route('liqpay.callback')
        );

        return view('wallet.liqpay-redirect', compact('paymentData'));
    }

    // Admin: manage products
    public function create() { return view('admin.shop-product-create'); }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_coins' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'photos.*' => 'nullable|image|max:5120',
        ]);

        $validated['price_uah'] = $validated['price_coins'];
        $product = ShopProduct::create($validated);

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $product->addMedia($photo)->toMediaCollection('photos');
            }
        }

        return redirect()->route('admin.shop.index')->with('success', 'Товар додано.');
    }

    public function adminIndex()
    {
        $products = ShopProduct::withTrashed()->latest()->paginate(20);
        return view('admin.shop-products', compact('products'));
    }

    public function adminUpdate(Request $request, ShopProduct $product)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_coins' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);
        $validated['price_uah'] = $validated['price_coins'];
        $product->update($validated);
        return back()->with('success', 'Товар оновлено.');
    }
}
