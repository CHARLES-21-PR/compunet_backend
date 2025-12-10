<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class ShopManager
{
    public function __construct(
        protected CartService $cartService,
        protected CheckoutService $checkoutService,
        protected GreenterService $greenterService,
        protected InvoiceService $invoiceService,
        protected InventoryService $inventoryService,
        protected PaymentService $paymentService,
        protected ShippingService $shippingService
    ) {}

    // Accessors for Services
    public function cart() { return $this->cartService; }
    public function checkoutService() { return $this->checkoutService; }
    public function greenter() { return $this->greenterService; }
    public function invoice() { return $this->invoiceService; }
    public function inventory() { return $this->inventoryService; }
    public function payment() { return $this->paymentService; }
    public function shipping() { return $this->shippingService; }

    // Facade Methods (Delegation)
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

    public function processCheckout(array $data)
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
