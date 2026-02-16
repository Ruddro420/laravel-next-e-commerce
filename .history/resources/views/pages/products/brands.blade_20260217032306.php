@extends('layouts.app')
@section('title','Brands')
@section('subtitle','Products')
@section('pageTitle','Brand Management')
@section('pageDesc','Create, edit and manage brands.')

@section('content')
<div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

  {{-- ADD BRAND --}}
  <div class="xl:col-span-1">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <h2 class="text-lg font-semibold">Add Brand</h2>
      <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Name, slug, description and image.</p>

      @if(session('success'))
        <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200">
          {{ session('success') }}
        </div>
      @endif

      @if($errors->any())
        <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200">
          <div class="font-semibold mb-1">Please fix:</div>
          <ul class="list-disc pl-5 space-y-1">
            @foreach($errors->all() as $err) <li>{{ $err }}</li> @endforeach
          </ul>
        </div>
      @endif

      <form class="mt-4 space-y-4" method="POST" action="{{ route('products.brands.store') }}" enctype="multipart/form-data">
        @csrf

        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Name</label>
          <input id="brandName" name="name" value="{{ old('name') }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
            placeholder="e.g. Nike" />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Slug</label>
          <input id="brandSlug" name="slug" value="{{ old('slug') }}"
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
            placeholder="leave empty to auto-generate" />
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Description</label>
          <textarea name="description" rows="4"
            class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
            placeholder="Short description...">{{ old('description') }}</textarea>
        </div>

        <div>
          <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Image</label>
          <div class="mt-2 rounded-2xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
            <input id="brandImage" type="file" name="image" accept="image/*"
              class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700" />

            <div id="brandPreviewWrap" class="hidden mt-4">
              <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 mb-2">Preview</div>
              <img id="brandPreview" class="h-28 w-full rounded-2xl object-cover border border-slate-200 dark:border-slate-800" alt="Preview">
            </div>
          </div>
        </div>

        <button type="submit"
          class="w-full rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
          Save Brand
        </button>
      </form>
    </div>
  </div>

  {{-- LIST + SEARCH + PAGINATION --}}
  <div class="xl:col-span-2">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h2 class="text-lg font-semibold">All Brands</h2>
          <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Edit / delete with confirmation.</p>
        </div>

        <form method="GET" action="{{ route('products.brands') }}" class="flex gap-2">
          <input name="q" value="{{ $q ?? '' }}"
            class="w-full sm:w-72 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
            placeholder="Search name or slug..." />
          <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
            Search
          </button>
        </form>
      </div>

      <div class="mt-4 overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
              <th class="py-3 pr-3">Image</th>
              <th class="py-3 pr-3">Name</th>
              <th class="py-3 pr-3">Slug</th>
              <th class="py-3 pr-3">Description</th>
              <th class="py-3 pr-3">Actions</th>
            </tr>
          </thead>

          <tbody>
            @forelse($brands as $b)
              <tr class="border-t border-slate-100 dark:border-slate-800">
                <td class="py-3 pr-3">
                  @if($b->image_path)
                    <img class="h-10 w-10 rounded-xl object-cover border border-slate-200 dark:border-slate-800"
                         src="{{ asset('storage/'.$b->image_path) }}" alt="{{ $b->name }}">
                  @else
                    <div class="h-10 w-10 rounded-xl bg-slate-100 dark:bg-slate-800"></div>
                  @endif
                </td>

                <td class="py-3 pr-3 font-semibold text-slate-800 dark:text-slate-100">{{ $b->name }}</td>
                <td class="py-3 pr-3 text-slate-600 dark:text-slate-300">{{ $b->slug }}</td>
                <td class="py-3 pr-3 text-slate-600 dark:text-slate-300">
                  <div class="line-clamp-2 max-w-[26rem]">{{ $b->description }}</div>
                </td>

                <td class="py-3 pr-3">
                  <div class="flex items-center gap-2">
                    <button type="button"
                      class="rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800"
                      data-brand-edit
                      data-id="{{ $b->id }}"
                      data-name="{{ e($b->name) }}"
                      data-slug="{{ e($b->slug) }}"
                      data-description="{{ e($b->description ?? '') }}"
                    >
                      Edit
                    </button>

                    <button type="button"
                      class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200 dark:hover:bg-rose-500/20"
                      data-brand-delete
                      data-id="{{ $b->id }}"
                      data-name="{{ e($b->name) }}"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            @empty
              <tr class="border-t border-slate-100 dark:border-slate-800">
                <td colspan="5" class="py-8 text-center text-slate-500 dark:text-slate-400">
                  No brands found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-4">
        {{ $brands->links() }}
      </div>
    </div>
  </div>

</div>

