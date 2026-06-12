<?php

namespace App\Models;

use App\Enums\ProductStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;
    protected $guarded=[];
    
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class,'category_id');
    }
    public function productImages(){
        return $this->hasMany(ProductImage::class,'product_id');
    }
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class,'created_by');
    }
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class,'updated_by');
    }
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class,'deleted_by');
    }
    
    
    protected $casts = [
      'created_at' => 'datetime:Y-m-d H:i:s',
      'updated_at' => 'datetime:Y-m-d H:i:s',
      'deleted_at' => 'datetime:Y-m-d H:i:s',
      'price' => 'float',
      'status'=> ProductStatusEnum::class,
    ];
    
}
