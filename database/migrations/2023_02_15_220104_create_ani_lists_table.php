<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ani_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anime_id');
            $table->unsignedBigInteger('user_id');
            $table->integer('overall_rating')->default(0);
            $table->integer('animation_rating')->default(0);
            $table->integer('history_rating')->default(0);
            $table->integer('characters_rating')->default(0);
            $table->integer('music_rating')->default(0);
            $table->integer('currently')->default(1);
            $table->text('the_good')->nullable();
            $table->text('the_bad')->nullable();
            $table->timestamps();
            $table->integer('status')->default(1);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('anime_id')
                ->references('id')
                ->on('animes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ani_lists');
    }
};
