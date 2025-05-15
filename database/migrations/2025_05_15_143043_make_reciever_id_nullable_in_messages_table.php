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
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('reciever_id')->nullable()->change(); // ✅ Only change the nullability
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // $table->unsignedBigInteger('reciever_id')->nullable(false)->change();
            $table->foreignId('reciever_id')->nullable(false)->change();
        });
    }
};
