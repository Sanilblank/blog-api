<?php

use App\Enums\DbTables;
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
        Schema::create(DbTables::POSTS->value, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(DbTables::USERS->value)->cascadeOnDelete();
            $table->foreignId('category_id')->constrained(DbTables::CATEGORIES->value)->restrictOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(DbTables::POSTS->value);
    }
};
