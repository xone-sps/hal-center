<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRestaurantTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('branch_id');
            $table->string('status')->default('available');
            $table->integer('created_by');
            $table->dateTime('created_at');
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurant_tables');
    }
}
