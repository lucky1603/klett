<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('municipality_id');
            $table->unsignedBigInteger('institution_type_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('municipality_id')
                ->on("municipalities")
                ->references('id')
                ->cascadeOnDelete();

            $table->foreign('institution_type_id')
                ->on("institution_types")
                ->references('id')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schools');
    }
}
