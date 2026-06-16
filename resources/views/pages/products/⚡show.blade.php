<?php
  
  use App\Models\Product;
  use Livewire\Component;
  
  new class extends Component {
    public Product $product;
    
    public function mount(string $slug): void
    {
      $this->product = Product::query()
        ->where('slug', $slug)
        ->with(['creator', 'updater', 'deleter', 'images', 'categories', 'tags', 'reviews.user'])
        ->firstOrFail();
      
      $this->product->setAttribute(
        'is_favorite',
        auth()->check() && $this->product->favoritedByUsers()->whereKey(auth()->id())->exists(),
      );
      $this->product->setAttribute(
        'cart_quantity',
        auth()->check()
          ? (int)$this->product->cartItems()->whereBelongsTo(auth()->user())->sum('quantity')
          : 0,
      );
    }
    
    public function render(): mixed
    {
      return view('pages.products.⚡show');
    }
  };
?>

@php
  $images = $product->images;
  $mainImage = $images->firstWhere('position', 1)?->url ?? $product->mainImage();
  $reviewCount = $product->reviews->count();
  $averageRating = $reviewCount > 0 ? round((float) $product->reviews->avg('rating'), 1) : null;
  $minimumQuantity = 1;
  $isPurchasable = $product->stock >= 1;
  $dimensions = is_array($product->dimensions) ? $product->dimensions : [];
  $dimensionText = collect(['width', 'height', 'depth'])
      ->map(fn (string $key) => isset($dimensions[$key]) ? $dimensions[$key].' cm' : null)
      ->filter()
      ->implode(' x ');
  $hasOffer = (bool) $product->on_offer || (float) $product->discount_percentage > 0;
@endphp

