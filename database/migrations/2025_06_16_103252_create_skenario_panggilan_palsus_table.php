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
        Schema::create('skenario_panggilan_palsus', function (Blueprint $table) {
            $table->id();
            $table->string('judul_skenario');
            $table->string('nama_penelepon');
            $table->string('audio_url');
            $table->text('teks_skrip')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skenario_panggilan_palsus');
    }
};