{{-- EDIT MODAL --}}
<div id="brandEditModal" class="hidden fixed inset-0 z-[80]">
  <div class="absolute inset-0 bg-slate-900/40" data-brand-edit-close></div>

  <div class="relative mx-auto mt-20 w-[92%] max-w-xl rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex items-start justify-between">
      <div>
        <h3 class="text-lg font-semibold">Edit Brand</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">Update details and image.</p>
      </div>
      <button class="h-10 w-10 rounded-xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800" type="button" data-brand-edit-close>✕</button>
    </div>

    <form id="brandEditForm" class="mt-4 space-y-4" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')

      <div>
        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Name</label>
        <input id="brandEditName" name="name"
          class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Slug</label>
        <input id="brandEditSlug" name="slug"
          class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800" />
      </div>

      <div>
        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Description</label>
        <textarea id="brandEditDesc" name="description" rows="4"
          class="mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm outline-none focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"></textarea>
      </div>

      <div>
        <label class="text-sm font-semibold text-slate-700 dark:text-slate-200">Replace Image</label>
        <input type="file" name="image" accept="image/*"
          class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-xl file:border-0 file:bg-slate-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-slate-700 hover:file:bg-slate-200 dark:text-slate-300 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700" />
      </div>

      <div class="flex items-center justify-end gap-2 pt-2">
        <button type="button" data-brand-edit-close
          class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Cancel
        </button>
        <button type="submit"
          class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
          Save Changes
        </button>
      </div>
    </form>
  </div>
</div>

{{-- DELETE CONFIRM MODAL --}}
<div id="brandDeleteModal" class="hidden fixed inset-0 z-[90]">
  <div class="absolute inset-0 bg-slate-900/40" data-brand-delete-close></div>

  <div class="relative mx-auto mt-28 w-[92%] max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <h3 class="text-lg font-semibold">Delete Brand</h3>
    <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
      Are you sure you want to delete <span id="brandDeleteName" class="font-semibold"></span>?
    </p>

    <form id="brandDeleteForm" class="mt-5 flex justify-end gap-2" method="POST">
      @csrf
      @method('DELETE')

      <button type="button" data-brand-delete-close
        class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
        Cancel
      </button>

      <button type="submit"
        class="rounded-2xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-rose-700">
        Delete
      </button>
    </form>
  </div>
</div>

<script>
  (function(){
    function toSlug(v){
      return v.toLowerCase().trim()
        .replace(/[^a-z0-9\s-]/g,'')
        .replace(/\s+/g,'-')
        .replace(/-+/g,'-');
    }

    // create slug
    const name = document.getElementById('brandName');
    const slug = document.getElementById('brandSlug');
    if(name && slug){
      name.addEventListener('input', ()=>{
        if(slug.value.trim().length) return;
        slug.value = toSlug(name.value);
      });
    }

    // image preview
    const image = document.getElementById('brandImage');
    const wrap = document.getElementById('brandPreviewWrap');
    const preview = document.getElementById('brandPreview');
    if(image && wrap && preview){
      image.addEventListener('change', ()=>{
        const file = image.files && image.files[0];
        if(!file){ wrap.classList.add('hidden'); return; }
        preview.src = URL.createObjectURL(file);
        wrap.classList.remove('hidden');
      });
    }

    // edit modal
    const editModal = document.getElementById('brandEditModal');
    const editForm  = document.getElementById('brandEditForm');
    const editName  = document.getElementById('brandEditName');
    const editSlug  = document.getElementById('brandEditSlug');
    const editDesc  = document.getElementById('brandEditDesc');

    function openEdit(){ editModal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
    function closeEdit(){ editModal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

    document.querySelectorAll('[data-brand-edit]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = btn.dataset.id;
        editForm.action = "{{ url('/brands') }}/" + id;
        editName.value = btn.dataset.name || '';
        editSlug.value = btn.dataset.slug || '';
        editDesc.value = btn.dataset.description || '';
        openEdit();
      });
    });

    editModal?.querySelectorAll('[data-brand-edit-close]').forEach(el=>{
      el.addEventListener('click', closeEdit);
    });

    // delete modal
    const deleteModal = document.getElementById('brandDeleteModal');
    const deleteForm  = document.getElementById('brandDeleteForm');
    const deleteName  = document.getElementById('brandDeleteName');

    function openDelete(){ deleteModal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
    function closeDelete(){ deleteModal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

    document.querySelectorAll('[data-brand-delete]').forEach(btn=>{
      btn.addEventListener('click', ()=>{
        const id = btn.dataset.id;
        deleteForm.action = "{{ url('/brands') }}/" + id;
        deleteName.textContent = btn.dataset.name || 'this brand';
        openDelete();
      });
    });

    deleteModal?.querySelectorAll('[data-brand-delete-close]').forEach(el=>{
      el.addEventListener('click', closeDelete);
    });

    document.addEventListener('keydown', (e)=>{
      if(e.key !== 'Escape') return;
      closeEdit();
      closeDelete();
    });
  })();
</script>
@endsection
