@extends('layouts.app')
@section('title','Settings')
@section('subtitle','Settings')
@section('pageTitle','Settings')
@section('pageDesc','Store, shipping, payments and user settings.')

@section('content')
  <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-lg font-semibold">Store</div>
      <div class="mt-4 space-y-3">
        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" placeholder="Store name">
        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" placeholder="Support email">
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-lg font-semibold">Security</div>
      <div class="mt-4 space-y-3">
        <button class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Change Password
        </button>
        <button class="w-full rounded-2xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-200 dark:hover:bg-rose-950/45">
          Enable 2FA
        </button>
      </div>
    </div>
  </div>
@endsection
