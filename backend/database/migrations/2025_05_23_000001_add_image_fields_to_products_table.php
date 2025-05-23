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
        Schema::table('products', function (Blueprint $table) {
            // We already have image_url, but add the other image-related fields
            $table->string('image_filename')->nullable()->after('image_url');
            $table->string('image_path')->nullable()->after('image_filename');
            $table->string('image_alt')->nullable()->after('image_path');
            $table->string('image_thumbnail')->nullable()->after('image_alt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_filename', 'image_path', 'image_alt', 'image_thumbnail']);
        });
    }
}; 