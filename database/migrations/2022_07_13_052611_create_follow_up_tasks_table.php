<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('follow_up_tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('appointment_id');
            $table->text('summary');
            $table->text('description');
            $table->text('status');
            $table->integer('user_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('follow_up_tasks');
    }
};
