<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Product;

class CartService
{
    protected $cartKey = 'shopping_cart';

    public function getCart()
    {
        if (Auth::check()) {
            // Lógica para recuperar de BD (ej. tabla carts)
            // Por simplicidad, retornamos una estructura simulada o caché
            return session()->get($this->cartKey, []);
        }
        return session()->get($this->cartKey, []);
    }

    public function add(int $productId, int $quantity)
    {
        $cart = $this->getCart();
        $product = Product::findOrFail($productId);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'image' => $product->image
            ];
        }

        $this->saveCart($cart);
    }

    public function remove(int $productId)
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        $this->saveCart($cart);
    }

    public function clear()
    {
        session()->forget($this->cartKey);
        if (Auth::check()) {
            // Limpiar BD
        }
    }

    public function getSubtotal(): float
    {
        $cart = $this->getCart();
        return array_reduce($cart, function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function getIgv(): float
    {
        return $this->getSubtotal() * 0.18;
    }

    public function getTotal(): float
    {
        return $this->getSubtotal() + $this->getIgv();
    }

    protected function saveCart($cart)
    {
        session()->put($this->cartKey, $cart);
        if (Auth::check()) {
            // Sincronizar con BD
        }
    }
}
