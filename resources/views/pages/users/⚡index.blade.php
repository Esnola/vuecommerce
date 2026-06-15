<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', User::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'avatar',
                'status',
                'is_admin',
                'email_verified_at',
                'created_at',
            ])
            ->withCount('orders')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($query): void {
                    $query
                        ->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when(
                $this->status !== '',
                fn ($query) => $query->where('status', $this->status),
            )
            ->latest()
            ->paginate(10);
    }
};
?>

@php
  $users = $this->users();
@endphp

<main class="min-h-screen bg-gray-50 py-12 dark:bg-gray-800">
  <div class="mx-auto flex max-w-7xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2">
      <flux:heading size="xl">{{ __('Users') }}</flux:heading>
      <flux:text>{{ __('Review accounts and open any user profile to edit it.') }}</flux:text>
    </div>

    <div class="grid gap-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5 md:grid-cols-[1fr_16rem]">
      <flux:field>
        <flux:label>{{ __('Search users') }}</flux:label>
        <flux:input
          wire:model.live.debounce.300ms="search"
          icon="magnifying-glass"
          :placeholder="__('Name or email')"
          clearable
        />
      </flux:field>

      <flux:field>
        <flux:label>{{ __('Status') }}</flux:label>
        <flux:select wire:model.live="status">
          <flux:select.option value="">{{ __('All statuses') }}</flux:select.option>
          @foreach (UserStatusEnum::cases() as $userStatus)
            <flux:select.option wire:key="status-filter-{{ $userStatus->value }}" :value="$userStatus->value">
              {{ $userStatus->label() }}
            </flux:select.option>
          @endforeach
        </flux:select>
      </flux:field>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">
      <flux:table :paginate="$users">
        <flux:table.columns>
          <flux:table.column>{{ __('User') }}</flux:table.column>
          <flux:table.column>{{ __('Phone') }}</flux:table.column>
          <flux:table.column>{{ __('Status') }}</flux:table.column>
          <flux:table.column>{{ __('Role') }}</flux:table.column>
          <flux:table.column align="end">{{ __('Orders') }}</flux:table.column>
          <flux:table.column align="end">{{ __('Actions') }}</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
          @forelse ($users as $user)
            @php
              $statusColor = match ($user->status) {
                UserStatusEnum::ACTIVE => 'green',
                UserStatusEnum::PENDING => 'amber',
                UserStatusEnum::SUSPEND => 'red',
              };
            @endphp

            <flux:table.row :key="$user->id">
              <flux:table.cell>
                <div class="flex items-center gap-3">
                  <flux:avatar
                    :src="$user->avatarUrl()"
                    :initials="str($user->first_name)->substr(0, 1)->append(str($user->last_name)->substr(0, 1))->upper()"
                  />
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white">
                      {{ $user->first_name }} {{ $user->last_name }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                  </div>
                </div>
              </flux:table.cell>
              <flux:table.cell>{{ $user->phone ?: __('Not provided') }}</flux:table.cell>
              <flux:table.cell>
                <flux:badge :color="$statusColor">{{ $user->status->label() }}</flux:badge>
              </flux:table.cell>
              <flux:table.cell>
                <flux:badge :color="$user->is_admin ? 'violet' : 'zinc'">
                  {{ $user->is_admin ? __('Administrator') : __('Customer') }}
                </flux:badge>
              </flux:table.cell>
              <flux:table.cell align="end">{{ $user->orders_count }}</flux:table.cell>
              <flux:table.cell align="end">
                <flux:button
                  :href="route('users.edit', $user)"
                  size="sm"
                  icon="pencil-square"
                  :aria-label="__('Edit :name', ['name' => $user->first_name])"
                >
                  {{ __('Edit') }}
                </flux:button>
              </flux:table.cell>
            </flux:table.row>
          @empty
            <flux:table.row>
              <flux:table.cell colspan="6">
                <div class="py-8 text-center">
                  <flux:text>{{ __('No users match the selected filters.') }}</flux:text>
                </div>
              </flux:table.cell>
            </flux:table.row>
          @endforelse
        </flux:table.rows>
      </flux:table>
    </div>
  </div>
</main>
