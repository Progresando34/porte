<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('users', 'profile_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('profile_id')->nullable()->after('password');
            });
        }

        $foreignExists = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'users'
            AND COLUMN_NAME = 'profile_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if (empty($foreignExists)) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('profile_id')
                      ->references('id')
                      ->on('profiles');
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
        });
    }
};