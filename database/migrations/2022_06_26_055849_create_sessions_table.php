<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('queue_id');
            $table->integer('location_id')->nullable();
            $table->date('date');
            $table->time('startTime');
            $table->time('endTime');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}; 