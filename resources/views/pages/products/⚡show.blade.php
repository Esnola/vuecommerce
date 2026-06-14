<?php
  
  use App\Models\Product;
  use Livewire\Component;
  
  new class extends Component {
    public Product $product;
    
    public function mount(string $slug): void
    {
      $this->product = Product::query()
        ->where('slug', $slug)
        ->with(['creator', 'updater', 'deleter', 'images', 'categories', 'tags'])
        ->firstOrFail();
    }
    
    public function render(): mixed
    {
      return view('pages.products.⚡show');
    }
  };
?>

<div class="bg-white dark:bg-gray-900">
  <div class="mx-auto grid max-w-2xl grid-cols-1 items-center gap-x-8 gap-y-16 px-4 py-24 sm:px-6 sm:py-32 lg:max-w-7xl lg:grid-cols-2 lg:px-8">
    <div>
      <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $product->title }}</h2>
      <p class="mt-4 text-gray-500">{{$product->description}}</p>

      <dl class="mt-16 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 sm:gap-y-16 lg:gap-x-8">
        <div class="border-t border-gray-200 pt-4">
          <dt class="font-medium text-gray-900">{{ __('Brand') }}</dt>
          <dd class="mt-2 text-sm text-gray-500">{{ $product->brand }}</dd>
        </div>
        <div class="border-t border-gray-200 pt-4">
          <dt class="font-medium text-gray-900">Material</dt>
          <dd class="mt-2 text-sm text-gray-500">Solid walnut base with rare earth magnets and powder coated steel card
            cover
          </dd>
        </div>
        <div class="border-t border-gray-200 pt-4">
          <dt class="font-medium text-gray-900">Dimensions</dt>
          <dd class="mt-2 text-sm text-gray-500">
            {{ $product->dimensions['width'] }}<span class="text-[10px]"> cm</span>
            x {{ $product->dimensions['height'] }}
            <span class="text-[10px]"> cm</span> x {{ $product->dimensions['depth'] }}
            <span class="text-[10px]"> cm</span>
        </div>
        <div class="border-t border-gray-200 pt-4">
          <dt class="font-medium text-gray-900">Finish</dt>
          <dd class="mt-2 text-sm text-gray-500">Hand sanded and finished with natural oil</dd>
        </div>
        <div class="border-t border-gray-200 pt-4">
          <dt class="font-medium text-gray-900">Includes</dt>
          <dd class="mt-2 text-sm text-gray-500">Wood card tray and 3 refill packs</dd>
        </div>
        <div class="border-t border-gray-200 pt-4">
          <dt class="font-medium text-gray-900">Considerations</dt>
          <dd class="mt-2 text-sm text-gray-500">Made from natural materials. Grain and color vary with each item.</dd>
        </div>
        <div class="border-t border-gray-200 pt-4">
          <dt class="font-medium text-gray-900">{{ __('Categories') }}</dt>
          @foreach ($product->categories as $cat)
            <dd class="mt-2 text-sm text-gray-500"> {{ $cat->name}} </dd>
          @endforeach
        </div>
        <div class="border-t border-gray-200 pt-4">
          <dt class="font-medium text-gray-900">{{ __('Tags') }}</dt>
          @foreach ($product->tags as $tag)
            <dd class="mt-2 text-sm text-gray-500"> {{ $tag->name}} </dd>
          @endforeach
        </div>
      </dl>
    </div>
    <div class="flex flex-col gap-4 sm:gap-6 lg:gap-8">
      <img id="mainImage" src="{{$product->mainImage()}}" alt="{{$product->title}}"
           class="aspect-square rounded-lg bg-gray-100 object-cover shadow-xl"/>
      <div class="flex items-center jusitfy-center gap-2">
        @foreach ($product->images->skip(1) as $image)
          <img data-thumbs src="{{ $image->url }}" alt="{{$product->title}}"
               class="rounded-lg bg-gray-100 object-cover aspect-square w-24 h-auto"/>
        @endforeach
      </div>
    </div>
  </div>
</div>


@vite('resources/js/show-images.js')
