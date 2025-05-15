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
        Schema::create('attributes', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->nullable();
            $table->uuid('attributable_id');
            $table->string('attributable_type');
            $table->string('attribute_category', 100)->nullable()->index()->comment('Category like beauty, electronics, grocery');
            $table->string('attribute_name', 100)->index();
            $table->text('attribute_value');
            $table->string('attribute_type', 50)->comment('text, number, boolean, enum, date');
            $table->boolean('is_filterable')->default(false)->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['attributable_id', 'attributable_type']);
            $table->timestamps();
            $table->softDeletes();
            // $table->uuid('id')->primary();
            // $table->uuid('tenant_id')->nullable();
            // $table->uuid('attributable_id');
            // $table->string('attributable_type');
            // $table->string('attribute_name', 100)->index();
            // $table->text('attribute_value');
            // $table->string('attribute_type', 50)->default('text')->comment('text, number, boolean, etc.');
            // $table->boolean('is_filterable')->default(false)->index();
            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            // $table->index(['attributable_id', 'attributable_type']);
            // $table->timestamps();
            // $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
