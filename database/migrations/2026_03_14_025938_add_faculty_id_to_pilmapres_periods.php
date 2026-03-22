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
        Schema::table('pilmapres_periods', function (Blueprint $table) {
            $table->foreignId('faculty_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilmapres_periods', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Faculty::class);
            $table->dropColumn('faculty_id');
        });
    }
};
