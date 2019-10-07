<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableDashboardTweets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_tweets', function (Blueprint $table) {
            $table->unsignedBigInteger('dashboard_id')->index();
            $table->string('tweet_id',100)->index();
            $table
                ->foreign('dashboard_id')
                ->references('id')
                ->on('dashboards')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table
                ->foreign('tweet_id')
                ->references('id')
                ->on('tweets')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dashboard_tweets');
    }
}
