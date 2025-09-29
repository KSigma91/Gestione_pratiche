<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePraticheTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pratiche', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('codice', 100);
            $table->string('cliente_nome', 255);
            $table->string('caso')->nullable();
            $table->string('tipo_pratica', 100);
            $table->enum('stato', ['in_giacenza','in_lavorazione','completata','annullata'])->default('in_giacenza');
            $table->dateTime('data_arrivo');
            $table->dateTime('data_scadenza')->nullable();
            $table->text('note')->nullable();
            $table->boolean('alerted')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index('stato');
            $table->index('data_arrivo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pratiche');
    }
}
