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
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cart_id')->nullable();
            $table->uuid('product_id');
            $table->uuid('owner_id');
            $table->uuid('user_id');

            $table->uuid('category_id')->nullable();
            $table->string('category_name')->nullable();
            $table->string('category_type')->nullable();
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->decimal('product_price', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->json('product_size')->nullable();
            $table->json('product_color')->nullable();
            $table->json('product_image')->nullable();
            $table->string('product_brand')->nullable();
            $table->integer('product_quantity');
            $table->integer('amount');
            $table->decimal('totalprice', 10, 2);
            $table->string('status')->default('active');
            $table->softDeletes();
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('merchants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
