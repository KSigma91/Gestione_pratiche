<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->string('causer_id')->nullable();
            $table->text('properties')->nullable(); // TEXT per compatibilità MySQL
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('causer_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_log');
    }
}
