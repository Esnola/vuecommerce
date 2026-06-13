<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('tags', function (Blueprint $table) {
      $table->id();
      $table->foreignId('category_id')
        ->constrained('categories')
        ->cascadeOnDelete();
      $table->string('name');
      $table->string('slug');
      $table->boolean('active')->default(true);
      $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
      $table->softDeletes();
      $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();

      $table->unique(['category_id', 'name']);
      $table->unique(['category_id', 'slug']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('tags');
  }
};
