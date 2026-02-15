@extends('layouts.app')
@section('title','Attributes')
@section('subtitle','Products')
@section('pageTitle','Product Attributes')
@section('pageDesc','Create and manage product attributes and values.')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

  {{-- ADD ATTRIBUTE --}}
  <div class="xl:col-span-1">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

      <h2 class="text-lg font-semibold">Add Attribute</h2>
      <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
        Example: Size → S, M, L
      </p>

      @if(session('success'))
        <div class="mt-4 p-3 rounded-xl bg-emerald-50 text-emerald-700 text-sm">
          {{ session('success') }}
        </div>
      @endif

      <form method="POST" action="{{ route('products.attributes.store') }}" class="mt-4 space-y-4">
        @csrf

        <div>
          <label class="text-sm font-semibold">Attribute Name</label>
          <input name="name"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/40"
            placeholder="Size" />
        </div>

        <div>
          <label class="text-sm font-semibold">Values (comma separated)</label>
          <input name="values"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500/40"
            placeholder="S, M, L, XL" />
        </div>

        <button
          class="w-full rounded-2xl bg-indigo-600 text-white py-2.5 text-sm font-semibold hover:bg-indigo-700">
          Save Attribute
        </button>
      </form>

    </div>
  </div>

  {{-- ATTRIBUTE LIST --}}
  <div class="xl:col-span-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">

      <div class="flex justify-between items-center">
        <h2 class="text-lg font-semibold">All Attributes</h2>

        <form method="GET" class="flex gap-2">
          <input name="q" value="{{ $q ?? '' }}"
            class="rounded-2xl border border-slate-200 px-4 py-2 text-sm"
            placeholder="Search..." />
        </form>
      </div>

      <div class="mt-4 overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-xs uppercase text-slate-500">
              <th class="py-3 text-left">Name</th>
              <th class="py-3 text-left">Values</th>
              <th class="py-3 text-left">Actions</th>
            </tr>
          </thead>

          <tbody>
            @foreach($attributes as $attr)
              <tr class="border-t border-slate-100">
                <td class="py-3 font-semibold">{{ $attr->name }}</td>

                <td class="py-3">
                  @foreach($attr->values as $v)
                    <span class="inline-flex bg-slate-100 rounded-xl px-2 py-1 text-xs mr-1">
                      {{ $v->value }}
                    </span>
                  @endforeach
                </td>

                <td class="py-3">
                  <form method="POST" action="{{ route('products.attributes.destroy',$attr->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="text-rose-600 text-xs font-semibold">
                      Delete
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $attributes->links() }}
      </div>

    </div>
  </div>

</div>

@endsection
