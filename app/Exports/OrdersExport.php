<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Order::query()->with('items.product');

        if (!empty($this->filters['status']) && $this->filters['status'] !== 'all') {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['payment_method']) && $this->filters['payment_method'] !== 'all') {
            $query->where('payment_method', $this->filters['payment_method']);
        }

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Fecha',
            'Método de Pago',
            'Total',
            'Estado',
            'Productos',
        ];
    }

    public function map($order): array
    {
        $products = $order->items->map(function ($item) {
            return ($item->product->name ?? 'Producto eliminado') . " (x{$item->quantity})";
        })->implode(', ');

        return [
            $order->id,
            $order->customer_name,
            $order->created_at->format('d/m/Y H:i'),
            $order->payment_method,
            $order->total,
            $order->status,
            $products,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}