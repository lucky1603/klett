<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserImportsTableFix extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_imports', function(Blueprint $table) {
            $table->dropColumn('site');
            $table->dropColumn('is_teacher');
            $table->string('rola')->nullable()->after('imported');
            $table->string('password')->nullable()->after('rola');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_imports', function(Blueprint $table) {
            $table->dropColumn('rola');
            $table->dropColumn('password');
            $table->boolean('is_teacher')->default(false)->after('imported');
            $table->string('site')->nullable()->after('is_teacher');
        });
    }
}
