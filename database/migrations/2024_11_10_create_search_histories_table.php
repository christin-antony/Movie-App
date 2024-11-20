<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('search_histories', function (Blueprint $table) {
            $table->id();
            $table->string('query');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('search_histories');
    }
};