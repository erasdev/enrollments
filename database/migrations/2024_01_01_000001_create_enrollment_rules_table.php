<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enrollment_rules', function (Blueprint $table) {
            $table->id();
            $table->morphs('enrollable');
            $table->json('config')->nullable();
            $table->string('type');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('enrollment_rules');
    }
};
