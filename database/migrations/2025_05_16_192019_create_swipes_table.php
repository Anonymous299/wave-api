<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swipes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('direction', ['left', 'right']);

            $table->uuid('swiper_id');
            $table->foreign('swiper_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->uuid('swipee_id');
            $table->foreign('swipee_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swipes');
    }
};
