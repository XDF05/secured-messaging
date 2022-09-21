<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('from_id');
            $table->unsignedBigInteger('to_id');
            $table->foreign('from_id', 'message_from')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_id', 'message_to')->references('id')->on('users')->onDelete('cascade');
            $table->text('content');
            $table->text('iv');
            $table->text('digital_signature');
            $table->timestamp('created_at')->useCurrent();
            $table->dateTime('read_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
