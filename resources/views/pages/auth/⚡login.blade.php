<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(
            Str::lower($this->email).'|'.request()->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();

        $this->redirectIntended(route('pages.index'));
    }
};
?>

<main class="flex min-h-[calc(100vh-6rem)] items-center bg-gray-50 px-4 py-16 dark:bg-gray-800 sm:px-6">
  <div class="mx-auto w-full max-w-md">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-white/10 dark:bg-white/5 sm:p-8">
      <div class="flex flex-col gap-2 text-center">
        <flux:heading size="xl">{{ __('Sign In') }}</flux:heading>
        <flux:text>{{ __('Access your account with your email and password.') }}</flux:text>
      </div>

      <form wire:submit="login" class="mt-8 flex flex-col gap-6">
        <flux:field>
          <flux:label>{{ __('Email') }}</flux:label>
          <flux:input
            wire:model="email"
            type="email"
            autocomplete="email"
            autofocus
            required
          />
          <flux:error name="email"/>
        </flux:field>

        <flux:field>
          <flux:label>{{ __('Password') }}</flux:label>
          <flux:input
            wire:model="password"
            type="password"
            autocomplete="current-password"
            required
            viewable
          />
          <flux:error name="password"/>
        </flux:field>

        <flux:checkbox wire:model="remember" :label="__('Remember me')"/>

        <flux:button
          type="submit"
          variant="primary"
          class="w-full"
          wire:loading.attr="disabled"
          wire:target="login"
        >
          <span wire:loading.remove wire:target="login">{{ __('Sign In') }}</span>
          <span wire:loading wire:target="login">{{ __('Signing in...') }}</span>
        </flux:button>
      </form>

      <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
        {{ __('Administrator access: test@example.com / password') }}
      </p>
    </div>
  </div>
</main>
