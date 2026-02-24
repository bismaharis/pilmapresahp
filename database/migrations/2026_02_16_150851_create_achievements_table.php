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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
    
            $table->string('name'); 
            $table->string('organizer'); 
            $table->year('year');
            $table->string('type'); 
            $table->string('level'); 
            $table->string('category'); 
            
            $table->string('file_proof'); 
            
            $table->boolean('is_validated')->default(false);
            $table->decimal('score', 8, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
