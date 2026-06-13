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
    Schema::whenTableDoesntHaveIndex(
      'categories',
      ['name'],
      fn (Blueprint $table) => $table->unique('name'),
      'unique'
    );

    Schema::whenTableDoesntHaveIndex(
      'categories',
      ['slug'],
      fn (Blueprint $table) => $table->unique('slug'),
      'unique'
    );
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::whenTableHasIndex(
      'categories',
      ['name'],
      fn (Blueprint $table) => $table->dropUnique(['name']),
      'unique'
    );

    Schema::whenTableHasIndex(
      'categories',
      ['slug'],
      fn (Blueprint $table) => $table->dropUnique(['slug']),
      'unique'
    );
  }
};
