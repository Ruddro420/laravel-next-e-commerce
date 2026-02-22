<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GeneralSettingController extends Controller
{
    public function edit()
    {
        $settings = Setting::singleton();
        return view('pages.settings.general', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = Setting::singleton();

        $data = $request->validate([
            'software_name' => ['required','string','max:160'],
            'software_tagline' => ['nullable','string','max:190'],

            'store_name' => ['nullable','string','max:190'],
            'store_email' => ['nullable','email','max:190'],
            'store_phone' => ['nullable','string','max:60'],
            'support_email' => ['nullable','email','max:190'],
            'website' => ['nullable','string','max:190'],

            'address_line1' => ['nullable','string','max:190'],
            'address_line2' => ['nullable','string','max:190'],
            'city' => ['nullable','string','max:120'],
            'state' => ['nullable','string','max:120'],
            'postal_code' => ['nullable','string','max:40'],
            'country' => ['nullable','string','max:120'],

            'currency_code' => ['required','string','max:10'],
            'currency_symbol' => ['required','string','max:10'],
            'currency_position' => ['required','in:before,after'],
            'timezone' => ['required','string','max:64'],
            'date_format' => ['required','string','max:40'],
            'time_format' => ['required','string','max:40'],

            'invoice_prefix' => ['required','string','max:30'],
            'order_prefix' => ['required','string','max:30'],
            'invoice_footer_note' => ['nullable','string','max:4000'],
            'pos_receipt_footer' => ['nullable','string','max:4000'],

            'default_shipping' => ['nullable','numeric','min:0'],
            'tax_enabled' => ['nullable'],
            'invoice_show_logo' => ['nullable'],

            'low_stock_threshold' => ['required','integer','min:0','max:100000'],
            'stock_alert_enabled' => ['nullable'],

            'logo' => ['nullable','image','mimes:png,jpg,jpeg,webp','max:2048'],
            'favicon' => ['nullable','image','mimes:png,jpg,jpeg,webp,ico','max:1024'],
        ]);

        // checkboxes
        $data['tax_enabled'] = $request->has('tax_enabled');
        $data['invoice_show_logo'] = $request->has('invoice_show_logo');
        $data['stock_alert_enabled'] = $request->has('stock_alert_enabled');

        // uploads
        if ($request->hasFile('logo')) {
            if ($settings->logo_path) Storage::disk('public')->delete($settings->logo_path);
            $data['logo_path'] = $request->file('logo')->store('settings', 'public');
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path) Storage::disk('public')->delete($settings->favicon_path);
            $data['favicon_path'] = $request->file('favicon')->store('settings', 'public');
        }

        $settings->update($data);

        return back()->with('success', 'General settings updated successfully!');
    }
}
