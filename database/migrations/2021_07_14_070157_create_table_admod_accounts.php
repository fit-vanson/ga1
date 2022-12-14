<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdmodAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admod_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('admod_pub_id');
            $table->text('access_token_full');
            $table->text('access_token');
            $table->string('admod_name');
            $table->string('g_client_id');
            $table->string('g_secret');
            $table->string('g_dev_key');
            $table->string('note');
            $table->string('error');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admod_accounts');
    }
}
