<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('branch_type');
            $table->tinyInteger('taxable');
            $table->tinyInteger('is_default')->default(0);
            $table->integer('tax_id')->default(0);
            $table->tinyInteger('is_cash_register');
            $table->integer('user_id')->default(null);
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
        Schema::dropIfExists('branches');
    }
}
