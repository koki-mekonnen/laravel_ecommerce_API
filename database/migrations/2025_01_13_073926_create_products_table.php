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

        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('product_name');
            $table->string('product_description');
            $table->decimal('product_price', 10, 2); // Ensure decimal precision
            $table->decimal('discount', 5, 2)->nullable();
            $table->json('product_size')->nullable();
            $table->json('product_color')->nullable();
            $table->json('product_image')->nullable();
            $table->string('product_brand')->nullable();
            $table->integer('product_quantity')->default(0);

            $table->uuid('owner_id');
            $table->uuid('category_id'); // Must match `categories.id`
            $table->string('category_name'); // Must match `categories.category_name`
            $table->string('category_type');

            $table->foreign('owner_id')
                ->references('id')
                ->on('merchants')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');

            $table->foreign('category_name')
                ->references('category_name')
                ->on('categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('category_type')
                ->references('category_type')
                ->on('categories')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['category_name']);
            $table->dropForeign(['category_type']);
            $table->dropForeign(['owner_id']);
        });

        Schema::dropIfExists('products');
    }
};
