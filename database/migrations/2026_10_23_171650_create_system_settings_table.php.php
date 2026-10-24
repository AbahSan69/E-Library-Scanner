<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $t) {
            $t->id();
            $t->unsignedInteger('fine_rate_per_day')->default(1000);
            $t->unsignedInteger('grace_period_days')->default(0);
            $t->boolean('exclude_saturday')->default(false);
            $t->boolean('exclude_sunday')->default(false);
            $t->boolean('exclude_holidays')->default(false);
            $t->timestamps();
        });

        // Single-row config: satu baris default
        DB::table('system_settings')->insert([
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
}