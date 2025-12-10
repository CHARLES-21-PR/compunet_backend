<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante Electrónico</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .company-info { margin-bottom: 20px; }
        .client-info { margin-bottom: 20px; border: 1px solid #ccc; padding: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .totals { text-align: right; }
        .qr { text-align: center; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>COMPUNET S.A.C.</h1>
        <p>RUC: 20123456789</p>
        <p>Av. Tecnológica 123, Lima, Perú</p>
        <h2>BOLETA DE VENTA ELECTRÓNICA</h2>
        <h3>{{ $order->id }}-{{ time() }}</h3>
    </div>

    <div class="client-info">
        <strong>Cliente:</strong> {{ $order->customer_name }}<br>
        <strong>Documento:</strong> {{ $order->customer_document_type }} - {{ $order->customer_document_number }}<br>
        <strong>Dirección:</strong> {{ $order->customer_address }}<br>
        <strong>Fecha de Emisión:</strong> {{ $order->created_at->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Precio Unit.</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->name ?? 'Producto' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>S/ {{ number_format($item->price, 2) }}</td>
                <td>S/ {{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <p><strong>Subtotal:</strong> S/ {{ number_format($order->subtotal ?? $order->total / 1.18, 2) }}</p>
        <p><strong>IGV (18%):</strong> S/ {{ number_format($order->igv ?? $order->total - ($order->total / 1.18), 2) }}</p>
        <p><strong>Total a Pagar:</strong> S/ {{ number_format($order->total, 2) }}</p>
    </div>

    <div class="qr">
        <p>Representación Impresa del Comprobante Electrónico</p>
        <p>Hash: {{ $greenterData['hash'] }}</p>
        <!-- Aquí iría la imagen del QR real -->
        <div style="border: 1px solid #000; width: 100px; height: 100px; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
            QR CODE
        </div>
    </div>
</body>
</html>
