<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reserves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuari_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seient_id')->constrained('seients')->onDelete('cascade');
            $table->enum('estat', ['pendent', 'confirmada', 'expirada'])->default('pendent');
            $table->timestamp('data_expiracio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserves');
    }
};