<div class="bg-white">
  <div class="mx-auto max-w-7xl overflow-hidden sm:px-6 lg:px-8">
    <h2 class="sr-only">{{$title}}</h2>

    <div class="-mx-px grid grid-cols-2 border-l border-gray-200 sm:mx-0 md:grid-cols-3 lg:grid-cols-4">
      @forelse($products as $product)
        @php
          $imageUrl = $product->productImages->first()?->url;
        @endphp

        <div class="group relative border-r border-b border-gray-200 p-4 sm:p-6">
          @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $product->title }}"
                 class="aspect-square rounded-lg bg-gray-200 object-cover group-hover:opacity-75"/>
          @else
            <div class="aspect-square rounded-lg bg-gray-100"></div>
          @endif

          <div class="pt-10 pb-4 text-center">
            <h3 class="text-sm font-medium text-gray-900">
              <a href="{{ route('products.show', $product) }}">
                <span aria-hidden="true" class="absolute inset-0"></span>
                {{ $product->title }}
              </a>
            </h3>

            <p class="mt-4 text-base font-medium text-gray-900">
              {{ Number::currency($product->price) }}
            </p>
          </div>
        </div>
      @empty
        <p class="col-span-full p-6 text-center text-sm text-gray-500">
          {{ __('No products found.') }}
        </p>
      @endforelse
    </div>

    <div class="px-4 py-6">
      {{ $products->links() }}
    </div>
  </div>
</div>
