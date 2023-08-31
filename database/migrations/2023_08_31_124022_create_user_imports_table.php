<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserImportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_imports', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable(false);
            $table->string('email')->nullable(false);
            $table->string('ime')->nullable();
            $table->string('prezime')->nullable();
            $table->string('site')->default('eUcionica.rs');
            $table->boolean('is_teacher')->default(false);
            $table->boolean('imported')->default(false);
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
        Schema::dropIfExists('user_imports');
    }
}
