<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHangoutComplementaryTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hangout_complementary_tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hangout_id');
            $table->string('type');
            $table->integer('quantity');
            $table->string('title');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('date');
            $table->softDeletes();
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
        Schema::dropIfExists('hangout_complementary_tickets');
    }
}
