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
        ->whereNull('deleted_at')
        ->with('creator')
        ->with('updater')
        ->with('deleter')
        ->paginate(20);
    }
  };
?>

<div class="bg-white">
  <div class="mx-auto max-w-7xl overflow-hidden sm:px-6 lg:px-8">
    <h2 class="sr-only">{{$title}}</h2>

    <div class="-mx-px grid grid-cols-2 border-l border-gray-200 sm:mx-0 md:grid-cols-3 lg:grid-cols-4">
      @forelse($this->products() as $product)
        @php
          $thumbnail = $product->mainImage();
        @endphp

        <div class="group relative border-r border-b border-gray-200 p-4 sm:p-6">
          @if($thumbnail)
            <img src="{{ $thumbnail }}" alt="{{ $product->title }}"
                 class="aspect-square rounded-lg bg-gray-200 object-cover group-hover:opacity-75"/>
          @else
            <div class="aspect-square rounded-lg bg-gray-100"></div>
          @endif

          <div class="pt-10 pb-4 text-center">
            <h3 class="text-sm font-medium text-gray-900">
              <a href="{{ route('products.show', $product->slug) }}">
                <span aria-hidden="true" class="absolute inset-0"></span>
                {{ $product->title }}
              </a>
            </h3>

            <p class="mt-4 text-base font-medium text-gray-900">
              {{ $product->formatPrice() }}
            </p>
            <flux:badge rounded color="sky"
                        class="border bg-sky-100/40! text-[10px]">{{ $product->formatDiscount() }}</flux:badge>
          </div>
          <div class="flex justify-between items-center">
            <flux:badge rounded color="neutral"
                        class="{{ $product->availability_status->getClass() }} text-[10px] border">{{ $product->availability_status->label() }}</flux:badge>
          </div>
        </div>
      @empty
        <p class="col-span-full p-6 text-center text-sm text-gray-500">
          {{ __('No products found.') }}
        </p>
      @endforelse
    </div>

    <div class="px-4 py-6">
      {{ $this->products()->links() }}
    </div>
  </div>
</div>
