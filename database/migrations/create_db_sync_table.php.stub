<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('filament_db_sync_table', function (Blueprint $table) {
            $table->id();
            $table->string('model') -> nullable();
            $table->string('model_id') -> nullable();
            $table->string('action') -> nullable();
            $table->json('data') -> nullable();
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->longText('failed_reason')->nullable();
            $table->timestamps();
        });
    }
};
