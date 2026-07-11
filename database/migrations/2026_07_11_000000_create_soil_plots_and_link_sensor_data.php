<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soil_plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('sensor_token', 64)->unique();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::table('sensor_data', function (Blueprint $table) {
            $table->foreignId('soil_plot_id')
                ->nullable()
                ->after('id')
                ->constrained('soil_plots')
                ->cascadeOnDelete();
        });

        // Data dari versi lama tidak dibuang. Jika sudah ada user, kelompokkan
        // sebagai "Data Lama" milik user pertama agar tetap dapat diakses.
        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        if ($firstUserId && DB::table('sensor_data')->whereNull('soil_plot_id')->exists()) {
            $legacyId = DB::table('soil_plots')->insertGetId([
                'user_id' => $firstUserId,
                'name' => 'Data Lama',
                'sensor_token' => Str::random(48),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('sensor_data')->whereNull('soil_plot_id')->update([
                'soil_plot_id' => $legacyId,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('sensor_data', function (Blueprint $table) {
            $table->dropConstrainedForeignId('soil_plot_id');
        });

        Schema::dropIfExists('soil_plots');
    }
};
