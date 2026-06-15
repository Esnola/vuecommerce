<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $selectedBuyerId = '';

    public function mount(): void
    {
        Gate::authorize('view-orders');
    }

    public function updatedSelectedBuyerId(): void
    {
        $this->resetPage();
    }

    public function buyers(): Collection
    {
        return User::query()
            ->select(['id', 'first_name', 'last_name', 'email'])
            ->whereHas('orders')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    public function orders(): LengthAwarePaginator
    {
        return Order::query()
            ->with([
                'buyer:id,first_name,last_name,email,phone',
                'items.product:id,title,slug,sku',
            ])
            ->withCount('items')
            ->when(
                $this->selectedBuyerId !== '',
                fn ($query) => $query->where('created_by', (int) $this->selectedBuyerId),
            )
            ->latest()
            ->paginate(10);
    }
};
?>

@php
  $buyers = $this->buyers();
  $orders = $this->orders();
@endphp

<main class="min-h-screen bg-gray-50 py-12 dark:bg-gray-800">
  <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2">
      <flux:heading size="xl">{{ __('Orders') }}</flux:heading>
      <flux:text>{{ __('Review every order and its purchased products.') }}</flux:text>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
      <flux:field class="max-w-md">
        <flux:label>{{ __('Filter by user') }}</flux:label>
        <flux:select wire:model.live="selectedBuyerId">
          <flux:select.option value="">{{ __('All users') }}</flux:select.option>
          @foreach ($buyers as $buyer)
            <flux:select.option wire:key="buyer-filter-{{ $buyer->id }}" :value="(string) $buyer->id">
              {{ $buyer->first_name }} {{ $buyer->last_name }} · {{ $buyer->email }}
            </flux:select.option>
          @endforeach
        </flux:select>
      </flux:field>
    </div>

    <div class="flex flex-col gap-5">
      @forelse ($orders as $order)
        @php
          $badgeColor = match ($order->status) {
            \App\Enums\OrderStatusEnum::PENDING => 'amber',
            \App\Enums\OrderStatusEnum::SHIPPED => 'sky',
            \App\Enums\OrderStatusEnum::DELIVERED => 'green',
          };
        @endphp

        <details
          wire:key="order-{{ $order->id }}"
          data-order="{{ $order->id }}"
          class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5"
        >
          <summary class="grid cursor-pointer list-none gap-5 p-5 [&::-webkit-details-marker]:hidden lg:grid-cols-[1fr_auto]">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
              <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Order') }}</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">#{{ $order->id }}</p>
              </div>

              <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Buyer') }}</p>
                <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                  {{ $order->buyer ? "{$order->buyer->first_name} {$order->buyer->last_name}" : __('Unknown buyer') }}
                </p>
                @if ($order->buyer)
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->buyer->email }}</p>
                @endif
                @if ($order->buyer?->phone)
                  <p class="text-sm text-gray-500 dark:text-gray-400">{{ $order->buyer->phone }}</p>
                @endif
              </div>

              <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Date') }}</p>
                <time class="mt-1 block text-sm text-gray-700 dark:text-gray-300" datetime="{{ $order->created_at->toISOString() }}">
                  {{ $order->created_at->translatedFormat('j F Y, H:i') }}
                </time>
              </div>

              <div>
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('Total') }}</p>
                <p class="mt-1 text-lg font-bold text-gray-900 dark:text-white">
                  {{ number_format((float) $order->total_price, 2, ',', '.') }} €
                </p>
              </div>
            </div>

            <div class="flex items-start gap-2 lg:justify-end">
              <flux:badge :color="$badgeColor">{{ $order->status->label() }}</flux:badge>
              <flux:badge color="zinc">
                {{ trans_choice(':count item|:count items', $order->items_count, ['count' => $order->items_count]) }}
              </flux:badge>
              <flux:icon.chevron-down class="size-5 text-gray-500 transition-transform group-open:rotate-180 dark:text-gray-400" />
            </div>
          </summary>

          <div class="overflow-x-auto border-t border-gray-200 dark:border-white/10">
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
                @foreach ($order->items as $item)
                  <tr wire:key="order-item-{{ $item->id }}">
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
        </details>
      @empty
        <flux:callout icon="shopping-bag">
          <flux:callout.heading>{{ __('No orders found.') }}</flux:callout.heading>
        </flux:callout>
      @endforelse
    </div>

    <div>
      {{ $orders->links() }}
    </div>
  </div>
</main>
