<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $brands = Brand::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%{$q}%")
                                   ->orWhere('slug', 'like', "%{$q}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.products.brands', compact('brands', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'slug' => ['nullable','string','max:160','unique:brands,slug'],
            'description' => ['nullable','string','max:2000'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $slug = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['slug'] = $this->uniqueSlug($slug);

        $data['image_path'] = null;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('brands', 'public');
        }

        Brand::create($data);

        return back()->with('success', 'Brand created successfully!');
    }

    public function update(Request $request, Brand $brand)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'slug' => ['nullable','string','max:160', Rule::unique('brands','slug')->ignore($brand->id)],
            'description' => ['nullable','string','max:2000'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $slug = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['name']);

        if ($slug !== $brand->slug) {
            $data['slug'] = $this->uniqueSlug($slug, $brand->id);
        } else {
            $data['slug'] = $brand->slug;
        }

        if ($request->hasFile('image')) {
            if ($brand->image_path) {
                Storage::disk('public')->delete($brand->image_path);
            }
            $data['image_path'] = $request->file('image')->store('brands', 'public');
        } else {
            unset($data['image_path']);
        }

        $brand->update($data);

        return back()->with('success', 'Brand updated successfully!');
    }

    public function destroy(Brand $brand)
    {
        if ($brand->image_path) {
            Storage::disk('public')->delete($brand->image_path);
        }
        $brand->delete();

        return back()->with('success', 'Brand deleted successfully!');
    }

    private function uniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $i = 1;

        while (
            Brand::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id','!=',$ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
