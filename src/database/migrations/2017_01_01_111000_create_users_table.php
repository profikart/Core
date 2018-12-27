<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('person_id')->unsigned()->index();

            $table->integer('group_id')->unsigned()->index();
            $table->foreign('group_id')->references('id')->on('user_groups');

            $table->integer('role_id')->unsigned()->index('roles_id');
            $table->foreign('role_id')->references('id')->on('roles');

            $table->string('email')->unique();
            $table->string('password')->nullable();

            $table->boolean('is_active');

            $table->integer('created_by')->unsigned()->index()->nullable();
            $table->foreign('created_by')->references('id')->on('users');

            $table->integer('updated_by')->unsigned()->index()->nullable();
            $table->foreign('updated_by')->references('id')->on('users');
            $table->integer('csr_id')->unsigned()->index()->nullable();
            $table->foreign('csr_id')->references('id')->on('company_structure_references');
            $table->integer('ot_id')->unsigned()->nullable();


            $table->rememberToken();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
