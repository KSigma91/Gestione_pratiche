<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePracticeArchivesTable extends Migration
{
    public function up()
    {
        Schema::create('practice_archives', function (Blueprint $table) {
            $table->bigIncrements('id');

            // riferimento alla pratica originale (se ancora presente)
            $table->unsignedBigInteger('practice_id')->nullable()->index();

            // snapshot dei campi della pratica
            $table->string('codice')->nullable();
            $table->string('cliente_nome')->nullable();
            $table->string('tipo_pratica')->nullable();
            $table->string('caso')->nullable();
            $table->string('stato')->nullable();
            $table->dateTime('data_arrivo')->nullable();
            $table->dateTime('data_scadenza')->nullable();
            $table->text('note')->nullable();

            // info sull'azione che ha generato l'entry d'archivio
            $table->string('action')->nullable(); // created, updated, deleted, restored, force_deleted
            $table->unsignedBigInteger('action_by')->nullable()->index(); // user id (se disponibile)
            $table->string('action_by_name')->nullable();

            // optional: campo properties testuale (leggero) se vuoi aggiungere note
            $table->text('action_note')->nullable();

            $table->timestamps(); // created_at = momento dell'archiviazione
        });
    }

    public function down()
    {
        Schema::dropIfExists('practice_archives');
    }
}
