<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdmodReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admod_report', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pub_id');
            $table->date('date');
            $table->bigInteger('pageview');
            $table->bigInteger('impression');
            $table->bigInteger('click');
            $table->float('cpc');
            $table->float('ctr');
            $table->float('total');
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
        Schema::dropIfExists('admod_report');
    }
}
