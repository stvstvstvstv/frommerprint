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
        Schema::dropIfExists('bom_packaging_rules');
    }

    public function down(): void
    {
        Schema::create('bom_packaging_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->cascadeOnDelete();
            $table->foreignId('inner_pack_material_id')->nullable()->constrained('materials');
            $table->unsignedInteger('inner_pack_qty')->nullable();
            $table->foreignId('outer_pack_material_id')->constrained('materials');
            $table->unsignedInteger('outer_pack_qty');
            $table->timestamps();
            $table->unique('bom_id');
        });
    }
};
