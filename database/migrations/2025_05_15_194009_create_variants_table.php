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
        Schema::create('variants', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('variant_name', 191);
            $table->string('sku', 100)->unique();
            $table->float('regular_price')->nullable();
            $table->float('sale_price')->nullable();
            $table->string('barcode')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->string('weight')->nullable();
            $table->text('image_path')->nullable();
            $table->boolean('status')->default(true);
            $table->date('expire_date')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variants');
    }
};
