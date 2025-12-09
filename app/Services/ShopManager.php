<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class ShopManager
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService
    ) {}

    public function getCart()
    {
        return $this->cartService->getCart();
    }

    public function addToCart(int $productId, int $quantity)
    {
        $this->cartService->add($productId, $quantity);
    }

    public function removeFromCart(int $productId)
    {
        $this->cartService->remove($productId);
    }

    public function clearCart()
    {
        $this->cartService->clear();
    }

    public function checkout(array $data)
    {
        // Extraer datos necesarios del request
        $paymentMethod = $data['payment_method'];
        $shippingData = $data['shipping_address'] ?? [];
        // Agregar detalles de pago a shippingData para pasarlo al servicio (o refactorizar firma)
        $shippingData['payment_details'] = $data['payment_details'] ?? [];

        return $this->checkoutService->createOrder(
            Auth::user(),
            $this->cartService->getCart(),
            $paymentMethod,
            $shippingData
        );
    }
}
