<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $categories = Category::query()
            ->when($q, fn($qr) => $qr->where('name', 'like', "%{$q}%")
                                   ->orWhere('slug', 'like', "%{$q}%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.products.categories', compact('categories', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'slug' => ['nullable','string','max:160','unique:categories,slug'],
            'description' => ['nullable','string','max:2000'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $slug = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['name']);
        $data['slug'] = $this->uniqueSlug($slug);

        $data['image_path'] = null;
        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        }

        Category::create($data);

        return back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'slug' => ['nullable','string','max:160', Rule::unique('categories','slug')->ignore($category->id)],
            'description' => ['nullable','string','max:2000'],
            'image' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $slug = $data['slug'] ? Str::slug($data['slug']) : Str::slug($data['name']);

        // If slug changed, ensure unique
        if ($slug !== $category->slug) {
            $data['slug'] = $this->uniqueSlug($slug, $category->id);
        } else {
            $data['slug'] = $category->slug;
        }

        if ($request->hasFile('image')) {
            // delete old image
            if ($category->image_path) {
                Storage::disk('public')->delete($category->image_path);
            }
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        } else {
            unset($data['image_path']); // don’t overwrite old path with null
        }

        $category->update($data);

        return back()->with('success', 'Category updated successfully!');
    }

    public function destroy(Category $category)
    {
        if ($category->image_path) {
            Storage::disk('public')->delete($category->image_path);
        }
        $category->delete();

        return back()->with('success', 'Category deleted successfully!');
    }

    private function uniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $i = 1;

        while (
            Category::where('slug', $slug)
                ->when($ignoreId, fn($q) => $q->where('id','!=',$ignoreId))
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
