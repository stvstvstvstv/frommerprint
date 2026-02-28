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
            $table->decimal('paper_waste_pct', 5, 2)->default(1.00)->after('preferred_jumbo_material_id');
        });
    }

    public function down(): void
    {
        Schema::table('bom_yield_rules', function (Blueprint $table) {
            $table->dropColumn('paper_waste_pct');
        });
    }
};
