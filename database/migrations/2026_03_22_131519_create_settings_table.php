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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable(); 
            $table->timestamps();
        });

        DB::table('settings')->insert([
            'key'        => 'guidebook_url',
            'value'      => 'https://lldikti6.id/wp-content/uploads/2025/05/Panduan-Pilmapres-Program-Sarjana-2025-1.pdf',
            'label'      => 'URL Guide Book Pilmapres',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
