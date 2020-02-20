<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateCornJobLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corn_job_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('branch_id');
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));;
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP'));;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('corn_job_logs');
    }
}
