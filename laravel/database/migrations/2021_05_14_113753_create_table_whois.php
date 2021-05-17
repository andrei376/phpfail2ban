<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWhois extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('whois', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();

            $table->timestamp('date')->useCurrent();

            $table->string('ipnum', 16)->charset('binary')->index();

            $table->integer('mask')->default(32)->index();

            $table->string('start', 16)->charset('binary')->index();
            $table->string('end', 16)->charset('binary')->index();

            $table->string('inetnum')->nullable();
            $table->string('range')->nullable();
            $table->string('netname')->nullable();
            $table->string('country')->nullable();
            $table->string('orgname')->nullable();

            $table->longText('output')->nullable();

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
        Schema::dropIfExists('whois');
    }
}