<div class="bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-white">
  <main class="mx-auto flex max-w-7xl flex-col gap-12 px-4 py-10 sm:px-6 lg:px-8">
    <section class="grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(24rem,0.95fr)] lg:items-start">
      <div class="flex relative flex-col gap-4">

        {{-- <button type="button"
                 data-favorite-button
                 data-product-id="{{ $product->getKey() }}"
                 data-toggle-url="{{ route('favorites.toggle', $product) }}"
                 data-is-favorite="{{ $product->is_favorite ? 'true' : 'false' }}"
                 --}}{{--             data-add-label="{{ __('Add to favorites') }}"
                              data-remove-label="{{ __('Remove from favorites') }}"--}}{{--
                 data-added-label="{{ __('Added to favorites') }}"
                 data-removed-label="{{ __('Removed from favorites') }}"
                 aria-pressed="{{ $product->is_favorite ? 'true' : 'false' }}"
                 class="group z-10 absolute top-1 left-2 inline-flex min-h-11 w-fit shrink-0 cursor-pointer items-center justify-center gap-2 rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-600 transition hover:border-rose-300 hover:text-rose-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 disabled:cursor-wait disabled:opacity-60 dark:border-white/10 dark:bg-gray-950 dark:text-gray-300 dark:hover:border-rose-400 dark:hover:text-rose-400"
                 --}}{{--                aria-label="{{ $product->is_favorite ? __('Remove from favorites') : __('Add to favorites') }}"--}}{{--
         >
           <svg data-favorite-icon
                viewBox="0 0 24 24"
                fill="{{ $product->is_favorite ? 'currentColor' : 'none' }}"
                stroke="currentColor"
                stroke-width="1.5"
                aria-hidden="true"
                class="size-5 shrink-0 transition-colors duration-100 group-hover:fill-rose-600 group-hover:text-rose-600"
           >
             <path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"
                   stroke-linecap="round"
                   stroke-linejoin="round"
             />
           </svg>
           <span data-favorite-feedback
                 role="status"
                 aria-live="polite"
                 class="pointer-events-none absolute right-0 top-full z-20 mt-2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition duration-150 dark:bg-white dark:text-gray-900"
           ></span>
         </button>--}}
        <x-favorite-button :product="$product" :favorite="$product->is_favorite"/>
        <div id="mainImageZoom"
             class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:bg-white/5 hover:cursor-zoom-in">
          @if ($mainImage)
            <img id="mainImage"
                 src="{{ $mainImage }}"
                 alt="{{ $product->title }}"
                 class="aspect-square w-full bg-gray-100 object-cover dark:bg-white/5"
            />
          @else
            <div class="flex aspect-square w-full items-center justify-center bg-gray-100 dark:bg-white/5">
              <flux:icon.photo class="size-14 text-gray-400"/>
            </div>
          @endif
        </div>

        @if ($images->count() > 1)
          <div class="grid grid-cols-4 gap-3 sm:grid-cols-6">
            @foreach ($images as $image)
              <button type="button"
                      class="overflow-hidden rounded-md border border-gray-200 bg-white transition hover:border-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-white/10 dark:bg-white/5"
              >
                <img data-thumbs
                     src="{{ $image->url }}"
                     alt="{{ $product->title }} {{ $image->position }}"
                     class="aspect-square w-full object-cover"
                />
              </button>
            @endforeach
          </div>
        @endif
      </div>

      <div class="flex flex-col gap-7">
        <div class="flex flex-col gap-4">
          <div class="flex flex-wrap items-center gap-2">
            <flux:badge rounded color="neutral" class="{{ $product->availability_status->getClass() }} border">
              {{ $product->availability_status->label() }}
            </flux:badge>

            @if ($hasOffer)
              <flux:badge rounded color="sky" class="border bg-sky-100/40!">
                {{ __('Discount') }} {{ $product->formatDiscount() }}
              </flux:badge>
            @endif
          </div>

          <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
              <p class="text-sm font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">
                {{ $product->brand ?: __('Unbranded') }}
              </p>
              <h1 class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 dark:text-white sm:text-4xl">
                {{ $product->title }}
              </h1>
            </div>
          </div>

          <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
              {{ $product->formatPrice() }}
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400">
              {{ __('SKU') }}: {{ $product->sku }}
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
            @if ($averageRating !== null)
              <span class="inline-flex items-center gap-1">
                <flux:icon.star class="size-4 text-amber-500"/>
                {{ $averageRating }} / 5
              </span>
            @endif
            <a href="#comments" class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
              {{ trans_choice(':count customer review|:count customer reviews', $reviewCount, ['count' => $reviewCount]) }}
            </a>
          </div>
        </div>

        <div class="prose prose-sm max-w-none text-gray-700 dark:prose-invert dark:text-gray-300">
          <p>{{ $product->description ?: __('No description available.') }}</p>
        </div>

        <form method="POST" action="{{ route('cart.store', $product) }}"
              class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
          @csrf

          <div class="grid gap-4 sm:grid-cols-[9rem_1fr] sm:items-end">
            <div>
              <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ __('Quantity') }}
              </label>
              <input
                      id="quantity"
                      name="quantity"
                      type="number"
                      min="1"
                      max="{{ max(1, $product->stock) }}"
                      value="1"
                      class="mt-2 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/40 focus:outline-none dark:border-white/10 dark:bg-gray-950 dark:text-white"
              />
            </div>

            <button
                    type="submit"
                    @disabled(! $isPurchasable)
                    data-cart-button
                    data-product-id="{{ $product->id }}"
                    class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-gray-400 dark:focus-visible:ring-offset-gray-900"
            >
              {{ __('Add to cart') }}
            </button>

          </div>

          <div class="mt-4 flex flex-col gap-2 text-sm text-gray-600 dark:text-gray-300 sm:flex-row sm:items-center sm:justify-between">
            <p>
              {{ __('Available stock') }}:
              <span class="font-semibold text-gray-900 dark:text-white">{{ $product->stock }}</span>
            </p>
          </div>
        </form>

        <div
                data-cart-status
                data-cart-status-label="{{ __('In cart: :quantity') }}"
                data-product-id="{{ $product->id }}"
                class="rounded-md bg-sky-50 px-3 py-3 text-sm font-medium text-sky-800 dark:bg-sky-400/10 dark:text-sky-200 {{ (int) $product->cart_quantity > 0 ? '' : 'hidden' }}"
        >
          <form
                  method="POST"
                  action="{{ route('cart.update', $product) }}"
                  data-cart-quantity-form
                  data-product-id="{{ $product->id }}"
                  class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
          >
            @csrf
            @method('PATCH')

            <span>
                {{ __('In cart: :quantity', ['quantity' => '']) }}
                <span data-cart-status-quantity>{{ (int) $product->cart_quantity }}</span>
              </span>

            <div class="flex flex-wrap items-center gap-2">
              <div class="inline-flex w-fit items-center overflow-hidden rounded-md border border-sky-200 bg-white text-gray-900 shadow-sm dark:border-sky-300/20 dark:bg-gray-950 dark:text-white">
                <button
                        type="button"
                        data-cart-quantity-step="-1"
                        class="flex size-9 cursor-pointer items-center justify-center text-base font-semibold transition hover:bg-sky-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-white/10"
                        aria-label="{{ __('Decrease quantity') }}"
                >
                  -
                </button>
                <input
                        type="number"
                        name="quantity"
                        min="1"
                        max="{{ max(1, $product->stock) }}"
                        value="{{ max(1, (int) $product->cart_quantity) }}"
                        data-cart-quantity-input
                        class="h-9 w-16 border-x border-sky-200 bg-transparent text-center text-sm font-semibold focus:outline-none focus:ring-0 dark:border-sky-300/20"
                />
                <button
                        type="button"
                        data-cart-quantity-step="1"
                        class="flex size-9 cursor-pointer items-center justify-center text-base font-semibold transition hover:bg-sky-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-white/10"
                        aria-label="{{ __('Increase quantity') }}"
                >
                  +
                </button>
              </div>

              <button
                      type="button"
                      data-cart-remove-button
                      data-remove-url="{{ route('cart.destroy', $product) }}"
                      class="inline-flex min-h-9 cursor-pointer items-center justify-center gap-1.5 rounded-md border border-red-200 px-3 text-xs font-semibold text-red-700 transition hover:text-white hover:border-red-300 hover:bg-red-500 disabled:cursor-wait disabled:opacity-60 bg-red-300/10 dark:border-red-400/30 dark:text-red-500 dark:hover:bg-red-300/10 dark:hover:text-red-400 dark:hover:border-red-400"
              >
                <flux:icon.trash class="size-4"/>
                {{ __('Remove') }}
              </button>
            </div>
          </form>
        </div>

        <dl class="grid gap-3 sm:grid-cols-2">
          <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Brand') }}</dt>
            <dd class="mt-1 font-medium text-gray-900 dark:text-white">{{ $product->brand ?: __('Unbranded') }}</dd>
          </div>
          <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Availability') }}</dt>
            <dd class="mt-1 font-medium text-gray-900 dark:text-white">{{ $product->availability_status->label() }}</dd>
          </div>
          @if ($product->weight !== null)
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
              <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Weight') }}</dt>
              <dd class="mt-1 font-medium text-gray-900 dark:text-white">{{ $product->weight }} kg</dd>
            </div>
          @endif
          @if ($dimensionText !== '')
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
              <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Dimensions') }}</dt>
              <dd class="mt-1 font-medium text-gray-900 dark:text-white">{{ $dimensionText }}</dd>
            </div>
          @endif
        </dl>
      </div>
    </section>

    <section class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.42fr)]">
      <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-white/10 dark:bg-white/5">
        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">{{ __('Delivery and returns') }}</h2>
        <dl class="mt-5 divide-y divide-gray-200 dark:divide-white/10">
          <div class="grid gap-1 py-4 sm:grid-cols-3">
            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Shipping information') }}</dt>
            <dd class="text-sm text-gray-900 dark:text-white sm:col-span-2">{{ $product->shipping_information ?: __('Not specified') }}</dd>
          </div>
          <div class="grid gap-1 py-4 sm:grid-cols-3">
            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Warranty information') }}</dt>
            <dd class="text-sm text-gray-900 dark:text-white sm:col-span-2">{{ $product->warranty_information ?: __('Not specified') }}</dd>
          </div>
          <div class="grid gap-1 py-4 sm:grid-cols-3">
            <dt class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Return policy') }}</dt>
            <dd class="text-sm text-gray-900 dark:text-white sm:col-span-2">{{ $product->return_policy ?: __('Not specified') }}</dd>
          </div>
        </dl>
      </div>

      <aside class="flex flex-col gap-5">
        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
          <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Categories') }}</h2>
          <div class="mt-3 flex flex-wrap gap-2">
            @forelse ($product->categories as $category)
              <flux:badge rounded color="neutral">{{ $category->name }}</flux:badge>
            @empty
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Not specified') }}</p>
            @endforelse
          </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-5 dark:border-white/10 dark:bg-white/5">
          <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Tags') }}</h2>
          <div class="mt-3 flex flex-wrap gap-2">
            @forelse ($product->tags as $tag)
              <flux:badge rounded color="sky">{{ $tag->name }}</flux:badge>
            @empty
              <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Not specified') }}</p>
            @endforelse
          </div>
        </div>
      </aside>
    </section>

    <section id="comments">
      <x-comments :product="$product"/>
    </section>
  </main>
</div>

@vite('resources/js/show-images.js')
