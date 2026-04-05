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
        Schema::table('criterias', function (Blueprint $table) {
            $table->decimal('cr_value', 10, 6)->default(0.0)->after('max_score');
            $table->enum('cr_status', ['consistent', 'inconsistent'])->default('consistent')->after('cr_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('criterias', function (Blueprint $table) {
            $table->dropColumn(['cr_value', 'cr_status']);
        });
    }
};
