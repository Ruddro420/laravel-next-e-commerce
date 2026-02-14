@extends('layouts.app')
@section('title','Products')
@section('subtitle','Products')
@section('pageTitle','Products')
@section('pageDesc','Manage catalog, inventory and pricing.')

@section('content')
  <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-lg font-semibold">Product List</div>
      <div class="mt-3 space-y-3">
        <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/40 flex items-center justify-between">
          <div>
            <div class="font-semibold">AirFlex Sneakers</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">SKU AF-219 • Stock 24</div>
          </div>
          <button class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">Edit</button>
        </div>
        <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/40 flex items-center justify-between">
          <div>
            <div class="font-semibold">Minimal Backpack</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">SKU MB-044 • Stock 8</div>
          </div>
          <button class="rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">Edit</button>
        </div>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="text-lg font-semibold">Quick Add</div>
      <form class="mt-4 space-y-3">
        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" placeholder="Product name">
        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" placeholder="SKU">
        <input class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2 text-sm dark:bg-slate-900 dark:border-slate-800" placeholder="Price">
        <button type="button" class="w-full rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Add Product</button>
      </form>
    </div>
  </div>
@endsection
