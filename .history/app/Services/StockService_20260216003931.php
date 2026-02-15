<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;

class StockService
{
    /**
     * Update products.stock and log movement in stock_movements table.
     *
     * type:
     *  - in      : increase stock
     *  - out     : decrease stock
     *  - adjust  : set exact stock (qty is final stock)
     */
    public static function move(
        Product $product,
        string $type,
        int $qty,
        ?int $orderId = null,
        ?string $reason = null,
        ?string $note = null
    ): void
    {
        // safety
        if ($qty < 0) $qty = abs($qty);

        $before = (int) ($product->stock ?? 0);

        if ($type === 'in') {
            $after = $before + $qty;
            $movementQty = $qty;
        } elseif ($type === 'out') {
            $after = $before - $qty;
            if ($after < 0) $after = 0; // avoid negative stock
            $movementQty = $qty;
        } else { // adjust => set exact stock
            $after = $qty; // here qty means final stock
            $movementQty = abs($after - $before);
        }

        // update product stock
        $product->stock = $after;
        $product->save();

        // log movement
        StockMovement::create([
            'product_id'    => $product->id,
            'order_id'      => $orderId,
            'type'          => $type,
            'qty'           => $movementQty,
            'before_stock'  => $before,
            'after_stock'   => $after,
            'reason'        => $reason,
            'note'          => $note,
        ]);
    }
}
