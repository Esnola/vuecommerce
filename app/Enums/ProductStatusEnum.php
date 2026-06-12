<?php
  namespace App\Enums;
  
  enum ProductStatusEnum:string
  {
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case SOLDOUT = 'sold out';
    
    // Opcional: método para obtener etiquetas para el frontend
    public function label(): string
    {
      return match($this) {
        self::PENDING => __('Pending'),
        self::PUBLISHED => __('Published'),
        self::SOLDOUT => __('Sold Out'),
      };
    }
  }
