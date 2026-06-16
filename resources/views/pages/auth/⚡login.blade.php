<?php

use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public bool $isDev = false;

    public bool $verificationEmailSent = false;

    public function mount(): void
    {
        $this->isDev = ! App::environment('production');
    }

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

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        if ($user->status === UserStatusEnum::SUSPEND) {
            throw ValidationException::withMessages([
                'email' => trans('auth.suspended'),
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => trans('auth.unverified'),
            ]);
        }

        if ($user->status === UserStatusEnum::PENDING) {
            throw ValidationException::withMessages([
                'email' => trans('auth.pending'),
            ]);
        }

        Auth::login($user, $this->remember);

        session()->regenerate();

        $this->redirectIntended(route('dashboard'));
    }

    public function resendVerificationEmail(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $throttleKey = 'verification-email:'.Str::transliterate(
            Str::lower($this->email).'|'.request()->ip()
        );

        if (RateLimiter::tooManyAttempts($throttleKey, 6)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        $user = User::query()->where('email', $this->email)->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        RateLimiter::hit($throttleKey, 60);

        $this->verificationEmailSent = true;
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

      @if (session('registration-status'))
        <flux:callout color="green" icon="check-circle" class="mt-6">
          <flux:callout.heading>{{ session('registration-status') }}</flux:callout.heading>
        </flux:callout>
      @endif

      @if ($this->verificationEmailSent)
        <flux:callout color="green" icon="check-circle" class="mt-6">
          <flux:callout.heading>{{ __('If the account exists and is not verified, we have sent a new verification link.') }}</flux:callout.heading>
        </flux:callout>
      @endif

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
          <button
            type="button"
            wire:click="resendVerificationEmail"
            wire:loading.attr="disabled"
            wire:target="resendVerificationEmail"
            class="cursor-pointer text-left text-sm font-medium text-indigo-600 hover:text-indigo-500 disabled:cursor-wait disabled:opacity-60 dark:text-indigo-400"
          >
            <span wire:loading.remove wire:target="resendVerificationEmail">{{ __('Resend verification email') }}</span>
            <span wire:loading wire:target="resendVerificationEmail">{{ __('Sending verification email...') }}</span>
          </button>
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
          class="w-full cursor-pointer"
          wire:loading.attr="disabled"
          wire:target="login"
        >
          <span wire:loading.remove wire:target="login">{{ __('Sign In') }}</span>
          <span wire:loading wire:target="login">{{ __('Signing in...') }}</span>
        </flux:button>
      </form>

      @if ($this->isDev)
        <p class="mt-6 text-center text-xs text-gray-500 dark:text-gray-400">
          {{ __('Administrator access: test@example.com / password') }}
        </p>
      @endif

      <p class="mt-6 text-center text-sm text-gray-600 dark:text-gray-300">
        {{ __("Don't have an account?") }}
        <a href="{{ route('register') }}" wire:navigate class="font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
          {{ __('Create account') }}
        </a>
      </p>
    </div>
  </div>
</main>
