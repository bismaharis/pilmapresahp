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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('pilmapres_periods');
            $table->foreignId('student_id')->constrained('students');
            
            // Status Pendaftaran
            $table->enum('stage', ['fakultas', 'universitas'])->default('fakultas');
            $table->enum('status', ['draft', 'submitted', 'verified', 'rejected', 'finalist'])->default('draft');
            
            // File Utama
            $table->string('file_gk')->nullable(); 
            $table->string('file_transkrip')->nullable();
            
            // File Tambahan di tahap univ
            $table->string('file_poster_gk')->nullable();
            $table->string('file_poster_diri')->nullable();
            $table->string('video_link')->nullable(); 
            
            // Nilai Akhir
            $table->float('total_score_fakultas')->nullable();
            $table->float('total_score_univ')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
