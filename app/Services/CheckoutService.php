<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Enums\OrderStatus;
use Exception;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutService
{
    public function __construct(
        protected CartService $cartService,
        protected InventoryService $inventoryService,
        protected PaymentService $paymentService,
        protected ShippingService $shippingService,
        protected GreenterService $greenterService,
        protected InvoiceService $invoiceService
    ) {}

    public function createOrder(User $user, array $cartItems, string $paymentMethod, array $shippingData)
    {
        return DB::transaction(function () use ($user, $cartItems, $paymentMethod, $shippingData) {
            
            if (empty($cartItems)) {
                throw new Exception("El carrito está vacío");
            }

            // 1. Verificar Stock
            foreach ($cartItems as $item) {
                if (!$this->inventoryService->checkStock($item['product_id'], $item['quantity'])) {
                    throw new Exception("Stock insuficiente para " . $item['name']);
                }
            }

            // 2. Calcular Totales
            $subtotal = $this->cartService->getSubtotal();
            $igv = $this->cartService->getIgv();
            $shippingCost = $this->shippingService->calculateCost($shippingData, $subtotal);
            $total = $subtotal + $igv + $shippingCost;

            // 3. Crear Orden (PENDIENTE)
            $order = Order::create([
                'user_id' => $user->id,
                'status' => OrderStatus::PENDING,
                'payment_method' => $paymentMethod,
                'subtotal' => $subtotal,
                'igv' => $igv,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'shipping_address' => $shippingData,
            ]);

            // 4. Crear Items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['quantity'],
                ]);
            }

            // 5. Procesar Pago
            try {
                // Extraer datos específicos del pago (ej. token de tarjeta, código yape)
                $paymentData = $shippingData['payment_details'] ?? []; 
                
                $paid = $this->paymentService->processPayment($order, $paymentMethod, $paymentData);

                if ($paid) {
                    $order->update(['status' => OrderStatus::PAID]);
                    
                    // 6. Descontar Stock
                    $this->inventoryService->decrementStock($order->id);

                    // 7. Facturación (Simulación SUNAT)
                    $greenterResponse = $this->greenterService->emitirComprobante($order);
                    
                    // Guardar hash/cdr en payment_info o en una tabla invoices
                    $paymentInfo = $order->payment_info ?? [];
                    $paymentInfo['sunat'] = $greenterResponse;
                    $order->update(['payment_info' => $paymentInfo]);
                }

            } catch (Exception $e) {
                $order->update(['status' => OrderStatus::FAILED]);
                throw $e; // Re-lanzar para que el controlador lo maneje
            }

            // 8. Limpiar Carrito
            $this->cartService->clear();

            return $order;
        });
    }
}
