<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTablesRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description', 200);
            $table->timestamps();
        });

        Schema::create('roles_users', function(Blueprint $table) {
            $table->integer('id_role')->index();
            $table->integer('id_user')->index();
            $table->timestamps();

            $table->primary(['id_role', 'id_user']);

            $table->foreign('id_role')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreign('id_user')->references('id')->on('users')->cascadeOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('roles_users');
        Schema::dropIfExists('roles');
    }
}
