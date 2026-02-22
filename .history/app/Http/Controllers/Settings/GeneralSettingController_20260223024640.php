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
            'software_name' => ['required', 'string', 'max:160'],
            'software_tagline' => ['nullable', 'string', 'max:190'],

            'store_name' => ['nullable', 'string', 'max:190'],
            'store_email' => ['nullable', 'email', 'max:190'],
            'store_phone' => ['nullable', 'string', 'max:60'],
            'support_email' => ['nullable', 'email', 'max:190'],
            'website' => ['nullable', 'string', 'max:190'],

            'address_line1' => ['nullable', 'string', 'max:190'],
            'address_line2' => ['nullable', 'string', 'max:190'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:120'],

            'currency_code' => ['required', 'string', 'max:10'],
            'currency_symbol' => ['required', 'string', 'max:10'],
            'currency_position' => ['required', 'in:before,after'],
            'timezone' => ['required', 'string', 'max:64'],
            'date_format' => ['required', 'string', 'max:40'],
            'time_format' => ['required', 'string', 'max:40'],

            'invoice_prefix' => ['required', 'string', 'max:30'],
            'order_prefix' => ['required', 'string', 'max:30'],
            'invoice_footer_note' => ['nullable', 'string', 'max:4000'],
            'pos_receipt_footer' => ['nullable', 'string', 'max:4000'],

            'default_shipping' => ['nullable', 'numeric', 'min:0'],
            'tax_enabled' => ['nullable'],
            'invoice_show_logo' => ['nullable'],

            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:100000'],
            'stock_alert_enabled' => ['nullable'],

            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,ico', 'max:1024'],
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
    // api for settings
    public function apiGetGeneralSettings()
    {
        $settings = Setting::singleton();

        return response()->json([
            'success' => true,
            'data' => [
                'software_name' => $settings->software_name,
                'software_tagline' => $settings->software_tagline,

                'store_name' => $settings->store_name,
                'store_email' => $settings->store_email,
                'store_phone' => $settings->store_phone,
                'support_email' => $settings->support_email,
                'website' => $settings->website,

                'address_line1' => $settings->address_line1,
                'address_line2' => $settings->address_line2,
                'city' => $settings->city,
                'state' => $settings->state,
                'postal_code' => $settings->postal_code,
                'country' => $settings->country,

                'currency_code' => $settings->currency_code,
                'currency_symbol' => $settings->currency_symbol,
                'currency_position' => $settings->currency_position,
                'timezone' => $settings->timezone,
                'date_format' => $settings->date_format,
                'time_format' => $settings->time_format,

                'invoice_prefix' => $settings->invoice_prefix,
                'order_prefix' => $settings->order_prefix,
                'invoice_footer_note' => $settings->invoice_footer_note,
                'pos_receipt_footer' => $settings->pos_receipt_footer,

                'default_shipping' => $settings->default_shipping,
                'tax_enabled' => (bool) $settings->tax_enabled,
                'invoice_show_logo' => (bool) $settings->invoice_show_logo,

                'low_stock_threshold' => $settings->low_stock_threshold,
                'stock_alert_enabled' => (bool) $settings->stock_alert_enabled,

                'logo_path' => $settings->logo_path,
                'favicon_path' => $settings->favicon_path,
                'logo_url' => $settings->logo_path ? asset('storage/' . $settings->logo_path) : null,
                'favicon_url' => $settings->favicon_path ? asset('storage/' . $settings->favicon_path) : null,
            ],
        ]);
    }
}
