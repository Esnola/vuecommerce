<?php
  
  namespace App\Models;
  
  use App\Enums\ProductStatusEnum;
  use Database\Factories\ProductFactory;
  use Illuminate\Database\Eloquent\Factories\HasFactory;
  use Illuminate\Database\Eloquent\Model;
  use Illuminate\Database\Eloquent\Relations\BelongsTo;
  use Illuminate\Database\Eloquent\Relations\HasMany;
  
  class Product extends Model
  {
    /** @use HasFactory<ProductFactory> */
    use HasFactory;
    
    protected $guarded = [];
    protected $casts = [
      'status' => ProductStatusEnum::class,
      'price' => 'decimal:2',
      'discount_percentage' => 'decimal:2',
      'rating' => 'decimal:2',
      'images' => 'array',
      'tags' => 'array',
      'dimensions' => 'array',
      'reviews' => 'array',
      'meta' => 'array',
    ];
    
    public function categories(): HasMany
    {
      return $this->hasMany(Category::class, 'category_id');
    }
    
    public function productImages()
    {
      return $this->hasMany(ProductImage::class, 'product_id');
    }
    
    public function creator(): BelongsTo
    {
      return $this->belongsTo(User::class, 'created_by');
    }
    
    public function updater(): BelongsTo
    {
      return $this->belongsTo(User::class, 'updated_by');
    }
    
    public function deleter(): BelongsTo
    {
      return $this->belongsTo(User::class, 'deleted_by');
    }
    
  }
