<?php
  
  use App\Enums\ProductStatusEnum;
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
        $table->string('slug')->unique();
        $table->string('title');
        $table->text('description')->nullable();
        $table->boolean('on_offer')->default(0);
        $table->decimal('price', 10, 2);
        $table->decimal('discount_percentage', 5, 2)->default(0);
        $table->decimal('rating', 3, 2)->default(0);
        $table->unsignedInteger('stock')->default(0);
        $table->string('brand')->nullable();
        $table->string('sku')->unique();
        $table->decimal('weight', 8, 2)->nullable();
        $table->json('dimensions')->nullable();
        $table->string('warranty_information')->nullable();
        $table->string('shipping_information')->nullable();
        $table->string('availability_status')->default(ProductStatusEnum::IN_STOCK->value)->index();
        $table->json('reviews')->nullable();
        $table->string('return_policy')->nullable();
        $table->unsignedInteger('minimum_order_quantity')->default(1);
        $table->json('meta')->nullable();
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
