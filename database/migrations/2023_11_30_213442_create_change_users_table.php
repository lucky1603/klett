<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChangeUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('change_users', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('username');
            $table->string('email');
            $table->string('firstName');
            $table->string('lastName');
            $table->string('source');
            $table->string('role');
            $table->boolean('klf_korisnik');
            $table->boolean('pedagoska_sveska');
            $table->boolean('testomat');
            $table->boolean('changed')->default(false);
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
        Schema::dropIfExists('change_users');
    }
}
