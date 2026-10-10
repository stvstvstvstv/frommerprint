<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('width', 10, 2)->nullable()->after('name');
            $table->decimal('length', 10, 2)->nullable()->after('width');
            $table->decimal('gsm', 10, 2)->nullable()->after('length');
            $table->decimal('thickness', 8, 3)->nullable()->after('gsm');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn([
                'width',
                'length',
                'gsm',
                'thickness',
            ]);
        });
    }
};
