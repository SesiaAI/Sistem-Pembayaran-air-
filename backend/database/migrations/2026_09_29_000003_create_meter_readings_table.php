<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnDelete();
            $table->integer('period_month'); // 1 - 12
            $table->integer('period_year');  // e.g. 2026
            $table->integer('initial_meter')->default(0);
            $table->integer('final_meter');
            $table->integer('total_usage'); // final_meter - initial_meter
            $table->string('photo_url')->nullable();
            $table->date('reading_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['customer_id', 'period_month', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
    }
};
