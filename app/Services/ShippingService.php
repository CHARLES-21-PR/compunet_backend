<?php

namespace App\Services;

class ShippingService
{
    public function calculateCost(array $addressData, float $cartTotal): float
    {
        // Envío gratis si supera 200
        if ($cartTotal > 200) {
            return 0.00;
        }

        // Lógica por ubicación (ejemplo simple)
        $city = $addressData['city'] ?? 'Lima';
        
        return match ($city) {
            'Lima' => 10.00,
            'Callao' => 12.00,
            default => 25.00,
        };
    }
}
