<?php

namespace App\Http\Controllers;

use App\Models\TaxRate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxRateController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');

        $taxRates = TaxRate::query()
            ->when($q, fn($qr)=>$qr->where('name','like',"%$q%"))
            ->latest()->paginate(10)->withQueryString();

        return view('pages.crm.taxes', compact('taxRates','q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'=>['required','string','max:80'],
            'rate'=>['required','numeric','min:0','max:99.99'],
            'mode'=>['required', Rule::in(['exclusive','inclusive'])],
        ]);

        TaxRate::create($data);
        return back()->with('success','Tax rate added!');
    }

    public function update(Request $request, TaxRate $taxRate)
    {
        $data = $request->validate([
            'name'=>['required','string','max:80'],
            'rate'=>['required','numeric','min:0','max:99.99'],
            'mode'=>['required', Rule::in(['exclusive','inclusive'])],
            'is_active'=>['nullable'],
        ]);

        $data['is_active'] = $request->has('is_active');
        $taxRate->update($data);

        return back()->with('success','Tax rate updated!');
    }

    public function destroy(TaxRate $taxRate)
    {
        $taxRate->delete();
        return back()->with('success','Tax rate deleted!');
    }
}
