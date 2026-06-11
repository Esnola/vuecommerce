<?php
  
  use App\Models\User;
  use Illuminate\Database\Migrations\Migration;
  use Illuminate\Database\Schema\Blueprint;
  use Illuminate\Support\Facades\Schema;
  
  return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('title', 2000);
        $table->string('slug', 2000);
        $table->string('image', 2000)->nullable();
        $table->string('image_mine')->nullable();
        $table->integer('image_size')->nullable();
        $table->longText('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->integer('quantity')->nullable();
        $table->boolean('published')->default(false);
        $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
        $table->softDeletes();
        $table->foreignId('deleted_by')->nullable()->constrained('users')->cascadeOnDelete();
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
