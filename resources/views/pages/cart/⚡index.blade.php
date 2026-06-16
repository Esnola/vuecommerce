<?php

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

new class extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->canAccessAccount(), 403);
    }

    public function cartItems(): Collection
    {
        return auth()->user()
            ->cartItems()
            ->with('product.images')
            ->latest()
            ->get();
    }

    public function updateQuantity(int $cartItemId, int $quantity): void
    {
        abort_unless(auth()->user()->canAccessAccount(), 403);

        $cartItem = $this->findCartItem($cartItemId);
        $cartItem->update([
            'quantity' => min(max(1, $quantity), max(1, $cartItem->product->stock)),
        ]);

        $this->dispatch('cart-updated', cart_count: $this->cartCount());
    }

    public function removeItem(int $cartItemId): void
    {
        abort_unless(auth()->user()->canAccessAccount(), 403);

        $this->findCartItem($cartItemId)->delete();

        $this->dispatch('cart-updated', cart_count: $this->cartCount());
    }

    public function clearCart(): void
    {
        abort_unless(auth()->user()->canAccessAccount(), 403);

        auth()->user()->cartItems()->delete();

        $this->dispatch('cart-updated', cart_count: 0);
    }

    private function findCartItem(int $cartItemId): CartItem
    {
        return auth()->user()
            ->cartItems()
            ->with('product')
            ->findOrFail($cartItemId);
    }

    private function cartCount(): int
    {
        return (int) auth()->user()->cartItems()->sum('quantity');
    }
};
?>

@php
  $cartItems = $this->cartItems();
  $cartTotal = $cartItems->sum(fn (CartItem $cartItem): float => (float) $cartItem->product->price * $cartItem->quantity);
@endphp

<main class="min-h-screen bg-gray-50 py-12 dark:bg-gray-800">
  <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
      <div class="flex flex-col gap-2">
        <flux:heading size="xl">{{ __('Shopping cart') }}</flux:heading>
        <flux:text>{{ __('Review the products you have added before checkout.') }}</flux:text>
      </div>

      @if ($cartItems->isNotEmpty())
        <flux:button wire:click="clearCart" variant="ghost" icon="trash" class="cursor-pointer">
          {{ __('Clear cart') }}
        </flux:button>
      @endif
    </div>

    @if (session('cart-status'))
      <flux:callout icon="check-circle" color="green">
        <flux:callout.text>{{ session('cart-status') }}</flux:callout.text>
      </flux:callout>
    @endif

    @if ($cartItems->isNotEmpty())
      <div class="grid gap-8 lg:grid-cols-[1fr_22rem]">
        <div class="flex flex-col gap-5">
          @foreach ($cartItems as $cartItem)
            @php
              $product = $cartItem->product;
              $thumbnail = $product->images->firstWhere('position', 1)?->url ?? '';
              $lineTotal = (float) $product->price * $cartItem->quantity;
            @endphp

            <flux:card wire:key="cart-item-{{ $cartItem->id }}" class="grid gap-5 sm:grid-cols-[8rem_1fr]">
              <a href="{{ route('products.show', $product->slug) }}" wire:navigate class="group cursor-pointer">
                @if ($thumbnail)
                  <img
                    src="{{ $thumbnail }}"
                    alt="{{ $product->title }}"
                    class="aspect-square w-full rounded-lg bg-gray-100 object-cover transition group-hover:opacity-90 dark:bg-white/5"
                  />
                @else
                  <div class="flex aspect-square items-center justify-center rounded-lg bg-gray-100 dark:bg-white/5">
                    <flux:icon.photo class="size-8 text-gray-400" />
                  </div>
                @endif
              </a>

              <div class="flex flex-col gap-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                  <div class="flex flex-col gap-1">
                    <a
                      href="{{ route('products.show', $product->slug) }}"
                      wire:navigate
                      class="text-base font-semibold text-gray-900 transition hover:text-indigo-600 dark:text-white"
                    >
                      {{ $product->title }}
                    </a>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->formatPrice() }}</p>
                  </div>

                  <p class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ number_format($lineTotal, 2, ',', '.') }} €
                  </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                  <flux:input
                    wire:change="updateQuantity({{ $cartItem->id }}, $event.target.value)"
                    label="{{ __('Quantity') }}"
                    type="number"
                    min="1"
                    max="{{ max(1, $product->stock) }}"
                    value="{{ $cartItem->quantity }}"
                    class="max-w-32"
                  />

                  <flux:button wire:click="removeItem({{ $cartItem->id }})" variant="ghost" icon="x-mark" class="cursor-pointer">
                    {{ __('Remove') }}
                  </flux:button>
                </div>
              </div>
            </flux:card>
          @endforeach
        </div>

        <aside class="flex flex-col gap-5">
          <flux:card class="flex flex-col gap-5">
            <div class="flex items-center justify-between gap-4">
              <flux:heading size="lg">{{ __('Summary') }}</flux:heading>
              <flux:badge color="neutral">{{ trans_choice(':count item|:count items', $cartItems->sum('quantity'), ['count' => $cartItems->sum('quantity')]) }}</flux:badge>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 pt-4 dark:border-white/10">
              <span class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('Subtotal') }}</span>
              <span class="text-lg font-semibold text-gray-900 dark:text-white">{{ number_format($cartTotal, 2, ',', '.') }} €</span>
            </div>

            <flux:button disabled variant="primary" icon="lock-closed" class="w-full justify-center">
              {{ __('Checkout coming soon') }}
            </flux:button>
          </flux:card>
        </aside>
      </div>
    @else
      <flux:callout icon="shopping-bag">
        <flux:callout.heading>{{ __('Your cart is empty.') }}</flux:callout.heading>
        <flux:callout.text>{{ __('Add products from the catalog and they will appear here.') }}</flux:callout.text>
        <x-slot name="actions">
          <flux:button :href="route('products.index')" wire:navigate class="cursor-pointer">
            {{ __('Browse products') }}
          </flux:button>
        </x-slot>
      </flux:callout>
    @endif
  </div>
</main>
