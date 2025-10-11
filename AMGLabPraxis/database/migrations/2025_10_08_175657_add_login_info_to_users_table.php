<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLoginInfoToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users','last_login_at')) {
                    $table->timestamp('last_login_at')->nullable()->after('remember_token');
                }
                if (! Schema::hasColumn('users','last_login_ip')) {
                    $table->string('last_login_ip',45)->nullable()->after('last_login_at');
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users','last_login_at')) $table->dropColumn('last_login_at');
            if (Schema::hasColumn('users','last_login_ip')) $table->dropColumn('last_login_ip');
        });
    }
}
