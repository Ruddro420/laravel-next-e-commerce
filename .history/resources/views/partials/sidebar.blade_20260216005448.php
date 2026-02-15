@php
// ---------- Helpers ----------
$is = fn(string $pattern) => request()->routeIs($pattern);

$baseParent = 'w-full flex items-center justify-between rounded-2xl px-3 py-2.5 text-sm font-semibold transition';
$baseLink = 'w-full flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm font-semibold transition';
$baseChild = 'block rounded-2xl px-3 py-2 text-sm font-semibold transition';

$activeParent = 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
$idleParent = 'text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800';

$activeChild = 'bg-slate-900 text-white dark:bg-white dark:text-slate-900';
$idleChild = 'text-slate-600 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800';

// Groups open if any child route is active
$openProducts = $is('products.*');
$openCrm = $is('crm.*');
$openSettings = $is('settings.*');
@endphp

<div class="space-y-1">

    {{-- ===================== Dashboard ===================== --}}
    <a href="{{ route('dashboard') }}"
        class="{{ $baseLink }} {{ $is('dashboard') ? $activeParent : $idleParent }}">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 13h8V3H3v10Zm10 8h8V11h-8v10ZM3 21h8v-6H3v6Zm10-18v6h8V3h-8Z" />
            </svg>
        </span>
        <div class="text-left">
            <div>Dashboard</div>
            <div class="text-xs opacity-70">Overview</div>
        </div>
    </a>

    {{-- ===================== Product (Dropdown) ===================== --}}
    <button type="button"
        class="{{ $baseParent }} {{ $openProducts ? $activeParent : $idleParent }}"
        data-collapse-btn="products"
        aria-expanded="{{ $openProducts ? 'true' : 'false' }}">
        <span class="flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-2xl bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="m7.5 4.27 9 5.15v10.16l-9-5.15V4.27Z" />
                    <path d="m16.5 9.42 3-1.72v10.16l-3 1.72V9.42Z" />
                    <path d="M7.5 4.27 4.5 6v10.16l3-1.73V4.27Z" />
                </svg>
            </span>
            <div class="text-left">
                <div>Product</div>
                <div class="text-xs opacity-70">Catalog</div>
            </div>
        </span>

        <svg class="h-4 w-4 opacity-80 transition-transform duration-200 {{ $openProducts ? 'rotate-180' : '' }}"
            data-collapse-chevron="products"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </button>


    <div
        class="pl-3 pr-1 overflow-hidden transition-all duration-300 ease-in-out
         {{ $openProducts ? 'max-h-[500px]' : 'max-h-0' }}"
        data-collapse-panel="products"
        data-collapse-default="{{ $openProducts ? 'open' : 'closed' }}">

        <!-- Items -->
        <div data-collapse-inner class="pt-1 space-y-1">
            <a href="{{ route('products.index') }}" class="{{ $baseChild }} {{ $is('products.index') ? $activeChild : $idleChild }}">
                <div class="flex align-items-center gap-2">
                    <div>
                        <span class="material-symbols-outlined">
                            remove
                        </span>
                    </div>
                    <div>All Products</div>
                </div>
            </a>
            <a href="{{ route('products.create') }}" class="{{ $baseChild }} {{ $is('products.create') ? $activeChild : $idleChild }}">
                <div class="flex align-items-center gap-2">
                    <div>
                        <span class="material-symbols-outlined">
                            remove
                        </span>
                    </div>
                    <div>Add Products</div>
                </div>
            </a>
            <a href="{{ route('products.categories') }}" class="{{ $baseChild }} {{ $is('products.categories') ? $activeChild : $idleChild }}">
                <div class="flex align-items-center gap-2">
                    <div>
                        <span class="material-symbols-outlined">
                            remove
                        </span>
                    </div>
                    <div>Category</div>
                </div>
            </a>
            <a href="{{ route('products.brands') }}" class="{{ $baseChild }} {{ $is('products.brands') ? $activeChild : $idleChild }}">
                <div class="flex align-items-center gap-2">
                    <div>
                        <span class="material-symbols-outlined">
                            remove
                        </span>
                    </div>
                    <div>Brands</div>
                </div>
            </a>
            <a href="{{ route('products.attributes') }}" class="{{ $baseChild }} {{ $is('products.attributes') ? $activeChild : $idleChild }}">
                <div class="flex align-items-center gap-2">
                    <div>
                        <span class="material-symbols-outlined">
                            remove
                        </span>
                    </div>
                    <div>Attributes</div>
                </div>
            </a>
            <a href="{{ route('products.reviews') }}" class="{{ $baseChild }} {{ $is('products.reviews') ? $activeChild : $idleChild }}">
                <div class="flex align-items-center gap-2">
                    <div>
                        <span class="material-symbols-outlined">
                            remove
                        </span>
                    </div>
                    <div>Reviews</div>
                </div>
            </a>
        </div>
    </div>

    {{-- ===================== CRM (Dropdown) ===================== --}}
    <button type="button"
        class="{{ $baseParent }} {{ $openCrm ? $activeParent : $idleParent }}"
        data-collapse-btn="crm"
        aria-expanded="{{ $openCrm ? 'true' : 'false' }}">
        <span class="flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-2xl bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </span>
            <div class="text-left">
                <div>CRM</div>
                <div class="text-xs opacity-70">Sales & customers</div>
            </div>
        </span>

        <svg class="h-4 w-4 opacity-80 transition-transform duration-200 {{ $openCrm ? 'rotate-180' : '' }}"
            data-collapse-chevron="crm"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </button>

    <div
        class="pl-3 pr-1 overflow-hidden transition-all duration-300 ease-in-out
         {{ $openCrm ? 'max-h-[1200px]' : 'max-h-0' }}"
        data-collapse-panel="crm"
        data-collapse-default="{{ $openCrm ? 'open' : 'closed' }}">
        <div data-collapse-inner class="pt-1 space-y-1">

            <a href="{{ route('crm.orders') }}" class="{{ $baseChild }} {{ $is('crm.orders') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Orders</div>
                </div>
            </a>

            <a href="{{ route('customers') }}" class="{{ $baseChild }} {{ $is('customers') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Customers</div>
                </div>
            </a>

            <a href="{{ route('crm.coupons') }}" class="{{ $baseChild }} {{ $is('crm.coupons') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Coupons</div>
                </div>
            </a>

            <a href="{{ route('crm.reports') }}" class="{{ $baseChild }} {{ $is('crm.reports') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Reports</div>
                </div>
            </a>

            <a href="{{ route('crm.payments') }}" class="{{ $baseChild }} {{ $is('crm.payments') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Payments</div>
                </div>
            </a>

            <a href="{{ route('crm.taxes') }}" class="{{ $baseChild }} {{ $is('crm.taxes') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Taxes</div>
                </div>
            </a>

            <a href="{{ route('stock') }}" class="{{ $baseChild }} {{ $is('stock') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Stock</div>
                </div>
            </a>

            <a href="{{ route('crm.settings') }}" class="{{ $baseChild }} {{ $is('crm.settings') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Settings</div>
                </div>
            </a>

        </div>
    </div>


    {{-- ===================== Analytics ===================== --}}
    <a href="{{ route('analytics') }}"
        class="{{ $baseLink }} {{ $is('analytics') ? $activeParent : $idleParent }}">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-violet-50 text-violet-700 dark:bg-violet-500/15 dark:text-violet-300">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3v18h18" />
                <path d="M7 14l4-4 4 4 6-6" />
            </svg>
        </span>
        <div class="text-left">
            <div>Analytics</div>
            <div class="text-xs opacity-70">Reports</div>
        </div>
    </a>

    {{-- ===================== POS ===================== --}}
    <a href="{{ route('pos') }}"
        class="{{ $baseLink }} {{ $is('pos') ? $activeParent : $idleParent }}">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2h12v4H6z" />
                <path d="M6 10h12v12H6z" />
                <path d="M9 14h6" />
            </svg>
        </span>
        <div class="text-left">
            <div>POS</div>
            <div class="text-xs opacity-70">Point of sale</div>
        </div>
    </a>

    {{-- ===================== Landing Page ===================== --}}
    <a href="{{ route('landing') }}"
        class="{{ $baseLink }} {{ $is('landing') ? $activeParent : $idleParent }}">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 12h18" />
                <path d="M12 3v18" />
            </svg>
        </span>
        <div class="text-left">
            <div>Landing Page</div>
            <div class="text-xs opacity-70">Marketing</div>
        </div>
    </a>

    {{-- ===================== Frontend Design ===================== --}}
    <a href="{{ route('frontend') }}"
        class="{{ $baseLink }} {{ $is('frontend') ? $activeParent : $idleParent }}">
        <span class="grid h-9 w-9 place-items-center rounded-2xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16v16H4z" />
                <path d="M8 8h8" />
                <path d="M8 12h8" />
                <path d="M8 16h5" />
            </svg>
        </span>
        <div class="text-left">
            <div>Frontend Design</div>
            <div class="text-xs opacity-70">UI builder</div>
        </div>
    </a>

    {{-- ===================== Settings (Dropdown) ===================== --}}
    <button type="button"
        class="{{ $baseParent }} {{ $openSettings ? $activeParent : $idleParent }}"
        data-collapse-btn="settings"
        aria-expanded="{{ $openSettings ? 'true' : 'false' }}">
        <span class="flex items-center gap-3">
            <span class="grid h-9 w-9 place-items-center rounded-2xl bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-1.41 3.41h-.17a1.65 1.65 0 0 0-1.82.33 1.65 1.65 0 0 0-.5 1.6 2 2 0 0 1-3.9 0 1.65 1.65 0 0 0-1-1.22 1.65 1.65 0 0 0-1.6.5 2 2 0 0 1-3.41-1.41l.06-.06A1.65 1.65 0 0 0 5 15.4a1.65 1.65 0 0 0-1.6-.5 2 2 0 0 1 0-3.9 1.65 1.65 0 0 0 1.22-1 1.65 1.65 0 0 0-.5-1.6A2 2 0 0 1 5.53 4l.06.06A1.65 1.65 0 0 0 7.4 4.73 1.65 1.65 0 0 0 7.9 3.1a2 2 0 0 1 3.9 0 1.65 1.65 0 0 0 1 1.22 1.65 1.65 0 0 0 1.6-.5A2 2 0 0 1 19.4 5.53l-.06.06A1.65 1.65 0 0 0 19 7.4c0 .6.23 1.17.64 1.6.41.43.96.67 1.56.67a2 2 0 0 1 0 4 1.65 1.65 0 0 0-1.8 1.33Z" />
                </svg>
            </span>
            <div class="text-left">
                <div>Settings</div>
                <div class="text-xs opacity-70">Configuration</div>
            </div>
        </span>

        <svg class="h-4 w-4 opacity-80 transition-transform duration-200 {{ $openSettings ? 'rotate-180' : '' }}"
            data-collapse-chevron="settings"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m6 9 6 6 6-6" />
        </svg>
    </button>

    <div
        class="pl-3 pr-1 overflow-hidden transition-all duration-300 ease-in-out
         {{ $openSettings ? 'max-h-[1000px]' : 'max-h-0' }}"
        data-collapse-panel="settings"
        data-collapse-default="{{ $openSettings ? 'open' : 'closed' }}">
        <div data-collapse-inner class="pt-1 space-y-1">

            <a href="{{ route('settings.general') }}" class="{{ $baseChild }} {{ $is('settings.general') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>General Setting</div>
                </div>
            </a>

            <a href="{{ route('settings.users') }}" class="{{ $baseChild }} {{ $is('settings.users') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>User Management</div>
                </div>
            </a>

            <a href="{{ route('settings.contact') }}" class="{{ $baseChild }} {{ $is('settings.contact') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Contact Info</div>
                </div>
            </a>

            <a href="{{ route('settings.colors') }}" class="{{ $baseChild }} {{ $is('settings.colors') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Color Combination</div>
                </div>
            </a>

            <a href="{{ route('settings.fonts') }}" class="{{ $baseChild }} {{ $is('settings.fonts') ? $activeChild : $idleChild }}">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] leading-none opacity-70">remove</span>
                    <div>Font Family</div>
                </div>
            </a>

        </div>
    </div>


</div>