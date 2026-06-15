<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(auth()->user()->canAccessAccount(), 403);
    }

    public function favorites(): LengthAwarePaginator
    {
        return auth()->user()
            ->favoriteProducts()
            ->with('images')
            ->latest('favorites.created_at')
            ->paginate(12);
    }

    public function removeFavorite(int $productId): void
    {
        abort_unless(auth()->user()->canAccessAccount(), 403);

        auth()->user()
            ->favoriteProducts()
            ->detach($productId);
    }
};
?>

@php
  $favorites = $this->favorites();
@endphp

<main class="min-h-screen bg-gray-50 py-12 dark:bg-gray-800">
  <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2">
      <flux:heading size="xl">{{ __('My favorites') }}</flux:heading>
      <flux:text>{{ __('Manage the products you have saved for later.') }}</flux:text>
    </div>

    @if ($favorites->isNotEmpty())
      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($favorites as $product)
          @php
            $thumbnail = $product->images->firstWhere('position', 1)?->url ?? '';
          @endphp

          <flux:card wire:key="favorite-{{ $product->id }}" class="flex flex-col gap-5">
            <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="group">
              @if ($thumbnail)
                <img
                  src="{{ $thumbnail }}"
                  alt="{{ $product->title }}"
                  class="aspect-square w-full rounded-xl bg-gray-100 object-cover transition group-hover:opacity-90 dark:bg-white/5"
                />
              @else
                <div class="flex aspect-square items-center justify-center rounded-xl bg-gray-100 dark:bg-white/5">
                  <flux:icon.photo class="size-10 text-gray-400" />
                </div>
              @endif
            </a>

            <div class="flex flex-1 flex-col gap-4">
              <div class="flex flex-col gap-1">
                <a
                  href="{{ route('products.show', $product->slug) }}"
                  wire:navigate
                  class="font-semibold text-gray-900 hover:text-sky-600 dark:text-white dark:hover:text-sky-400"
                >
                  {{ $product->title }}
                </a>
                <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $product->formatPrice() }}</p>
              </div>

              <div class="mt-auto flex items-center justify-between gap-3">
                <flux:button :href="route('products.show', $product->slug)" wire:navigate icon="eye">
                  {{ __('View product') }}
                </flux:button>

                <flux:button
                  type="button"
                  variant="danger"
                  icon="trash"
                  wire:click="removeFavorite({{ $product->id }})"
                  wire:loading.attr="disabled"
                  wire:target="removeFavorite({{ $product->id }})"
                >
                  {{ __('Remove') }}
                </flux:button>
              </div>
            </div>
          </flux:card>
        @endforeach
      </div>

      <div>
        {{ $favorites->links() }}
      </div>
    @else
      <flux:callout icon="heart">
        <flux:callout.heading>{{ __('You have no favorite products yet.') }}</flux:callout.heading>
        <flux:callout.text>{{ __('Save products from the catalog and they will appear here.') }}</flux:callout.text>
        <x-slot:actions>
          <flux:button :href="route('products.index')" wire:navigate>
            {{ __('Browse products') }}
          </flux:button>
        </x-slot:actions>
      </flux:callout>
    @endif
  </div>
</main>
