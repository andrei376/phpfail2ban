<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIpInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ip_infos', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->char('ipnum', 16)->charset('binary');
            $table->integer('mask')->default(32)->index();

            $table->char('start', 16)->charset('binary')->index();
            $table->char('end', 16)->charset('binary')->index();

            $table->string('inetnum')->nullable();
            $table->string('netname')->nullable();
            $table->string('country')->nullable();
            $table->string('orgname')->nullable();
            $table->string('geoipcountry')->nullable();

            $table->timestamp('last_check')->nullable()->index();

            $table->tinyInteger('checked')->default(0)->index();

            $table->unique(['ipnum', 'mask']);

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
        Schema::dropIfExists('ip_infos');
    }
}
