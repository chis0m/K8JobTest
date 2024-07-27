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
            $table->id();
            $table->string('region_name')->nullable();
            $table->string('country_name')->nullable();
            $table->string('registration_name')->nullable();
            $table->string('number')->nullable();
            $table->string('status_number')->nullable();
            $table->date('start_date')->nullable();
            $table->timestamp('submission_date')->nullable();
            $table->timestamp('decision_date')->nullable();
            $table->string('type_name')->nullable();
            $table->string('name')->nullable();
            $table->string('rimsys_number')->nullable();
            $table->string('version')->nullable();
            $table->string('model')->nullable();
            $table->string('part')->nullable();
            $table->string('catalog_number')->nullable();
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
