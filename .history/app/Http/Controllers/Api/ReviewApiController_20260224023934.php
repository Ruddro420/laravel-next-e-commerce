<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewApiController extends Controller
{
    // OPTIONAL: GET /api/reviews?q=&product_id=&page=
    // (approved only by default)
    public function index(Request $request)
    {
        $q = $request->get('q');
        $productId = $request->get('product_id');

        $reviews = Review::with('product:id,name')
            ->where('is_approved', 1)
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('customer_name', 'like', "%{$q}%")
                        ->orWhere('customer_email', 'like', "%{$q}%")
                        ->orWhere('comment', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    // GET /api/products/{id}/reviews?page=
    public function productReviews(Request $request, $id)
    {
        $product = Product::select('id', 'name')->findOrFail($id);

        $reviews = Review::where('product_id', $product->id)
            ->where('is_approved', 1)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // average rating (approved reviews only)
        $avg = Review::where('product_id', $product->id)
            ->where('is_approved', 1)
            ->avg('rating');

        $count = Review::where('product_id', $product->id)
            ->where('is_approved', 1)
            ->count();

        return response()->json([
            'success' => true,
            'product' => $product,
            'stats' => [
                'avg_rating' => $avg ? round((float)$avg, 2) : 0,
                'total_reviews' => $count,
            ],
            'data' => $reviews,
        ]);
    }

    // POST /api/products/{id}/reviews
    public function storeForProduct(Request $request, $id)
    {
        $product = Product::select('id')->findOrFail($id);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'email', 'max:190'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:5000'],
        ]);

        $data['product_id'] = $product->id;

        // ✅ public submit -> should not be auto-approved
        $data['is_approved'] = 0;

        $review = Review::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully! It will appear after approval.',
            'data' => $review,
        ], 201);
    }
}