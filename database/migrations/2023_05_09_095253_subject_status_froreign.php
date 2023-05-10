<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SubjectStatusFroreign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_user_subject', function(Blueprint $table) {
            $table->unsignedBigInteger('app_user_id');
            $table->unsignedBigInteger('subject_id');

            $table->unique(['app_user_id', 'subject_id']);

            $table->foreign('app_user_id')
                ->on('app_users')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreign('subject_id')
                ->on('subjects')
                ->references('id')
                ->cascadeOnDelete();
        });

        Schema::create('app_user_professional_status', function(Blueprint $table) {
            $table->unsignedBigInteger('app_user_id');
            $table->unsignedBigInteger('professional_status_id');

            $table->unique(['app_user_id', 'professional_status_id'], "app_user_prof_status_unique");

            $table->foreign('app_user_id')
                ->on('app_users')
                ->references('id')
                ->cascadeOnDelete();

            $table->foreign('professional_status_id')
                ->on('professional_statuses')
                ->references('id')
                ->cascadeOnDelete();

        });

        Schema::table('app_users', function(Blueprint $table) {
            $table->foreign('school_id','app_users_schools_foreign')
                ->on('schools')
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

        Schema::table('app_users', function(Blueprint $table) {
            $table->dropForeign('app_users_schools_foreign');
        });

        Schema::dropIfExists('app_user_subject');
        Schema::dropIfExists('app_user_professional_status');

    }
}
