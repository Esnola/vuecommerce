<?php

use App\Enums\OrderStatusEnum;
use App\Models\Order;
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

    public function purchases(): LengthAwarePaginator
    {
        return Order::query()
            ->whereBelongsTo(auth()->user(), 'buyer')
            ->with('items.product:id,title,slug,sku')
            ->withCount('items')
            ->latest()
            ->paginate(10);
    }
};
?>

@php
  $purchases = $this->purchases();
@endphp

<main class="min-h-screen bg-gray-50 py-12 dark:bg-gray-800">
  <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2">
      <flux:heading size="xl">{{ __('Purchase history') }}</flux:heading>
      <flux:text>{{ __('Review the orders and products you have purchased.') }}</flux:text>
    </div>

    <div class="flex flex-col gap-5">
      @forelse ($purchases as $purchase)
        @php
          $badgeColor = match ($purchase->status) {
            OrderStatusEnum::PENDING => 'amber',
            OrderStatusEnum::SHIPPED => 'sky',
            OrderStatusEnum::DELIVERED => 'green',
          };
        @endphp

        <article
          wire:key="purchase-{{ $purchase->id }}"
          class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5"
        >
          <div class="grid gap-5 border-b border-gray-200 p-5 dark:border-white/10 lg:grid-cols-[1fr_auto]">
            <div class="grid gap-4 sm:grid-cols-3">
              <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Order') }}</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">#{{ $purchase->id }}</p>
              </div>

              <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Purchase date') }}</p>
                <time class="mt-1 block text-sm text-gray-700 dark:text-gray-300" datetime="{{ $purchase->created_at->toISOString() }}">
                  {{ $purchase->created_at->translatedFormat('j F Y, H:i') }}
                </time>
              </div>

              <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Total') }}</p>
                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                  {{ number_format((float) $purchase->total_price, 2, ',', '.') }} €
                </p>
              </div>
            </div>

            <div class="flex items-start gap-2 lg:justify-end">
              <flux:badge :color="$badgeColor">{{ $purchase->status->label() }}</flux:badge>
              <flux:badge color="zinc">
                {{ trans_choice(':count item|:count items', $purchase->items_count, ['count' => $purchase->items_count]) }}
              </flux:badge>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
              <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                  <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Product') }}</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Quantity') }}</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Unit price') }}</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Discount') }}</th>
                  <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Subtotal') }}</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach ($purchase->items as $item)
                  <tr wire:key="purchase-item-{{ $item->id }}">
                    <td class="px-5 py-4">
                      <a
                        href="{{ route('products.show', $item->product->slug) }}"
                        class="font-medium text-gray-900 hover:text-sky-600 dark:text-white dark:hover:text-sky-400"
                      >
                        {{ $item->product->title }}
                      </a>
                      <p class="text-xs text-gray-500 dark:text-gray-400">{{ $item->product->sku }}</p>
                    </td>
                    <td class="px-5 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $item->quantity }}</td>
                    <td class="px-5 py-4 text-right text-sm text-gray-700 dark:text-gray-300">
                      {{ number_format((float) $item->unit_price, 2, ',', '.') }} €
                    </td>
                    <td class="px-5 py-4 text-right text-sm text-gray-700 dark:text-gray-300">
                      {{ number_format((float) $item->discount_percentage, 2, ',', '.') }} %
                    </td>
                    <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                      {{ number_format((float) $item->total_price, 2, ',', '.') }} €
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </article>
      @empty
        <flux:callout icon="shopping-bag">
          <flux:callout.heading>{{ __('You have not made any purchases yet.') }}</flux:callout.heading>
          <flux:callout.text>{{ __('Your completed orders will appear here.') }}</flux:callout.text>
        </flux:callout>
      @endforelse
    </div>

    <div>
      {{ $purchases->links() }}
    </div>
  </div>
</main>
