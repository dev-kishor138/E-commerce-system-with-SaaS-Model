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
        Schema::create('user_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('user_id')->unique();
            $table->string('full_name', 100)->index();
            $table->string('secondary_email', 255)->nullable()->index();
            $table->longText('address')->nullable();
            $table->string('phone_number', 20)->nullable()->index();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('police_station', 100)->nullable();
            $table->string('image')->nullable();
            $table->string('country', 100)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('preferred_language', 5)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
