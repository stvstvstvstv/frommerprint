<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bom_yield_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bom_id')->constrained('boms')->cascadeOnDelete();

            // Preferred jumbo SKU for this BOM
            $table->foreignId('preferred_jumbo_material_id')
                ->constrained('materials');

            // Ink coefficient (kg per m2). Null or 0 => no ink
            $table->decimal('ink_kg_per_m2', 12, 6)->nullable();

            // Optional: choose which ink material SKU to cost against (if you have multiple inks)
            $table->foreignId('ink_material_id')->nullable()->constrained('materials');

            // Packaging (simple v1)
            $table->unsignedInteger('foil_per_roll')->default(0);
            $table->unsignedInteger('rolls_per_box')->default(0);
            $table->foreignId('foil_material_id')->nullable()->constrained('materials');
            $table->foreignId('box_material_id')->nullable()->constrained('materials');

            $table->timestamps();

            $table->unique('bom_id'); // 1 rule row per BOM for now
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_yield_rules');
    }
};