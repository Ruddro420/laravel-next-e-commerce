<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // period: daily|weekly|monthly|yearly|range
        $period = $request->get('period', 'monthly');

        [$from, $to] = $this->resolveDates($request, $period);

        // inclusive range
        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt = Carbon::parse($to)->endOfDay();

        // Orders base query
        $ordersQ = Order::query()
            ->whereBetween('created_at', [$fromDt, $toDt]);

        // KPI
        $ordersCount = (clone $ordersQ)->count();
        $salesTotal = (float) (clone $ordersQ)->sum('total');
        $salesSubtotal = (float) (clone $ordersQ)->sum('subtotal');
        $taxTotal = (float) (clone $ordersQ)->sum('tax_amount');
        $shippingTotal = (float) (clone $ordersQ)->sum('shipping');
        $couponTotal = (float) (clone $ordersQ)->sum('coupon_discount');

        $aov = $ordersCount > 0 ? $salesTotal / $ordersCount : 0;

        // Status breakdown
        $statusBreakdown = (clone $ordersQ)
            ->select('status', DB::raw('COUNT(*) as c'), DB::raw('SUM(total) as s'))
            ->groupBy('status')
            ->orderByDesc('c')
            ->get();

        // Payments breakdown
        $paymentsBreakdown = Payment::query()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->select('method', DB::raw('COUNT(*) as c'), DB::raw('SUM(amount) as paid'))
            ->groupBy('method')
            ->orderByDesc('paid')
            ->get();


        // Revenue series + Orders series (for charts)
        $bucket = $this->bucketForPeriod($period, $fromDt, $toDt);

        $series = (clone $ordersQ)
            ->select(
                DB::raw($bucket['select'] . ' as label'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        // Fill missing dates (nice chart)
        $filled = $this->fillSeries($series, $bucket, $fromDt, $toDt);

        // Top products by quantity + amount
        $topProducts = OrderItem::query()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->select(
                DB::raw("COALESCE(product_name, 'Unknown') as product_name"),
                DB::raw('SUM(qty) as qty_sum'),
                DB::raw('SUM(line_total) as sales_sum')
            )
            ->groupBy('product_name')
            ->orderByDesc('qty_sum')
            ->limit(10)
            ->get();

        // Top customers by total spent
        $topCustomers = Order::query()
            ->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereBetween('orders.created_at', [$fromDt, $toDt]) // ✅ fixed
            ->select(
                DB::raw("COALESCE(customers.name,'Guest') as name"),
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('SUM(orders.total) as spent')
            )
            ->groupBy('name')
            ->orderByDesc('spent')
            ->limit(10)
            ->get();


        // New customers in range
        $newCustomers = Customer::query()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->count();

        // Low stock alerts (optional)
        $lowStockThreshold = 5;
        $lowStock = Product::query()
            ->whereNotNull('stock')
            ->where('stock', '<=', $lowStockThreshold)
            ->orderBy('stock')
            ->limit(10)
            ->get();

        return view('pages.analytics.index', [
            'period' => $period,
            'from' => $fromDt->toDateString(),
            'to' => $toDt->toDateString(),

            'ordersCount' => $ordersCount,
            'salesTotal' => $salesTotal,
            'salesSubtotal' => $salesSubtotal,
            'taxTotal' => $taxTotal,
            'shippingTotal' => $shippingTotal,
            'couponTotal' => $couponTotal,
            'aov' => $aov,
            'newCustomers' => $newCustomers,

            'statusBreakdown' => $statusBreakdown,
            'paymentsBreakdown' => $paymentsBreakdown,

            'labels' => $filled['labels'],
            'ordersSeries' => $filled['orders'],
            'revenueSeries' => $filled['revenue'],

            'topProducts' => $topProducts,
            'topCustomers' => $topCustomers,
            'lowStock' => $lowStock,
            'lowStockThreshold' => $lowStockThreshold,
        ]);
    }

    public function exportCsv(Request $request)
    {
        $period = $request->get('period', 'monthly');
        [$from, $to] = $this->resolveDates($request, $period);
        $fromDt = Carbon::parse($from)->startOfDay();
        $toDt = Carbon::parse($to)->endOfDay();

        $rows = Order::query()
            ->whereBetween('created_at', [$fromDt, $toDt])
            ->with(['customer', 'payment'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($o) {
                return [
                    'order_number' => $o->order_number,
                    'date' => optional($o->created_at)->format('Y-m-d H:i'),
                    'status' => $o->status,
                    'customer' => $o->customer?->name ?? 'Guest',
                    'total' => $o->total,
                    'paid' => $o->payment?->amount_paid ?? 0,
                    'due' => $o->payment?->amount_due ?? 0,
                    'method' => $o->payment?->method ?? '',
                ];
            });

        $filename = "analytics_orders_{$fromDt->toDateString()}_to_{$toDt->toDateString()}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_keys($rows->first() ?? [
                'order_number',
                'date',
                'status',
                'customer',
                'total',
                'paid',
                'due',
                'method'
            ]));
            foreach ($rows as $r) {
                fputcsv($out, $r);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function resolveDates(Request $request, string $period): array
    {
        $today = now();

        if ($period === 'daily') {
            $d = $request->get('date', $today->toDateString());
            return [$d, $d];
        }

        if ($period === 'weekly') {
            // current week default (Mon-Sun)
            $start = $today->copy()->startOfWeek(Carbon::MONDAY);
            $end = $today->copy()->endOfWeek(Carbon::SUNDAY);
            return [
                $request->get('from', $start->toDateString()),
                $request->get('to', $end->toDateString()),
            ];
        }

        if ($period === 'monthly') {
            $m = $request->get('month', $today->format('Y-m')); // YYYY-MM
            $start = Carbon::createFromFormat('Y-m', $m)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $m)->endOfMonth();
            return [$start->toDateString(), $end->toDateString()];
        }

        if ($period === 'yearly') {
            $y = (int)$request->get('year', $today->year);
            $start = Carbon::create($y, 1, 1)->startOfDay();
            $end = Carbon::create($y, 12, 31)->endOfDay();
            return [$start->toDateString(), $end->toDateString()];
        }

        // range
        $from = $request->get('from', $today->copy()->startOfMonth()->toDateString());
        $to = $request->get('to', $today->toDateString());
        return [$from, $to];
    }

    private function bucketForPeriod(string $period, Carbon $from, Carbon $to): array
    {
        // label format per period (MySQL)
        if ($period === 'daily' || $period === 'range') {
            // group by day
            return [
                'type' => 'day',
                'select' => "DATE_FORMAT(created_at, '%Y-%m-%d')",
            ];
        }
        if ($period === 'weekly') {
            // group by day for nicer weekly charts too
            return [
                'type' => 'day',
                'select' => "DATE_FORMAT(created_at, '%Y-%m-%d')",
            ];
        }
        if ($period === 'monthly') {
            // group by day in month
            return [
                'type' => 'day',
                'select' => "DATE_FORMAT(created_at, '%Y-%m-%d')",
            ];
        }
        // yearly -> group by month
        return [
            'type' => 'month',
            'select' => "DATE_FORMAT(created_at, '%Y-%m')",
        ];
    }

    private function fillSeries($series, array $bucket, Carbon $from, Carbon $to): array
    {
        $map = $series->keyBy('label');

        $labels = [];
        $orders = [];
        $revenue = [];

        if ($bucket['type'] === 'month') {
            $cursor = $from->copy()->startOfMonth();
            $end = $to->copy()->startOfMonth();
            while ($cursor->lte($end)) {
                $k = $cursor->format('Y-m');
                $labels[] = $k;
                $orders[] = (int)($map[$k]->orders ?? 0);
                $revenue[] = (float)($map[$k]->revenue ?? 0);
                $cursor->addMonth();
            }
        } else {
            $cursor = $from->copy()->startOfDay();
            $end = $to->copy()->startOfDay();
            while ($cursor->lte($end)) {
                $k = $cursor->format('Y-m-d');
                $labels[] = $k;
                $orders[] = (int)($map[$k]->orders ?? 0);
                $revenue[] = (float)($map[$k]->revenue ?? 0);
                $cursor->addDay();
            }
        }

        return compact('labels', 'orders', 'revenue');
    }
}
