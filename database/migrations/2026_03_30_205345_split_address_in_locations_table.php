<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, DB};

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('city')->nullable()->after('address');
            $table->string('street')->nullable()->after('city');
        });

        // Migrate existing data into street
        DB::table('locations')->whereNotNull('address')->update([
            'street' => DB::raw('`address`'),
        ]);
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropColumn(['city', 'street']);
        });
    }
};