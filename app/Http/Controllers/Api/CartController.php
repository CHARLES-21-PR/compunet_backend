<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Facades\Shop;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = Shop::getCart();
        
        // Calcular totales para enviarlos al frontend
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        
        return response()->json([
            'items' => array_values($cart), // Convertir a array indexado para JSON
            'subtotal' => $subtotal,
            'igv' => $subtotal * 0.18,
            'total' => $subtotal * 1.18,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        try {
            Shop::addToCart($request->product_id, $request->quantity);
            return response()->json(['message' => 'Producto agregado al carrito']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function destroy(string $productId)
    {
        Shop::removeFromCart($productId);
        return response()->json(['message' => 'Producto eliminado del carrito']);
    }

    public function clear()
    {
        Shop::clearCart();
        return response()->json(['message' => 'Carrito vaciado']);
    }
}
