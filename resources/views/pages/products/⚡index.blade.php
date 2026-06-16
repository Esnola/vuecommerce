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
      $products = Product::query()
        ->whereNull('deleted_at')
        ->with('creator')
        ->with('updater')
        ->with('deleter');

      if (auth()->check()) {
        $products->withExists([
          'favoritedByUsers as is_favorite' => fn($query) => $query->whereKey(auth()->id()),
        ]);
        $products->withSum([
          'cartItems as cart_quantity' => fn($query) => $query->whereBelongsTo(auth()->user()),
        ], 'quantity');
      }

      return $products->paginate(20);
    }
  };
?>

<div class="bg-white dark:bg-gray-800">
  <div class="mx-auto mt-4 max-w-7xl sm:px-6 lg:px-8">
    <h2 class="sr-only">{{$title}}</h2>

    <div class="gap-2 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
      @forelse($this->products() as $product)
        @php
          $thumbnail = $product->mainImage();
        @endphp

        <div class="relative border border-gray-300 dark:border-gray-200/30 rounded-t-lg">
          <x-favorite-button
                  :product="$product"
                  :favorite="(bool) ($product->is_favorite ?? false)"
          />

          @if($thumbnail)
            <img src="{{ $thumbnail }}" alt="{{ $product->title }}"
                 class="aspect-square rounded-t-lg bg-gray-200/60 dark:bg-gray-900/30 object-cover opacity-75 group-hover:opacity-100"/>
          @else
            <div class="aspect-square rounded-lg bg-gray-100"></div>
          @endif

          <div class="pt-10 pb-4 text-center">
            <a class="font-medium text-gray-800 dark:text-gray-300/70"
               href="{{ route('products.show', $product->slug) }}">
              {{ $product->title }}
            </a>

            <p class="mt-4 text-base font-medium text-gray-800 dark:text-gray-300/70">
              {{ $product->formatPrice() }}
            </p>
            <flux:badge rounded color="sky"
                        class="border bg-sky-100/40! text-[10px]">{{ $product->formatDiscount() }}</flux:badge>
          </div>
          <div class="flex items-center justify-between gap-3 px-3 pb-3">
            <flux:badge rounded color="neutral"
                        class="{{ $product->availability_status->getClass() }} text-[10px] border">{{ $product->availability_status->label() }}</flux:badge>

            <form method="POST" action="{{ route('cart.store', $product) }}" class="relative">
              @csrf
              <flux:button
                      type="submit"
                      size="sm"
                      icon="shopping-cart"
                      class="cursor-pointer"
                      data-cart-button
                      data-product-id="{{ $product->id }}"
                      data-added-label="{{ __('Added to cart') }}"
                      :disabled="$product->stock < 1"
              >
                {{ __('Add') }}
              </flux:button>
              <span
                      data-cart-feedback
                      role="status"
                      aria-live="polite"
                      class="pointer-events-none absolute right-0 top-full z-20 mt-2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition duration-150 dark:bg-white dark:text-gray-900"
              ></span>
              <p
                      data-cart-status
                      data-cart-status-label="{{ __('In cart: :quantity') }}"
                      data-product-id="{{ $product->id }}"
                      class="mt-1 text-right text-[11px] font-medium text-sky-700 dark:text-sky-300 {{ (int) ($product->cart_quantity ?? 0) > 0 ? '' : 'hidden' }}"
              >
                {{ __('In cart: :quantity', ['quantity' => (int) ($product->cart_quantity ?? 0)]) }}
              </p>
            </form>
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
