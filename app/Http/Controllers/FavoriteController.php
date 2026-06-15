<?php

namespace App\Http\Controllers;

use App\Http\Requests\SyncFavoritesRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function toggle(Request $request, Product $product): JsonResponse
    {
        $favoriteProducts = $request->user()->favoriteProducts();
        $isFavorite = $favoriteProducts
            ->whereKey($product->getKey())
            ->exists();

        if ($isFavorite) {
            $favoriteProducts->detach($product);
        } else {
            $favoriteProducts->syncWithoutDetaching([$product->getKey()]);
        }

        return response()->json([
            'is_favorite' => ! $isFavorite,
        ]);
    }

    public function sync(SyncFavoritesRequest $request): JsonResponse
    {
        $request->user()
            ->favoriteProducts()
            ->syncWithoutDetaching($request->validated('product_ids'));

        return response()->json([
            'favorite_ids' => $request->user()
                ->favoriteProducts()
                ->pluck((new Product)->qualifyColumn('id')),
        ]);
    }
}
