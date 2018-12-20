<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->increments('id');

            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->integer('cs_id');
            $table->json('display_to');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_groups');
    }
}
