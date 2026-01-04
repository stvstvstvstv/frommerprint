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
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->nullable()->after('unit_id');
            $table->string('currency', 3)->default('EUR')->after('price');
            $table->foreignId('pricing_unit_id')->nullable()->after('currency');
            $table->unsignedInteger('pricing_per')->default(1)->after('pricing_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            //
        });
    }
};
