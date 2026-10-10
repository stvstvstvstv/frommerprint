<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('finished_material_id')
                ->constrained('materials')
                ->cascadeOnDelete();

            // Setup time needed to prepare the machine for this product
            $table->unsignedInteger('setup_time_minutes')->default(0);

            $table->boolean('active')->default(true);

            $table->timestamps();

            // One BOM per finished product (for now)
            $table->unique('finished_material_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
