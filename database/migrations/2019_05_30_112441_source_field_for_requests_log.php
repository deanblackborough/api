<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SourceFieldForRequestsLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('request_log', function (Blueprint $table) {
            $table->string('source', 25)
                ->after('method')
                ->default('api');
            $table->index('source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('request_log', function (Blueprint $table) {
            $table->dropIndex('request_log_source_index');
            $table->dropColumn('source');
        });
    }
}