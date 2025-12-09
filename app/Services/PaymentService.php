<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use Exception;

class PaymentService
{
    public function processPayment(Order $order, string $method, array $data): bool
    {
        $status = 'pending';
        $transactionId = null;
        $payload = [];

        try {
            if ($method === 'yape') {
                // Simulación de validación Yape
                if (empty($data['approval_code'])) {
                    throw new Exception("Código de aprobación requerido para Yape");
                }
                // Aquí se validaría contra API real
                $status = 'approved';
                $transactionId = $data['approval_code'];
                $payload = ['provider' => 'yape', 'response' => 'ok'];

            } elseif ($method === 'tarjeta') {
                // Simulación de pasarela (Stripe/MercadoPago)
                // $charge = Stripe::charge(...);
                $status = 'approved';
                $transactionId = 'txn_' . uniqid();
                $payload = ['provider' => 'stripe_simulated', 'last4' => '4242'];
            } else {
                throw new Exception("Método de pago no soportado");
            }

            // Registrar el pago
            Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'method' => $method,
                'status' => $status,
                'transaction_id' => $transactionId,
                'payload' => $payload,
            ]);

            return $status === 'approved';

        } catch (Exception $e) {
            // Registrar intento fallido
            Payment::create([
                'order_id' => $order->id,
                'amount' => $order->total,
                'method' => $method,
                'status' => 'rejected',
                'payload' => ['error' => $e->getMessage()],
            ]);
            
            throw $e;
        }
    }
}
