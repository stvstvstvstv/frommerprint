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
        Schema::table('bom_yield_rules', function (Blueprint $table) {
            $table->foreignId('tube_material_id')->nullable()->constrained('materials')->nullOnDelete()->after('ink_material_id');
        });
    }

    public function down(): void
    {
        Schema::table('bom_yield_rules', function (Blueprint $table) {
            $table->dropForeign(['tube_material_id']);
            $table->dropColumn('tube_material_id');
        });
    }
};
