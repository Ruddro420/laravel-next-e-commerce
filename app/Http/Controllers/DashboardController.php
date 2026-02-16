<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Payment;

class DashboardController extends Controller
{
    public function index()
    {
        $from = now()->startOfMonth();
        $to   = now()->endOfDay();

        $ordersQ = Order::query()->whereBetween('orders.created_at', [$from, $to]);

        $kpi = [
            'revenue' => (float) (clone $ordersQ)->sum('total'),
            'orders'  => (int) (clone $ordersQ)->count(),
            'tax'     => (float) (clone $ordersQ)->sum('tax_amount'),
            'discount'=> (float) (clone $ordersQ)->sum('coupon_discount'),
        ];

        $kpi['aov'] = $kpi['orders'] > 0 ? ($kpi['revenue'] / $kpi['orders']) : 0;

        // Trend for chart (daily in current month)
        $trend = (clone $ordersQ)
            ->selectRaw("DATE_FORMAT(orders.created_at,'%Y-%m-%d') as label, COUNT(*) as orders, SUM(total) as revenue")
            ->groupBy('label')
            ->orderBy('label')
            ->get()
            ->keyBy('label');

        $labels = [];
        $ordersSeries = [];
        $revenueSeries = [];

        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            $k = $cursor->format('Y-m-d');
            $labels[] = $k;
            $ordersSeries[] = (int)($trend[$k]->orders ?? 0);
            $revenueSeries[] = (float)($trend[$k]->revenue ?? 0);
            $cursor->addDay();
        }

        $latestOrders = Order::with(['customer','payment'])
            ->latest()
            ->limit(8)
            ->get();

        $newCustomers = Customer::query()
            ->whereBetween('customers.created_at', [$from, $to])
            ->count();

        $pendingPayments = Payment::query()
            ->where('status', 'pending')
            ->count();

        $lowStockThreshold = 5;
        $lowStock = Product::query()
            ->whereNotNull('stock')
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock')
            ->limit(8)
            ->get();

        return view('pages.dashboard', compact(
            'from','to','kpi','labels','ordersSeries','revenueSeries',
            'latestOrders','newCustomers','pendingPayments',
            'lowStock','lowStockThreshold'
        ));
    }
}
