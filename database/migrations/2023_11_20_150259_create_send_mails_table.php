<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendMailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_types', function(Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name');
            $table->string('description');
        });

        DB::table('email_types')
            ->insert([
                'id' => 1,
                'name' => 'reset_password',
                'description' => "Reset lozinke"
            ]);

        Schema::create('send_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('email_type_id')->default(1);
            $table->string('user_id')->nullable();
            $table->string('username');
            $table->string('email');
            $table->boolean('sent')->default(false);
            $table->timestamps();


            $table->foreign('email_type_id')->on('email_types')->references('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('send_emails');
        Schema::dropIfExists('email_types');
    }
}
