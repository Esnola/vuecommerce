<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    #[Locked]
    public User $user;

    #[Locked]
    public bool $canManageUser = false;

    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public $avatar;

    public string $status = '';

    public bool $isAdmin = false;

    public bool $emailVerified = false;

    public string $password = '';

    public string $passwordConfirmation = '';

    public bool $saved = false;

    public function mount(User $user): void
    {
        Gate::authorize('view', $user);

        $this->user = $user;
        $this->canManageUser = (bool) auth()->user()->is_admin;
        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->status = $user->status?->value ?? UserStatusEnum::PENDING->value;
        $this->isAdmin = (bool) $user->is_admin;
        $this->emailVerified = $user->email_verified_at !== null;
    }

    public function save(): void
    {
        Gate::authorize('update', $this->user);

        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user),
            ],
            'phone' => ['nullable', 'string', 'max:255', 'regex:/^\+?[0-9]+$/'],
            'avatar' => [
                'nullable',
                File::image()
                    ->max('1mb')
                    ->dimensions(Rule::dimensions()->maxWidth(680)->maxHeight(680)),
            ],
            'password' => ['nullable', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['nullable', 'string'],
        ]);

        $emailChanged = $this->user->email !== $validated['email'];

        $attributes = [
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
        ];

        $previousAvatar = $this->user->avatar;

        if ($validated['avatar'] !== null) {
            $attributes['avatar'] = $validated['avatar']->store('avatars', 'public');
        }

        if ($validated['password'] !== '') {
            $attributes['password'] = $validated['password'];
        }

        if ($this->canManageUser) {
            $adminFields = $this->validate([
                'status' => ['required', Rule::enum(UserStatusEnum::class)],
                'isAdmin' => ['boolean'],
                'emailVerified' => ['boolean'],
            ]);

            $attributes['status'] = $adminFields['status'];
            $attributes['is_admin'] = $adminFields['isAdmin'];
            $attributes['email_verified_at'] = $adminFields['emailVerified'] ? now() : null;

            if (! $adminFields['emailVerified'] && $adminFields['status'] !== UserStatusEnum::SUSPEND->value) {
                $attributes['status'] = UserStatusEnum::PENDING->value;
            }
        } elseif ($emailChanged) {
            $attributes['email_verified_at'] = null;
            $attributes['status'] = UserStatusEnum::PENDING->value;
        }

        $this->user->forceFill($attributes)->save();

        if ($validated['avatar'] !== null && $previousAvatar !== null) {
            Storage::disk('public')->delete($previousAvatar);
        }

        $this->avatar = null;
        $this->password = '';
        $this->passwordConfirmation = '';
        $this->emailVerified = $this->user->email_verified_at !== null;
        $this->saved = true;
    }
};
?>

