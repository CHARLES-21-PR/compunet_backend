<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Order;

class InvoiceService
{
    public function generatePdf(Order $order)
    {
        // Datos simulados de Greenter si no existen en la orden
        $greenterData = [
            'hash' => $order->payment_info['hash'] ?? 'N/A',
            'cdr' => 'Aceptado'
        ];

        $pdf = Pdf::loadView('pdf.invoice', compact('order', 'greenterData'));
        
        return $pdf->stream('invoice-' . $order->id . '.pdf');
    }
}
