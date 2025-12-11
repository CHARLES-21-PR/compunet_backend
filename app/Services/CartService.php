<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Product;

class CartService
{
    protected $cartKey = 'shopping_cart';
    
    // Propiedades para el manejo del estado en la solicitud (Singleton)
    protected $items = [];
    protected $subtotal = 0;
    protected $igv = 0;
    protected $total = 0;

    /**
     * Carga los items recibidos del request y recalcula con precios reales de la BD.
     * @param array $requestItems Lista de items con ['id' => int, 'quantity' => int]
     */
    public function loadItems(array $requestItems)
    {
        $this->items = [];
        $this->subtotal = 0;
        $this->igv = 0;
        $this->total = 0;

        foreach ($requestItems as $item) {
            $product = Product::find($item['id']);
            
            if ($product) {
                $quantity = (int) $item['quantity'];
                $price = (float) $product->price;
                $subtotalItem = $price * $quantity;

                $this->items[] = [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'price' => $price,
                    'quantity' => $quantity,
                    'subtotal' => $subtotalItem
                ];

                $this->total += $subtotalItem;
            }
        }

        // Calcular IGV y Subtotal (Asumiendo que el precio incluye IGV o se calcula aparte)
        // En Perú, generalmente el precio mostrado incluye IGV.
        // Subtotal = Total / 1.18
        // IGV = Total - Subtotal
        $this->subtotal = $this->total / 1.18;
        $this->igv = $this->total - $this->subtotal;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getTotal()
    {
        return round($this->total, 2);
    }

    public function getSubtotal()
    {
        return round($this->subtotal, 2);
    }

    public function getIgv()
    {
        return round($this->igv, 2);
    }

    // --- Métodos Legacy (Sesión) ---

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

    protected function saveCart($cart)
    {
        session()->put($this->cartKey, $cart);
        if (Auth::check()) {
            // Sincronizar con BD
        }
    }
}
