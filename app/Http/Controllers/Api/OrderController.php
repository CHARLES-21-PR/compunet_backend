<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * Public endpoint for Checkout.
     */
    public function store(Request $request)
    {
        $request->validate([
            'billingData.name' => 'required|string',
            'billingData.email' => 'required|email',
            'billingData.address' => 'required|string',
            'billingData.document_type' => 'nullable|string',
            'billingData.document_number' => 'nullable|string',
            'paymentMethod' => 'required|string|in:tarjeta,yape',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $billing = $request->input('billingData');
                $items = $request->input('items');
                
                // Calcular total real desde el backend para seguridad (opcional pero recomendado)
                // Aquí usaremos el enviado o recalcularemos. Recalculando es mejor.
                $total = 0;
                foreach ($items as $item) {
                    $total += $item['price'] * $item['quantity'];
                }

                $order = Order::create([
                    'user_id' => Auth::id(), // Null si es invitado
                    'customer_name' => $billing['name'],
                    'customer_email' => $billing['email'],
                    'customer_document_type' => $billing['document_type'] ?? null,
                    'customer_document_number' => $billing['document_number'] ?? null,
                    'customer_address' => $billing['address'],
                    'total' => $total,
                    'status' => 'Pendiente',
                    'payment_method' => $request->input('paymentMethod'),
                    'payment_info' => $request->input('paymentData'),
                ]);

                foreach ($items as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['quantity'],
                    ]);
                    
                    // Opcional: Descontar stock aquí
                    // Product::find($item['id'])->decrement('stock', $item['quantity']);
                }

                return response()->json($order->load('items'), 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al procesar el pedido', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a listing of the resource.
     * Admin endpoint.
     */
    public function index(Request $request)
    {
        $query = Order::with('items');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
        }

        $orders = $query->latest()->paginate(10);

        return response()->json($orders);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $order = Order::with(['items.product', 'user'])->findOrFail($id);
        return response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string|in:Pendiente,Procesando,Completado,Cancelado',
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json($order);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Pedido eliminado']);
    }

    /**
     * Listar pedidos del usuario autenticado (Mi Historial)
     */
    public function myOrders(Request $request)
    {
        $orders = $request->user()->orders()
            ->with(['items.product'])
            ->latest()
            ->paginate(10);

        return response()->json($orders);
    }
}
