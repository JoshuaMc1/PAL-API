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
        Schema::create('favorite_characters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('list_id');
            $table->json('characters');
            $table->timestamps();

            $table->foreign('list_id')
                ->references('id')
                ->on('ani_lists')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('favorite_characters');
    }
};
