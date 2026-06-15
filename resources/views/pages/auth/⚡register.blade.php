<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Component;

new class extends Component
{
    public string $firstName = '';

    public string $lastName = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => ['nullable', 'string', 'max:255', 'regex:/^\+?[0-9]+$/'],
            'password' => ['required', 'string', 'min:8', 'same:passwordConfirmation'],
            'passwordConfirmation' => ['required', 'string'],
        ]);

        User::query()->create([
            'first_name' => $validated['firstName'],
            'last_name' => $validated['lastName'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'status' => UserStatusEnum::PENDING,
            'password' => $validated['password'],
        ]);

        session()->flash('registration-status', __('Your account has been created and is awaiting approval.'));

        $this->redirectRoute('login');
    }
};
?>

<main class="flex min-h-[calc(100vh-6rem)] items-center bg-gray-50 px-4 py-16 dark:bg-gray-800 sm:px-6">
  <div class="mx-auto w-full max-w-2xl">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-white/10 dark:bg-white/5 sm:p-8">
      <div class="flex flex-col gap-2 text-center">
        <flux:heading size="xl">{{ __('Create account') }}</flux:heading>
        <flux:text>{{ __('Enter your details to request access to Vuecommerce.') }}</flux:text>
      </div>

      <form wire:submit="register" class="mt-8 flex flex-col gap-6">
        <div class="grid gap-6 sm:grid-cols-2">
          <flux:field>
            <flux:label>{{ __('First name') }}</flux:label>
            <flux:input
              wire:model="firstName"
              icon="user"
              :placeholder="__('Enter the first name')"
              autocomplete="given-name"
              autofocus
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
            autocomplete="tel"
          />
          <flux:error name="phone" />
        </flux:field>

        <div class="grid gap-6 sm:grid-cols-2">
          <flux:field>
            <flux:label>{{ __('Password') }}</flux:label>
            <flux:input
              wire:model="password"
              type="password"
              autocomplete="new-password"
              required
              viewable
            />
            <flux:error name="password" />
          </flux:field>

          <flux:field>
            <flux:label>{{ __('Confirm password') }}</flux:label>
            <flux:input
              wire:model="passwordConfirmation"
              type="password"
              autocomplete="new-password"
              required
              viewable
            />
            <flux:error name="passwordConfirmation" />
          </flux:field>
        </div>

        <flux:button
          type="submit"
          variant="primary"
          class="w-full"
          wire:loading.attr="disabled"
          wire:target="register"
        >
          <span wire:loading.remove wire:target="register">{{ __('Create account') }}</span>
          <span wire:loading wire:target="register">{{ __('Creating account...') }}</span>
        </flux:button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-300">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
          {{ __('Sign In') }}
        </a>
      </p>
    </div>
  </div>
</main>
