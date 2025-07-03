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
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category_name')->index();
            $table->string('category_type')->index();
            $table->uuid('owner_id');
            $table->foreign('owner_id')
                ->references('id')
                ->on('merchants')
                ->onDelete('cascade');
            $table->timestamps();
            $table->unique(['category_type', 'owner_id'], 'unique_category_type_per_owner');

        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {        Schema::dropIfExists('categories');
    }
};
