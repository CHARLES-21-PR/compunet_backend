<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Facades\Shop;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function download(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        // Verificar autorización (Admin o dueño)
        if ($request->user()->role !== 'admin' && $order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return Shop::invoice()->generatePdf($order);
    }
}
