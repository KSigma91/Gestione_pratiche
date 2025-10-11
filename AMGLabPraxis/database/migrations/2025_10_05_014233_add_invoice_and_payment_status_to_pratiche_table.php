<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceAndPaymentStatusToPraticheTable extends Migration
{
    public function up()
    {
        Schema::table('pratiche', function (Blueprint $table) {
            // usa stringa per massima compatibilità; valori: 'emessa'|'non_emessa' e 'pagato'|'non_pagato'
            $table->string('stato_fattura', 20)->default('non_emessa')->after('stato');
            $table->string('stato_pagamento', 20)->default('non_pagato')->after('stato_fattura');
        });
    }

    public function down()
    {
        Schema::table('pratiche', function (Blueprint $table) {
            $table->dropColumn(['stato_fattura', 'stato_pagamento']);
        });
    }
}
