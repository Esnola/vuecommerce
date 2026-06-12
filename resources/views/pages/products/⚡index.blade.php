<?php

  use App\Models\Product;
  use Illuminate\Pagination\LengthAwarePaginator;
  use Livewire\Component;

  new class extends Component {
    public string $title = '';

    public function mount(): void
    {
      $this->title = __('Products');
    }

    public function products(): LengthAwarePaginator
    {
      return Product::query()
        ->with('creator')
        ->with('updater')
        ->with('deleter')
        ->paginate(20);
    }
  };
?>

@include('products.index', ['products' => $this->products(), 'title' => $this->title])
