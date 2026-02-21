<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bom_packaging_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bom_id')
                ->constrained('boms')
                ->cascadeOnDelete();

            // Optional inner packaging (e.g. shrink foil)
            $table->foreignId('inner_pack_material_id')
                ->nullable()
                ->constrained('materials');

            // Number of finished units per inner pack (e.g. 6 rolls per foil)
            $table->unsignedInteger('inner_pack_qty')->nullable();

            // Outer packaging (box)
            $table->foreignId('outer_pack_material_id')
                ->constrained('materials');

            // Either:
            // - rolls per box (direct)
            // - inner packs per box (wrapped)
            $table->unsignedInteger('outer_pack_qty');

            $table->timestamps();

            // One packaging rule per BOM
            $table->unique('bom_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bom_packaging_rules');
    }
};