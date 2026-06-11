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
      Schema::create('customers', function (Blueprint $table) {
        $table->id('user_id');
        $table->string('first_name');
        $table->string('last_name');
        $table->string('phone')->nullable();
        $table->string('status', 45)->nullable();
        $table->timestamps();
        $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
      });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
      Schema::dropIfExists('customers');
    }
  };
