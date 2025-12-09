<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Facades\Shop;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:yape,tarjeta',
            'shipping_address' => 'required|array',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            // Validaciones condicionales para pagos
            'payment_details' => 'nullable|array',
            'payment_details.approval_code' => 'required_if:payment_method,yape',
        ]);

        try {
            $order = Shop::checkout($request->all());
            
            return response()->json([
                'message' => 'Compra realizada con éxito',
                'order' => $order
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al procesar la compra',
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
