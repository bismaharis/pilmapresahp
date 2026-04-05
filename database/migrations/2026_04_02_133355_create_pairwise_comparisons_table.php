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
        Schema::create('pairwise_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id_1')->constrained('criterias')->cascadeOnDelete();
            $table->foreignId('criteria_id_2')->constrained('criterias')->cascadeOnDelete();
            $table->decimal('value', 10, 6); // nilai a_ij yang diinput admin
            $table->timestamps();

            // Pastikan criteria_id_1 < criteria_id_2 untuk menghindari duplikasi
            $table->unique(['criteria_id_1', 'criteria_id_2']);
            // Tambahkan check constraint jika perlu, tapi untuk sekarang skip
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pairwise_comparisons');
    }
};
