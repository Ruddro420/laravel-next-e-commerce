@extends('layouts.app')
@section('title','Customers')
@section('subtitle','Customers')
@section('pageTitle','Customers')
@section('pageDesc','Customer list and segmentation.')

@section('content')
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="text-lg font-semibold">Customers</div>
    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
      @foreach([
        ['Amina Rahman','VIP','34 orders'],
        ['M. Hasan','New','1 order'],
        ['Sara Khan','Returning','8 orders']
      ] as $c)
        <div class="rounded-2xl bg-slate-50 p-3 dark:bg-slate-800/40">
          <div class="font-semibold">{{ $c[0] }}</div>
          <div class="text-xs text-slate-500 dark:text-slate-400">{{ $c[2] }}</div>
          <span class="mt-2 inline-flex rounded-full bg-indigo-50 px-2 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">{{ $c[1] }}</span>
        </div>
      @endforeach
    </div>
  </div>
@endsection
