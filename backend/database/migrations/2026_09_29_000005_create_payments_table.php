<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->string('order_id')->unique(); // e.g. ORD-TAG-202609-0001-XXXX
            $table->string('payment_type')->nullable(); // 'qris', 'bank_transfer', 'cash', 'gopay', etc.
            $table->decimal('gross_amount', 10, 2);
            $table->enum('transaction_status', ['pending', 'settlement', 'expire', 'cancel', 'failed'])->default('pending');
            $table->string('snap_token')->nullable();
            $table->string('payment_method_detail')->nullable(); // e.g. 'BCA VA 912839123'
            $table->timestamp('paid_at')->nullable();
            $table->json('midtrans_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
