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
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('metode_bayar', ['tunai', 'qris', 'transfer']);
            $table->string('bukti_bayar')->nullable();
            $table->enum('status_bayar', ['belum_bayar', 'menunggu_verifikasi', 'lunas'])->default('belum_bayar');
            $table->timestamp('tanggal_bayar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};