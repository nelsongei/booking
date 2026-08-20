<?php

namespace App\Domain\Pos\Services;

use App\Domain\Pos\Models\PosOrder;
use Illuminate\Support\Facades\DB;

class StockControlService
{
    /**
     * Auto-deduct raw ingredient stock based on sold POS menu items and recipes.
     */
    public function deductStockForOrder(PosOrder $order): void
    {
        $orderItems = DB::table('pos_order_items')
            ->where('pos_order_id', $order->id)
            ->get();

        foreach ($orderItems as $item) {
            $recipes = DB::table('recipes')
                ->where('pos_menu_item_id', $item->pos_menu_item_id)
                ->get();

            foreach ($recipes as $recipe) {
                $totalQtyUsed = $recipe->quantity_used * $item->quantity;

                DB::table('stock_items')
                    ->where('id', $recipe->stock_item_id)
                    ->decrement('quantity_on_hand', $totalQtyUsed);
            }
        }
    }

    /**
     * Query low-stock alert items for a property.
     */
    public function getLowStockAlerts(int $propertyId): array
    {
        return DB::table('stock_items')
            ->where('property_id', $propertyId)
            ->whereRaw('quantity_on_hand <= reorder_level')
            ->get()
            ->toArray();
    }
}
