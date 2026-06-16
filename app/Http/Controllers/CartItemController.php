<?php

namespace App\Http\Controllers;

use App\Enums\ProductStatusEnum;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class CartItemController extends Controller
{
    public function store(StoreCartItemRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        if ($product->availability_status === ProductStatusEnum::NO_STOCK || $product->stock < 1) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('This product is out of stock.'),
                ], 422);
            }

            return back()->with('cart-error', __('This product is out of stock.'));
        }

        $quantity = $request->integer('quantity', 1);
        $cartItem = CartItem::query()
            ->firstOrNew([
                'user_id' => $request->user()->getKey(),
                'product_id' => $product->getKey(),
            ]);

        $cartItem->quantity = min($product->stock, $cartItem->quantity + $quantity);
        $cartItem->save();

        if ($request->expectsJson()) {
            return response()->json([
                'cart_count' => $request->user()->cartItems()->sum('quantity'),
                'item_quantity' => $cartItem->quantity,
                'message' => __('Product added to cart.'),
            ]);
        }

        return back()->with('cart-status', __('Product added to cart.'));
    }

    public function update(UpdateCartItemRequest $request, Product $product): JsonResponse
    {
        if ($product->availability_status === ProductStatusEnum::NO_STOCK || $product->stock < 1) {
            return response()->json([
                'message' => __('This product is out of stock.'),
            ], 422);
        }

        $cartItem = CartItem::query()
            ->firstOrNew([
                'user_id' => $request->user()->getKey(),
                'product_id' => $product->getKey(),
            ]);

        $cartItem->quantity = min($product->stock, $request->integer('quantity'));
        $cartItem->save();

        return response()->json([
            'cart_count' => $request->user()->cartItems()->sum('quantity'),
            'item_quantity' => $cartItem->quantity,
            'message' => __('Cart updated.'),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        CartItem::query()
            ->whereBelongsTo(request()->user())
            ->whereBelongsTo($product)
            ->delete();

        return response()->json([
            'cart_count' => request()->user()->cartItems()->sum('quantity'),
            'item_quantity' => 0,
            'message' => __('Product removed from cart.'),
        ]);
    }
}
