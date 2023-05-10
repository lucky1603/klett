<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_users', function (Blueprint $table) {
            $table->id();
            $table->string('ime');
            $table->string('prezime');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('country_id');
            $table->string('adresa');
            $table->string('pb');
            $table->string('mesto');
            $table->string('tel1');
            $table->string('tel2');
            $table->boolean('is_teacher')->default(false);
            $table->unsignedBigInteger("school_id")->nullable(true);
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
        Schema::dropIfExists('app_users');
    }
}
