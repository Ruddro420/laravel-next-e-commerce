<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $productId = $request->get('product_id');

        $products = Product::select('id','name')->orderBy('name')->get();

        $reviews = Review::with('product:id,name')
            ->when($productId, fn($query) => $query->where('product_id', $productId))
            ->when($q, function ($query) use ($q) {
                $query->where('customer_name','like',"%{$q}%")
                      ->orWhere('customer_email','like',"%{$q}%")
                      ->orWhere('comment','like',"%{$q}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.products.reviews', compact('reviews','products','q','productId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => ['required','exists:products,id'],
            'customer_name' => ['required','string','max:120'],
            'customer_email' => ['nullable','email','max:190'],
            'rating' => ['required','integer','min:1','max:5'],
            'comment' => ['nullable','string','max:5000'],
            'is_approved' => ['nullable'],
        ]);

        $data['is_approved'] = $request->has('is_approved');

        Review::create($data);

        return back()->with('success','Review added successfully!');
    }

    public function update(Request $request, Review $review)
    {
        $data = $request->validate([
            'product_id' => ['required','exists:products,id'],
            'customer_name' => ['required','string','max:120'],
            'customer_email' => ['nullable','email','max:190'],
            'rating' => ['required','integer','min:1','max:5'],
            'comment' => ['nullable','string','max:5000'],
            'is_approved' => ['nullable'],
        ]);

        $data['is_approved'] = $request->has('is_approved');

        $review->update($data);

        return back()->with('success','Review updated successfully!');
    }

    public function destroy(Review $review)
    {
        $review->delete();
        return back()->with('success','Review deleted successfully!');
    }
}
