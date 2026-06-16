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
    }
    
    public function render(): mixed
    {
      return view('pages.products.⚡show');
    }
  };
?>

<div class="bg-white dark:bg-gray-800">
  <div class="mx-auto grid max-w-2xl grid-cols-1 items-center gap-x-8 gap-y-16 px-4 py-24 sm:px-6 sm:py-32 lg:max-w-7xl lg:grid-cols-2 lg:px-8">
    <div>
      <div class="flex items-start justify-between gap-4">
        <h2 class="text-3xl font-bold tracking-tight text-gray-800 dark:text-white sm:text-4xl">{{ $product->title }}</h2>
        <x-favorite-button :product="$product" :favorite="(bool) $product->is_favorite"/>
      </div>
      <p class="mt-4 text-gray-500 dark:text-gray-400">{{$product->description}}</p>

      <dl class="mt-16 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 sm:gap-y-16 lg:gap-x-8">
        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <dt class="font-medium text-gray-800 dark:text-white">{{ __('Brand') }}</dt>
          <dd class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $product->brand }}</dd>
        </div>
        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <dt class="font-medium text-gray-800 dark:text-white">Material</dt>
          <dd class="mt-2 text-sm text-gray-500 dark:text-gray-400">Solid walnut base with rare earth magnets and powder
            coated steel card
            cover
          </dd>
        </div>
        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <dt class="font-medium text-gray-800 dark:text-white">Dimensions</dt>
          <dd class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            {{ $product->dimensions['width'] }}<span class="text-[10px]"> cm</span>
            x {{ $product->dimensions['height'] }}
            <span class="text-[10px]"> cm</span> x {{ $product->dimensions['depth'] }}
            <span class="text-[10px]"> cm</span>
        </div>
        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <dt class="font-medium text-gray-800 dark:text-white">Finish</dt>
          <dd class="mt-2 text-sm text-gray-500 dark:text-gray-400">Hand sanded and finished with natural oil</dd>
        </div>
        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <dt class="font-medium text-gray-800 dark:text-white">Includes</dt>
          <dd class="mt-2 text-sm text-gray-500 dark:text-gray-400">Wood card tray and 3 refill packs</dd>
        </div>
        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <dt class="font-medium text-gray-800 dark:text-white">Considerations</dt>
          <dd class="mt-2 text-sm text-gray-500 dark:text-gray-400">Made from natural materials. Grain and color vary
            with each item.
          </dd>
        </div>
        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <dt class="font-medium text-gray-800 dark:text-white">{{ __('Categories') }}</dt>
          @foreach ($product->categories as $cat)
            <dd class="mt-2 text-sm text-gray-500 dark:text-gray-400"> {{ $cat->name}} </dd>
          @endforeach
        </div>
        <div class="border-t border-gray-200 dark:border-white/10 pt-4">
          <dt class="font-medium text-gray-800 dark:text-white">{{ __('Tags') }}</dt>
          @foreach ($product->tags as $tag)
            <dd class="mt-2 text-sm text-gray-500 dark:text-gray-400"> {{ $tag->name}} </dd>
          @endforeach
        </div>
      </dl>
    </div>
    <div class="flex flex-col gap-4 sm:gap-6 lg:gap-8">
      <div id="mainImageZoom" class="relative aspect-square cursor-zoom-in overflow-hidden rounded-lg shadow-xl">
        <img id="mainImage" src="{{$product->mainImage()}}" alt="{{$product->title}}"
             class="aspect-square h-full w-full rounded-lg bg-gray-100 object-cover transition-transform duration-100 ease-out"/>
      </div>
      <div class="flex items-center jusitfy-center gap-2">
        @foreach ($product->images->skip(1) as $image)
          <img data-thumbs src="{{ $image->url }}" alt="{{$product->title}}"
               class="rounded-lg bg-gray-100 object-cover aspect-square w-24 h-auto"/>
        @endforeach
      </div>
    </div>
  </div>

  <section id="comments" class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
    <div class="border-t border-gray-200 pt-10 dark:border-white/10">
      <x-comments :product="$product"/>
    </div>
  </section>
</div>
  @vite('resources/js/show-images.js')