<main class="min-h-screen bg-gray-50 py-12 dark:bg-gray-800">
  <div class="mx-auto flex max-w-3xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-2">
      <flux:heading size="xl">
        {{ auth()->user()->is($user) ? __('Your profile') : __('Edit user') }}
      </flux:heading>
      <flux:text>
        {{ __('Update the account information and security settings.') }}
      </flux:text>
    </div>

    @if ($saved)
      <flux:callout color="green" icon="check-circle">
        <flux:callout.heading>{{ __('User updated successfully.') }}</flux:callout.heading>
      </flux:callout>
    @endif

    <form wire:submit="save" class="flex flex-col gap-8">
      <section class="flex flex-col gap-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-8">
        <div class="flex flex-col gap-1">
          <flux:heading size="lg">{{ __('Personal information') }}</flux:heading>
          <flux:text>{{ __('Information used to identify and contact this user.') }}</flux:text>
        </div>

        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
          @if ($avatar && str_starts_with($avatar->getMimeType(), 'image/'))
            <img
              src="{{ $avatar->temporaryUrl() }}"
              alt="{{ __('Avatar preview') }}"
              class="size-24 rounded-full object-cover ring-2 ring-gray-200 dark:ring-white/15"
            />
          @elseif ($user->avatarUrl())
            <img
              src="{{ $user->avatarUrl() }}"
              alt="{{ __('User avatar') }}"
              class="size-24 rounded-full object-cover ring-2 ring-gray-200 dark:ring-white/15"
            />
          @else
            <div class="flex size-24 items-center justify-center rounded-full bg-gray-100 text-2xl font-semibold text-gray-500 ring-2 ring-gray-200 dark:bg-white/10 dark:text-gray-300 dark:ring-white/15">
              {{ str($firstName)->substr(0, 1)->append(str($lastName)->substr(0, 1))->upper() }}
            </div>
          @endif

          <flux:field class="flex-1">
            <flux:label>{{ __('Avatar') }}</flux:label>
            <flux:input
              wire:model="avatar"
              type="file"
              accept="image/*"
            />
            <flux:description>{{ __('Image up to 680 x 680 pixels and 1 MB.') }}</flux:description>
            <flux:error name="avatar" />
            <div wire:loading wire:target="avatar">
              <flux:text>{{ __('Uploading image...') }}</flux:text>
            </div>
          </flux:field>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <flux:field>
            <flux:label>{{ __('First name') }}</flux:label>
            <flux:input
              wire:model="firstName"
              icon="user"
              :placeholder="__('Enter the first name')"
              autocomplete="given-name"
              required
            />
            <flux:error name="firstName" />
          </flux:field>

          <flux:field>
            <flux:label>{{ __('Last name') }}</flux:label>
            <flux:input
              wire:model="lastName"
              icon="identification"
              :placeholder="__('Enter the last name')"
              autocomplete="family-name"
              required
            />
            <flux:error name="lastName" />
          </flux:field>
        </div>

        <flux:field>
          <flux:label>{{ __('Email') }}</flux:label>
          <flux:input
            wire:model="email"
            icon="envelope"
            :placeholder="__('Enter the email address')"
            type="email"
            autocomplete="email"
            required
          />
          <flux:error name="email" />
        </flux:field>

        <flux:field>
          <flux:label>{{ __('Phone') }}</flux:label>
          <flux:input
            wire:model="phone"
            icon="phone"
            :placeholder="__('Enter the phone number')"
            type="tel"
            inputmode="tel"
            autocomplete="tel"
            pattern="\+?[0-9]+"
          />
          <flux:error name="phone" />
        </flux:field>
      </section>

      @if ($canManageUser)
        <section class="flex flex-col gap-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-8">
          <div class="flex flex-col gap-1">
            <flux:heading size="lg">{{ __('Administration') }}</flux:heading>
            <flux:text>{{ __('Manage account status, verification, and permissions.') }}</flux:text>
          </div>

          <flux:field>
            <flux:label>{{ __('Status') }}</flux:label>
            <flux:input.group>
              <flux:input.group.prefix>
                <flux:icon.signal class="size-4 text-zinc-500" />
              </flux:input.group.prefix>
              <flux:select wire:model="status" :placeholder="__('Select a status')">
                @foreach (UserStatusEnum::cases() as $userStatus)
                  <flux:select.option
                    wire:key="user-status-{{ $userStatus->value }}"
                    :value="$userStatus->value"
                  >
                    {{ $userStatus->label() }}
                  </flux:select.option>
                @endforeach
              </flux:select>
            </flux:input.group>
            <flux:error name="status" />
          </flux:field>

          <div class="grid gap-4 sm:grid-cols-2">
            <div class="flex items-center gap-2">
              <flux:icon.check-badge class="size-5 text-zinc-500 dark:text-zinc-400" />
              <flux:switch wire:model="emailVerified" :label="__('Email verified')" class="cursor-pointer"/>
            </div>
            <div class="flex items-center gap-2">
              <flux:icon.shield-check class="size-5 text-zinc-500 dark:text-zinc-400 curso" />
              <flux:switch wire:model="isAdmin" :label="__('Administrator')" class="cursor-pointer" />
            </div>
          </div>
        </section>
      @endif

      <section class="flex flex-col gap-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-white/5 sm:p-8">
        <div class="flex flex-col gap-1">
          <flux:heading size="lg">{{ __('Password') }}</flux:heading>
          <flux:text>{{ __('Leave these fields empty to keep the current password.') }}</flux:text>
        </div>

        <div class="grid gap-6 sm:grid-cols-2">
          <flux:field>
            <flux:label>{{ __('New password') }}</flux:label>
            <flux:input
              wire:model="password"
              icon="key"
              :placeholder="__('Enter a new password')"
              type="password"
              autocomplete="new-password"
              viewable
            />
            <flux:error name="password" />
          </flux:field>

          <flux:field>
            <flux:label>{{ __('Confirm password') }}</flux:label>
            <flux:input
              wire:model="passwordConfirmation"
              icon="lock-closed"
              :placeholder="__('Repeat the new password')"
              type="password"
              autocomplete="new-password"
              viewable
            />
            <flux:error name="passwordConfirmation" />
          </flux:field>
        </div>
      </section>

      <div class="flex justify-end">
        <flux:button
          type="submit"
          variant="primary"
          wire:loading.attr="disabled"
          wire:target="save"
          class="cursor-pointer"
        >
          <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
          <span wire:loading wire:target="save">{{ __('Saving...') }}</span>
        </flux:button>
      </div>
    </form>
  </div>
</main>
