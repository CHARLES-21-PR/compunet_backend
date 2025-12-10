<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Filtros
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');
        $customerId = $request->input('customerId');

        // Query base para ventas
        $salesQuery = Order::query();

        if ($startDate) {
            $salesQuery->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $salesQuery->whereDate('created_at', '<=', $endDate);
        }

        if ($customerId) {
            $salesQuery->where('user_id', $customerId);
        }

        // 0. Today Sales
        $todaySales = Order::whereDate('created_at', Carbon::today())->sum('total');

        // 0.1 Today Products Sold
        $todayProductsSold = Order::whereDate('orders.created_at', Carbon::today())
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->sum('order_items.quantity');

        // 1. Total Sales (Filtrado)
        // Solo sumamos pedidos que no estén cancelados o fallidos, si aplica.
        // Asumiremos que sumamos todo lo que coincida con el filtro por ahora, 
        // o idealmente solo los 'Pagado' o 'Completado'. 
        // El usuario pidió "Suma total del campo total", sin especificar estado, pero es mejor filtrar por validez.
        // Sin embargo, seguiré la instrucción literal: "Suma total del campo total de la tabla orders".
        $totalSales = $salesQuery->sum('total');

        // 2. New Orders (Pendientes)
        // Aquí no aplicamos filtro de fecha necesariamente, o tal vez sí? 
        // Generalmente "Nuevos pedidos" se refiere a los que requieren atención ahora.
        $newOrders = Order::where('status', 'Pendiente')->count();

        // 3. Customers Count (Non-admin)
        $customersCount = User::where('role', '!=', 'admin')->count();

        // 4. Products Count
        $productsCount = Product::count();

        // 5. Stock Status (Low Stock <= 10)
        $stockStatus = Product::where('stock', '<=', 10)
            ->orderBy('stock', 'asc')
            ->take(20)
            ->get(['id', 'name', 'stock']);

        // 6. Sales Chart (Quantity Sold)
        $groupBy = $request->input('groupBy', 'day');
        
        // Clonamos la query base para no afectar otros cálculos si se reusara, 
        // y agregamos el join con order_items
        $chartQuery = $salesQuery->clone()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id');

        // Definir formato de fecha según agrupación
        switch ($groupBy) {
            case 'year':
                $dateFormat = "DATE_FORMAT(orders.created_at, '%Y')";
                break;
            case 'month':
                $dateFormat = "DATE_FORMAT(orders.created_at, '%Y-%m')";
                break;
            case 'day':
            default:
                $dateFormat = "DATE(orders.created_at)";
                break;
        }

        $salesChart = $chartQuery->select(
                DB::raw("$dateFormat as date"),
                DB::raw('SUM(order_items.quantity) as quantity')
            )
            ->groupBy(DB::raw('date')) // Usamos el alias definido en select
            ->orderBy(DB::raw('date'), 'asc')
            ->get();

        // 7. Customers List for Filter
        $customers = User::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'today_sales' => (float) $todaySales,
            'today_products_sold' => (int) $todayProductsSold,
            'total_sales' => (float) $totalSales,
            'new_orders' => $newOrders,
            'customers_count' => $customersCount,
            'products_count' => $productsCount,
            'stock_status' => $stockStatus,
            'sales_chart' => $salesChart,
            'customers' => $customers,
        ]);
    }
}
