<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
  /** @use HasFactory<\Database\Factories\CategoryFactory> */
  use HasFactory;

  protected $guarded = [];

  public function products(): BelongsToMany
  {
    return $this->belongsToMany(Product::class, 'product_categories');
  }

  public function tags(): HasMany
  {
    return $this->hasMany(Tag::class);
  }
}
