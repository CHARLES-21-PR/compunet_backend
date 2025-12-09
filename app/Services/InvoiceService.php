<?php

namespace App\Services;

// use Dompdf\Dompdf;

class InvoiceService
{
    public function generatePdf($order, $greenterData)
    {
        // $dompdf = new Dompdf();
        // $html = view('invoices.default', compact('order', 'greenterData'))->render();
        // $dompdf->loadHtml($html);
        // $dompdf->render();
        // return $dompdf->output();

        return "Contenido binario del PDF simulado para la orden " . $order->id;
    }
}
