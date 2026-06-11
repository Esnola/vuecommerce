<?php
  
  use App\Models\User;
  use Illuminate\Database\Migrations\Migration;
  use Illuminate\Database\Schema\Blueprint;
  use Illuminate\Support\Facades\Schema;
  
  return new class extends Migration
  {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
      Schema::create('payments', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
        $table->string('session_id', 255)->nullable();
        $table->decimal('amount', 10, 2);
        $table->string('status', 45);
        $table->string('type', 45);
        $table->timestamps();
        $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
        $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
      });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
      Schema::dropIfExists('payments');
    }
  };
