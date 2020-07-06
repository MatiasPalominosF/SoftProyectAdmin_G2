<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRequirementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_requirements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('internalId');
            $table->string('source');
            $table->integer('cost');
            $table->string('stability');
            $table->string('priority');
            $table->string('state');
            $table->bigInteger('idUser');
            $table->string('type');
            $table->string('description');
            $table->bigInteger('project_id');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_requirements');
    }
}
