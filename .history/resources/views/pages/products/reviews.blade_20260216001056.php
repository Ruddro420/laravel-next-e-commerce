@extends('layouts.app')
@section('title','Reviews')
@section('subtitle','Products')
@section('pageTitle','Reviews')
@section('pageDesc','Manage product reviews')

@section('content')
<div class="space-y-6">

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

  {{-- Top controls --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft dark:bg-slate-900 dark:border-slate-800">
    <div class="flex flex-col lg:flex-row gap-3 lg:items-center lg:justify-between">
      <form class="flex flex-col sm:flex-row gap-3 sm:items-center" method="GET" action="{{ route('products.reviews') }}">
        <div>
          <select name="product_id"
            class="w-full sm:w-64 rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="">All Products</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" {{ (string)$productId===(string)$p->id ? 'selected' : '' }}>
                {{ $p->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div class="relative w-full sm:w-72">
          <input name="q" value="{{ $q }}"
            class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm outline-none placeholder:text-slate-400 focus:ring-2 focus:ring-indigo-500/40 dark:bg-slate-900 dark:border-slate-800"
            placeholder="Search name/email/comment..." />
        </div>

        <button class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Filter
        </button>

        <a href="{{ route('products.reviews') }}"
          class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
          Reset
        </a>
      </form>

      <button id="btnOpenAdd"
        class="rounded-2xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-slate-900">
        + Add Review
      </button>
    </div>
  </div>

  {{-- Table --}}
  <div class="rounded-2xl border border-slate-200 bg-white shadow-soft dark:bg-slate-900 dark:border-slate-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 dark:bg-slate-950/40">
          <tr class="text-left text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400">
            <th class="py-3 px-4">Product</th>
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4">Rating</th>
            <th class="py-3 px-4">Comment</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reviews as $r)
            <tr class="border-t border-slate-100 dark:border-slate-800">
              <td class="py-3 px-4">
                <div class="font-semibold">{{ $r->product->name ?? '—' }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">Product ID: {{ $r->product_id }}</div>
              </td>

              <td class="py-3 px-4">
                <div class="font-semibold">{{ $r->customer_name }}</div>
                <div class="text-xs text-slate-500 dark:text-slate-400">{{ $r->customer_email ?? '—' }}</div>
              </td>

              <td class="py-3 px-4">
                <div class="inline-flex items-center gap-1 font-semibold">
                  <span class="text-amber-600">★</span> {{ $r->rating }}/5
                </div>
              </td>

              <td class="py-3 px-4 max-w-[460px]">
                <div class="text-slate-700 dark:text-slate-200">
                  {{ $r->comment ? \Illuminate\Support\Str::limit($r->comment, 120) : '—' }}
                </div>
              </td>

              <td class="py-3 px-4">
                @if($r->is_approved)
                  <span class="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-200">
                    Approved
                  </span>
                @else
                  <span class="inline-flex rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-200">
                    Pending
                  </span>
                @endif
              </td>

              <td class="py-3 px-4 text-right">
                <div class="inline-flex gap-2">
                  <button type="button"
                    class="rounded-xl border border-slate-200 px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800"
                    data-edit
                    data-id="{{ $r->id }}"
                    data-product_id="{{ $r->product_id }}"
                    data-customer_name="{{ e($r->customer_name) }}"
                    data-customer_email="{{ e($r->customer_email ?? '') }}"
                    data-rating="{{ $r->rating }}"
                    data-comment="{{ e($r->comment ?? '') }}"
                    data-is_approved="{{ $r->is_approved ? '1' : '0' }}">
                    Edit
                  </button>

                  <form method="POST" action="{{ route('products.reviews.destroy',$r) }}" data-del>
                    @csrf
                    @method('DELETE')
                    <button class="rounded-xl border border-rose-200 px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-500/30 dark:hover:bg-rose-950/30">
                      Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-10 text-center text-slate-500 dark:text-slate-400">
                No reviews found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="p-4 border-t border-slate-100 dark:border-slate-800">
      {{ $reviews->links() }}
    </div>
  </div>

</div>

{{-- Add Modal --}}
<div id="addModal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-slate-900/50" data-close-add></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div class="text-lg font-semibold">Add Review</div>
        <button class="h-9 w-9 rounded-xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800" data-close-add>✕</button>
      </div>

      <form class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4" method="POST" action="{{ route('products.reviews.store') }}">
        @csrf

        <div class="md:col-span-2">
          <label class="text-sm font-semibold">Product</label>
          <select name="product_id" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="">Select product</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}" {{ (string)$productId===(string)$p->id ? 'selected' : '' }}>
                {{ $p->name }}
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-sm font-semibold">Customer Name</label>
          <input name="customer_name" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Customer Email (optional)</label>
          <input name="customer_email" type="email"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Rating</label>
          <select name="rating" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            @for($i=5;$i>=1;$i--)
              <option value="{{ $i }}">{{ $i }}</option>
            @endfor
          </select>
        </div>

        <div class="flex items-end gap-2">
          <label class="inline-flex items-center gap-2 text-sm font-semibold">
            <input type="checkbox" name="is_approved" value="1" checked
              class="h-4 w-4 rounded border-slate-300" />
            Approved
          </label>
        </div>

        <div class="md:col-span-2">
          <label class="text-sm font-semibold">Comment</label>
          <textarea name="comment" rows="4"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
        </div>

        <div class="md:col-span-2 flex justify-end gap-2">
          <button type="button" data-close-add
            class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
            Cancel
          </button>
          <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
            Save Review
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit Modal --}}
<div id="editModal" class="hidden fixed inset-0 z-50">
  <div class="absolute inset-0 bg-slate-900/50" data-close-edit></div>
  <div class="absolute inset-0 flex items-center justify-center p-4">
    <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-5 shadow-soft dark:bg-slate-900 dark:border-slate-800">
      <div class="flex items-center justify-between">
        <div class="text-lg font-semibold">Edit Review</div>
        <button class="h-9 w-9 rounded-xl border border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800" data-close-edit>✕</button>
      </div>

      <form id="editForm" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4" method="POST">
        @csrf
        @method('PUT')

        <div class="md:col-span-2">
          <label class="text-sm font-semibold">Product</label>
          <select id="editProduct" name="product_id" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            <option value="">Select product</option>
            @foreach($products as $p)
              <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-sm font-semibold">Customer Name</label>
          <input id="editName" name="customer_name" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Customer Email (optional)</label>
          <input id="editEmail" name="customer_email" type="email"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800" />
        </div>

        <div>
          <label class="text-sm font-semibold">Rating</label>
          <select id="editRating" name="rating" required
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800">
            @for($i=5;$i>=1;$i--)
              <option value="{{ $i }}">{{ $i }}</option>
            @endfor
          </select>
        </div>

        <div class="flex items-end gap-2">
          <label class="inline-flex items-center gap-2 text-sm font-semibold">
            <input id="editApproved" type="checkbox" name="is_approved" value="1"
              class="h-4 w-4 rounded border-slate-300" />
            Approved
          </label>
        </div>

        <div class="md:col-span-2">
          <label class="text-sm font-semibold">Comment</label>
          <textarea id="editComment" name="comment" rows="4"
            class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-2.5 text-sm dark:bg-slate-900 dark:border-slate-800"></textarea>
        </div>

        <div class="md:col-span-2 flex justify-end gap-2">
          <button type="button" data-close-edit
            class="rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:bg-slate-900 dark:border-slate-800 dark:hover:bg-slate-800">
            Cancel
          </button>
          <button class="rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
            Update Review
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  // Add modal
  const addModal = document.getElementById('addModal');
  const btnOpenAdd = document.getElementById('btnOpenAdd');
  const closeAddBtns = document.querySelectorAll('[data-close-add]');
  function openAdd(){ addModal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function closeAdd(){ addModal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }
  btnOpenAdd?.addEventListener('click', openAdd);
  closeAddBtns.forEach(b=>b.addEventListener('click', closeAdd));

  // Edit modal
  const editModal = document.getElementById('editModal');
  const editForm = document.getElementById('editForm');
  const closeEditBtns = document.querySelectorAll('[data-close-edit]');
  const editProduct = document.getElementById('editProduct');
  const editName = document.getElementById('editName');
  const editEmail = document.getElementById('editEmail');
  const editRating = document.getElementById('editRating');
  const editComment = document.getElementById('editComment');
  const editApproved = document.getElementById('editApproved');

  function openEdit(){ editModal.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
  function closeEdit(){ editModal.classList.add('hidden'); document.body.classList.remove('overflow-hidden'); }

  closeEditBtns.forEach(b=>b.addEventListener('click', closeEdit));

  document.querySelectorAll('[data-edit]').forEach(btn=>{
    btn.addEventListener('click', ()=>{
      const id = btn.dataset.id;
      editForm.action = "{{ url('/products/reviews') }}/" + id;

      editProduct.value = btn.dataset.product_id || '';
      editName.value = btn.dataset.customer_name || '';
      editEmail.value = btn.dataset.customer_email || '';
      editRating.value = btn.dataset.rating || '5';
      editComment.value = btn.dataset.comment || '';
      editApproved.checked = (btn.dataset.is_approved === '1');

      openEdit();
    });
  });

  // Delete confirm
  document.querySelectorAll('form[data-del]').forEach(f=>{
    f.addEventListener('submit', (e)=>{
      if(!confirm('Delete this review?')) e.preventDefault();
    });
  });

  // ESC closes modals
  document.addEventListener('keydown', (e)=>{
    if(e.key === 'Escape'){
      closeAdd();
      closeEdit();
    }
  });
})();
</script>
@endsection
