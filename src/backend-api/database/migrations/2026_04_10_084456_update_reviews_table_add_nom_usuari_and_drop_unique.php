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
        Schema::table('reviews', function (Blueprint $table) {
            // Drop foreign keys first to allow dropping the unique index
            $table->dropForeign(['user_id']);
            $table->dropForeign(['esdeveniment_id']);

            // Now drop the unique index
            $table->dropUnique(['user_id', 'esdeveniment_id']);

            // Add the new column
            $table->string('nom_usuari')->after('id')->nullable();

            // Restore foreign keys (this will automatically create non-unique indexes)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('esdeveniment_id')->references('id')->on('esdeveniments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['esdeveniment_id']);
            $table->dropColumn('nom_usuari');
            $table->unique(['user_id', 'esdeveniment_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('esdeveniment_id')->references('id')->on('esdeveniments')->onDelete('cascade');
        });
    }
};