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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecturer_id')->constrained('lecturers'); 
            $table->foreignId('criteria_id')->constrained('criterias'); 
            
            $table->float('score'); 
            
            $table->text('notes')->nullable(); 
            $table->timestamps();
            $table->unique(['registration_id', 'lecturer_id', 'criteria_id'], 'unique_assessment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
