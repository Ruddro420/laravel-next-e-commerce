@extends('layouts.app')
@section('title','General Setting')
@section('subtitle','Settings')
@section('pageTitle','General Setting')
@section('pageDesc','Software & store configuration')

@section('content')
<div class="space-y-5">

  @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  @if($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
      <div class="font-semibold mb-1">Fix these errors:</div>
      <ul class="list-disc pl-5 space-y-1">
        @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('settings.general.update') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @method('PUT')

    {{-- Header --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
        <div>
          <div class="text-lg font-extrabold">General Settings</div>
          <div class="text-sm text-slate-500 dark:text-slate-400">Update software identity, store details, invoice defaults and stock alerts.</div>
        </div>

        <button class="rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
          Save Changes
        </button>
      </div>
    </div>

    {{-- Software / Branding --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Software & Branding</div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">

        <div>
          <label class="text-sm font-semibold">Software Name</label>
          <input name="software_name" value="{{ old('software_name',$settings->software_name) }}"
                 class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Tagline</label>
          <input name="software_tagline" value="{{ old('software_tagline',$settings->software_tagline) }}"
                 class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
                 placeholder="Optional" />
        </div>

        <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
            <div class="flex items-center justify-between">
              <div class="font-semibold text-sm">Logo</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">PNG/JPG/WEBP (max 2MB)</div>
            </div>

            <div class="mt-3 flex items-center gap-4">
              <div class="h-14 w-14 rounded-2xl border border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 overflow-hidden grid place-items-center">
                @if($settings->logo_path)
                  <img src="{{ asset('storage/'.$settings->logo_path) }}" class="h-full w-full object-cover" alt="logo">
                @else
                  <span class="text-xs text-slate-500 dark:text-slate-400">No logo</span>
                @endif
              </div>

              <input type="file" name="logo"
                class="w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-white hover:file:bg-slate-800 dark:file:bg-white dark:file:text-slate-900 dark:hover:file:bg-slate-100" />
            </div>
          </div>

          <div class="rounded-2xl border border-slate-200 p-4 dark:border-slate-800">
            <div class="flex items-center justify-between">
              <div class="font-semibold text-sm">Favicon</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">ICO/PNG (max 1MB)</div>
            </div>

            <div class="mt-3 flex items-center gap-4">
              <div class="h-14 w-14 rounded-2xl border border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 overflow-hidden grid place-items-center">
                @if($settings->favicon_path)
                  <img src="{{ asset('storage/'.$settings->favicon_path) }}" class="h-full w-full object-cover" alt="favicon">
                @else
                  <span class="text-xs text-slate-500 dark:text-slate-400">No icon</span>
                @endif
              </div>

              <input type="file" name="favicon"
                class="w-full text-sm file:mr-3 file:rounded-xl file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-white hover:file:bg-slate-800 dark:file:bg-white dark:file:text-slate-900 dark:hover:file:bg-slate-100" />
            </div>
          </div>
        </div>

      </div>
    </div>

    {{-- Store Details --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Store Details</div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Store Name</label>
          <input name="store_name" value="{{ old('store_name',$settings->store_name) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Website</label>
          <input name="website" value="{{ old('website',$settings->website) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="https://example.com" />
        </div>

        <div>
          <label class="text-sm font-semibold">Store Email</label>
          <input name="store_email" value="{{ old('store_email',$settings->store_email) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Support Email</label>
          <input name="support_email" value="{{ old('support_email',$settings->support_email) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Phone</label>
          <input name="store_phone" value="{{ old('store_phone',$settings->store_phone) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
      </div>
    </div>

    {{-- Address --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Address</div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="text-sm font-semibold">Address Line 1</label>
          <input name="address_line1" value="{{ old('address_line1',$settings->address_line1) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Address Line 2</label>
          <input name="address_line2" value="{{ old('address_line2',$settings->address_line2) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">City</label>
          <input name="city" value="{{ old('city',$settings->city) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">State</label>
          <input name="state" value="{{ old('state',$settings->state) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Postal Code</label>
          <input name="postal_code" value="{{ old('postal_code',$settings->postal_code) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>
        <div>
          <label class="text-sm font-semibold">Country</label>
          <input name="country" value="{{ old('country',$settings->country) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="Bangladesh" />
        </div>
      </div>
    </div>

    {{-- Currency & Locale --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Currency & Locale</div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-semibold">Currency Code</label>
          <input name="currency_code" value="{{ old('currency_code',$settings->currency_code) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="BDT" />
        </div>

        <div>
          <label class="text-sm font-semibold">Currency Symbol</label>
          <input name="currency_symbol" value="{{ old('currency_symbol',$settings->currency_symbol) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="৳" />
        </div>

        <div>
          <label class="text-sm font-semibold">Currency Position</label>
          <select name="currency_position"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="before" {{ old('currency_position',$settings->currency_position)=='before'?'selected':'' }}>Before (৳100)</option>
            <option value="after" {{ old('currency_position',$settings->currency_position)=='after'?'selected':'' }}>After (100৳)</option>
          </select>
        </div>

        <div>
          <label class="text-sm font-semibold">Timezone</label>
          <input name="timezone" value="{{ old('timezone',$settings->timezone) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="Asia/Dhaka" />
        </div>

        <div>
          <label class="text-sm font-semibold">Date Format</label>
          <input name="date_format" value="{{ old('date_format',$settings->date_format) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="d M, Y" />
        </div>

        <div>
          <label class="text-sm font-semibold">Time Format</label>
          <input name="time_format" value="{{ old('time_format',$settings->time_format) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="h:i A" />
        </div>
      </div>
    </div>

    {{-- Invoice / Order Defaults --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Order / Invoice Defaults</div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm font-semibold">Order Prefix</label>
          <input name="order_prefix" value="{{ old('order_prefix',$settings->order_prefix) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Invoice Prefix</label>
          <input name="invoice_prefix" value="{{ old('invoice_prefix',$settings->invoice_prefix) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div class="flex items-center gap-2 pt-8">
          <input type="checkbox" name="invoice_show_logo" value="1"
            class="h-5 w-5 rounded border-slate-300"
            {{ old('invoice_show_logo',$settings->invoice_show_logo) ? 'checked' : '' }}>
          <label class="text-sm font-semibold">Show logo on invoice</label>
        </div>

        <div class="md:col-span-3">
          <label class="text-sm font-semibold">Invoice Footer Note</label>
          <textarea name="invoice_footer_note" rows="3"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="Thank you for your purchase...">{{ old('invoice_footer_note',$settings->invoice_footer_note) }}</textarea>
        </div>

        <div class="md:col-span-3">
          <label class="text-sm font-semibold">POS Receipt Footer</label>
          <textarea name="pos_receipt_footer" rows="3"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"
            placeholder="Return policy / Contact / Thank you...">{{ old('pos_receipt_footer',$settings->pos_receipt_footer) }}</textarea>
        </div>
      </div>
    </div>

    {{-- Tax / Shipping / Stock --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="font-semibold">Tax, Shipping & Stock</div>
      <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="flex items-center gap-2 pt-2">
          <input type="checkbox" name="tax_enabled" value="1"
            class="h-5 w-5 rounded border-slate-300"
            {{ old('tax_enabled',$settings->tax_enabled) ? 'checked' : '' }}>
          <label class="text-sm font-semibold">Enable tax calculation</label>
        </div>

        <div>
          <label class="text-sm font-semibold">Default Shipping</label>
          <input name="default_shipping" type="number" step="0.01" value="{{ old('default_shipping',$settings->default_shipping) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Low Stock Threshold</label>
          <input name="low_stock_threshold" type="number" min="0" value="{{ old('low_stock_threshold',$settings->low_stock_threshold) }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
          <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Products at or below this will show as low stock.</div>
        </div>

        <div class="md:col-span-3 flex items-center gap-2 pt-1">
          <input type="checkbox" name="stock_alert_enabled" value="1"
            class="h-5 w-5 rounded border-slate-300"
            {{ old('stock_alert_enabled',$settings->stock_alert_enabled) ? 'checked' : '' }}>
          <label class="text-sm font-semibold">Enable stock alerts</label>
        </div>
      </div>
    </div>

    {{-- Footer Save --}}
    <div class="flex justify-end">
      <button class="rounded-2xl bg-indigo-600 px-6 py-3 text-sm font-semibold text-white hover:bg-indigo-700">
        Save Settings
      </button>
    </div>

  </form>
</div>
@endsection
