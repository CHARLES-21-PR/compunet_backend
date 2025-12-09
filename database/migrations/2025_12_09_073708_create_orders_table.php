<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_document_type')->nullable(); // boleta, factura
            $table->string('customer_document_number')->nullable();
            $table->text('customer_address');
            
            $table->decimal('total', 10, 2);
            $table->string('status')->default('Pendiente'); // Pendiente, Procesando, Completado, Cancelado
            $table->string('payment_method'); // tarjeta, yape
            $table->json('payment_info')->nullable(); // Detalles del pago
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
