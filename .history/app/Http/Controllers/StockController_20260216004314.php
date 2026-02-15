<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $filter = $request->get('filter'); // low/out/all

        $products = Product::query()
            ->when($q, function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
            })
            ->when($filter === 'low', function ($query) {
                $query->whereNotNull('stock')->where('stock', '>', 0)->where('stock', '<=', 5);
            })
            ->when($filter === 'out', function ($query) {
                $query->whereNotNull('stock')->where('stock', '<=', 0);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.products.stock', compact('products', 'q', 'filter'));
    }

    public function show(Product $product)
    {
        $movements = $product->stockMovements()->latest()->paginate(12);
        return view('pages.products.stock_show', compact('product', 'movements'));
    }

    public function adjust(Request $request, Product $product)
    {
        $data = $request->validate([
            'type' => ['required', 'in:in,out,adjust'],
            'qty' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:190'],
            'note' => ['nullable', 'string', 'max:5000'],
        ]);

        StockService::move(
            $product,
            $data['type'],
            (int)$data['qty'],
            null,
            $data['reason'] ?? 'Manual',
            $data['note'] ?? null
        );

        return back()->with('success', 'Stock updated!');
    }
}
