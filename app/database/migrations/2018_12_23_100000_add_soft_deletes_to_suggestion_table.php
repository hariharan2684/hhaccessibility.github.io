<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSoftDeletesToSuggestionTable extends Migration
{
    public function up()
    {
        Schema::table('suggestion', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('suggestion', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
