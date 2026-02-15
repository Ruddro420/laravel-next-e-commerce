<?php

namespace App\Http\Controllers;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttributeController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $attributes = Attribute::with('values')
            ->when($q, fn($qr) => $qr->where('name', 'like', "%$q%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.products.attributes', compact('attributes', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'values' => ['nullable', 'string']
        ]);

        $attribute = Attribute::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'])
        ]);

        // Save values (comma separated)
        if (!empty($data['values'])) {
            $values = explode(',', $data['values']);

            foreach ($values as $v) {
                $attribute->values()->create([
                    'value' => trim($v)
                ]);
            }
        }

        return back()->with('success', 'Attribute created successfully!');
    }

    public function update(Request $request, Attribute $attribute)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'values' => ['nullable', 'string']
        ]);

        $attribute->update([
            'name' => $data['name'],
            'slug' => Str::slug($data['name'])
        ]);

        // Remove old values
        $attribute->values()->delete();

        if (!empty($data['values'])) {
            $values = explode(',', $data['values']);
            foreach ($values as $v) {
                $attribute->values()->create([
                    'value' => trim($v)
                ]);
            }
        }

        return back()->with('success', 'Attribute updated successfully!');
    }

    public function destroy(Attribute $attribute)
    {
        $attribute->delete();
        return back()->with('success', 'Attribute deleted successfully!');
    }
}
