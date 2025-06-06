<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bios', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('gender')->nullable();
            $table->integer('age')->nullable();
            $table->string('job')->nullable();
            $table->string('company')->nullable();
            $table->string('education')->nullable();
            $table->text('about')->nullable();

            $table->uuid('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bios');
    }
};
