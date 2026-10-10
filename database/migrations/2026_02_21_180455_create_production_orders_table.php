<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_orders', function (Blueprint $table) {
        $table->id();

        // What we produce
        $table->foreignId('finished_material_id')
            ->constrained('materials');

        // Which jumbo we cut from
        $table->foreignId('jumbo_material_id')
            ->constrained('materials');

        // Planned quantity (rolls)
        $table->unsignedInteger('planned_rolls');

        /*
        |--------------------------------------------------
        | SNAPSHOT FIELDS (frozen at order creation)
        |--------------------------------------------------
        */

        // Finished roll geometry
        $table->decimal('snapshot_finished_width_mm', 10, 2);
        $table->decimal('snapshot_finished_length_m', 10, 3);

        // Jumbo geometry + economics
        $table->decimal('snapshot_jumbo_width_mm', 10, 2);
        $table->decimal('snapshot_jumbo_gsm', 10, 2);
        $table->decimal('snapshot_jumbo_cost_per_m', 12, 6);

        /*
        |--------------------------------------------------
        | OVERRIDABLE PER ORDER
        |--------------------------------------------------
        */

        $table->decimal('override_ink_kg_per_m2', 12, 6)->nullable();
        $table->unsignedInteger('override_foil_per_roll')->default(0);
        $table->unsignedInteger('override_rolls_per_box')->default(0);

        $table->string('status')->default('draft');

        $table->decimal('confirmed_total_cost', 14, 4)->nullable();
        $table->timestamp('confirmed_at')->nullable();

        $table->timestamps();
    });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_orders');
    }
};