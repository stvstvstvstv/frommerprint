<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bom_id')
                ->constrained('boms')
                ->cascadeOnDelete();

            $table->foreignId('component_material_id')
                ->constrained('materials');

            // Logical role within the BOM (paper, ink, machine, etc.)
            $table->string('role');

            // Quantity of the component (pcs, kg, meters, minutes, hours)
            $table->decimal('quantity', 12, 4);

            // Optional waste factor, e.g. printing waste per colour
            $table->decimal('waste_factor_pct', 5, 2)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['bom_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};