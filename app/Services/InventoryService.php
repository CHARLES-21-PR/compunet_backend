<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Exception;

class InventoryService
{
    public function checkStock(int $productId, int $quantity): bool
    {
        $product = Product::find($productId);
        
        if (!$product || $product->stock < $quantity) {
            return false;
        }

        return true;
    }

    public function decrementStock(int $orderId): void
    {
        // Asumimos que tienes un modelo Order con relación orderItems
        // y cada item tiene product_id y quantity
        $orderItems = DB::table('order_items')->where('order_id', $orderId)->get();

        foreach ($orderItems as $item) {
            // Pessimistic Locking para evitar condiciones de carrera
            $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

            if ($product->stock < $item->quantity) {
                throw new Exception("Stock insuficiente para el producto: {$product->name}");
            }

            $product->stock -= $item->quantity;
            $product->save();
        }
    }
}
