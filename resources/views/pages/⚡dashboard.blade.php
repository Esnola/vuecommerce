<?php

use App\Models\Order;
use App\Models\User;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    #[Locked]
    public int $purchaseCount = 0;

    #[Locked]
    public int $userCount = 0;

    #[Locked]
    public int $orderCount = 0;

    #[Locked]
    public int $favoriteCount = 0;

    public function mount(): void
    {
        abort_unless(auth()->user()->canAccessAccount(), 403);

        $this->purchaseCount = auth()->user()->orders()->count();
        $this->favoriteCount = auth()->user()->favoriteProducts()->count();

        if (auth()->user()->is_admin) {
            $this->userCount = User::query()->count();
            $this->orderCount = Order::query()->count();
        }
    }
};
?>

<main class="min-h-screen bg-gray-50 py-12 dark:bg-gray-800">
  <div class="mx-auto flex max-w-6xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2">
      <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>
      <flux:text>{{ __('Welcome back, :name.', ['name' => auth()->user()->first_name]) }}</flux:text>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
      <flux:card class="flex flex-col gap-5">
        <div class="flex items-start justify-between gap-4">
          <div class="flex flex-col gap-1">
            <flux:heading size="lg">{{ __('Your profile') }}</flux:heading>
            <flux:text>{{ __('Update your personal information, avatar, email, phone, and password.') }}</flux:text>
          </div>
          <flux:icon.user-circle class="size-8 text-sky-600 dark:text-sky-400" />
        </div>

        <div>
          <flux:button :href="route('users.edit', auth()->user())" variant="primary" icon="pencil-square">
            {{ __('Edit profile') }}
          </flux:button>
        </div>
      </flux:card>

      <flux:card class="flex flex-col gap-5">
        <div class="flex items-start justify-between gap-4">
          <div class="flex flex-col gap-1">
            <flux:heading size="lg">{{ __('My purchases') }}</flux:heading>
            <flux:text>{{ trans_choice(':count order|:count orders', $purchaseCount, ['count' => $purchaseCount]) }}</flux:text>
          </div>
          <flux:icon.shopping-bag class="size-8 text-sky-600 dark:text-sky-400" />
        </div>

        <div>
          <flux:button :href="route('purchases.index')" icon="receipt-percent">
            {{ __('View purchases') }}
          </flux:button>
        </div>
      </flux:card>

      <flux:card class="flex flex-col gap-5">
        <div class="flex items-start justify-between gap-4">
          <div class="flex flex-col gap-1">
            <flux:heading size="lg">{{ __('My favorites') }}</flux:heading>
            <flux:text>{{ trans_choice(':count favorite|:count favorites', $favoriteCount, ['count' => $favoriteCount]) }}</flux:text>
          </div>
          <flux:icon.heart class="size-8 text-rose-600 dark:text-rose-400" />
        </div>

        <div>
          <flux:button :href="route('favorites.index')" icon="heart">
            {{ __('Manage favorites') }}
          </flux:button>
        </div>
      </flux:card>

      @if (auth()->user()->is_admin)
        <flux:card class="flex flex-col gap-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
              <flux:heading size="lg">{{ __('Users') }}</flux:heading>
              <flux:text>{{ trans_choice(':count registered user|:count registered users', $userCount, ['count' => $userCount]) }}</flux:text>
            </div>
            <flux:icon.users class="size-8 text-violet-600 dark:text-violet-400" />
          </div>

          <div>
            <flux:button :href="route('users.index')" icon="user-group">
              {{ __('Manage users') }}
            </flux:button>
          </div>
        </flux:card>

        <flux:card class="flex flex-col gap-5">
          <div class="flex items-start justify-between gap-4">
            <div class="flex flex-col gap-1">
              <flux:heading size="lg">{{ __('Orders') }}</flux:heading>
              <flux:text>{{ trans_choice(':count order in the store|:count orders in the store', $orderCount, ['count' => $orderCount]) }}</flux:text>
            </div>
            <flux:icon.clipboard-document-list class="size-8 text-violet-600 dark:text-violet-400" />
          </div>

          <div>
            <flux:button :href="route('orders.index')" icon="clipboard-document-list">
              {{ __('Manage orders') }}
            </flux:button>
          </div>
        </flux:card>
      @endif
    </div>
  </div>
</main>
