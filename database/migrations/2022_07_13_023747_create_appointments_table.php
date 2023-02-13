<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->integer('session_id');
            $table->text('status');
            $table->integer('token');
            $table->text('uuid');
            $table->dateTime('scheduledTime');
            $table->dateTime('estimatedTime');
            $table->dateTime('arrivedTime')->nullable();
            $table->dateTime('servingStartedTime')->nullable();
            $table->dateTime('servingCompletedTime')->nullable();
            $table->boolean('paid')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('appointments');
    }
};
