<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInternalModificationAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifiche_giacenza', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('pratica_id');
            $table->boolean('letta')->default(false);
            $table->timestamp('notificata_at')->nullable();
            $table->timestamps();

            $table->foreign('pratica_id')->references('id')->on('pratiche')->onDelete('cascade');
            $table->index('letta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifiche_giacenza');
    }
}
