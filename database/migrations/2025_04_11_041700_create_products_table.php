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
        Schema::connection('pgsql')->create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->uuid('category_id');
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for better query performance
            $table->index('sku');
            $table->index('name');
            $table->index('price');
            $table->index('stock');
            $table->index('category_id');
        });

        Schema::connection('pgsql_query')->create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->uuid('category_id');
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for better query performance
            $table->index('sku');
            $table->index('name');
            $table->index('price');
            $table->index('stock');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('products');
        Schema::connection('pgsql_query')->dropIfExists('products');
    }
};
