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
        Schema::create('properties', function (Blueprint $table) {
            $status = ['available', 'rented', 'sold'];
            $property_type = ['house', 'apartment', 'commercial', 'land'];

            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            $table->decimal('area', 10, 2);

            // TODO 1: create table StatusProperty and table PropertyType
            // Enum for status and property type
            $table->enum('status', $status)->default('available');
            $table->enum('property_type', $property_type)->default('house');

            // Address fields
            $table->string('address', 255); // Full address
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('zip', 20); // Postal code

            // Location fields (latitude and longitude)
            $table->decimal('latitude', 10, 8)->nullable();  // Precision for coordinates
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
