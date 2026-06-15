<nav class="relative bg-gray-800 shadow-white/10 shadow-lg">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-4">
    <div class="flex h-16 items-center justify-between">
      <div class="flex items-center justify-between w-full ">
        <img src="{{asset('images/gitlogo.png')}}" alt="Your Company"
             class="w-14 rounded-full h-auto shrink-0"/>
        <div class="hidden sm:ml-6 sm:flex w-full justify-between items-center
        ">
          <div class="flex  justify-between items-center pl-8 w-full ">
            <x-partials.main-links/>
            <div class="flex items-center gap-x-4">
              <livewire:language-toggle/>
              <x-partials.darkmode-switch/>
            </div>
          </div>
        </div>
      </div>
      <div class="hidden sm:ml-3 min-w-fit sm:flex items-center">
          @auth
            <!--    <button type="button"
                  class="relative rounded-full p-1 text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
            <span class="absolute -inset-1.5"></span>
            <span class="sr-only">View notifications</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
                 aria-hidden="true" class="size-6">
              <path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                    stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </button>
        Profile dropdown -->
          <el-dropdown class="relative ml-3">
            <button class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
              <span class="absolute -inset-1.5"></span>
              <span class="sr-only">Open user menu</span>
              <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                   alt="" class="size-8 rounded-full bg-gray-800 outline -outline-offset-1 outline-white/10"/>
            </button>

            <el-menu anchor="bottom end" popover
                     class="w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg outline-1 outline-black/5 transition transition-discrete [--anchor-gap:--spacing(2)] data-closed:scale-95 data-closed:transform data-closed:opacity-0 data-enter:duration-100 data-enter:ease-out data-leave:duration-75 data-leave:ease-in">
              <a href="{{ route('users.edit', auth()->user()) }}"
                 class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 focus:bg-gray-100 focus:outline-hidden">
                {{ __('Your profile') }}
              </a>
              <a href="{{ route('purchases.index') }}"
                 class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 focus:bg-gray-100 focus:outline-hidden">
                {{ __('My purchases') }}
              </a>
              <div class="px-4 py-2">
                <p class="text-sm font-medium text-gray-800 dark:text-white">
                  {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
                </p>
                <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email }}</p>
              </div>
              <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="block w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 focus:bg-gray-100 focus:outline-hidden">
                  {{ __('Sign out') }}
                </button>
              </form>
            </el-menu>
          </el-dropdown>
          @else
            <div class="flex items-center gap-x-4">
            <a href="{{ route('login') }}"
               class="min-w-fit rounded-md bg-white/10 px-3 py-2 text-[10px] font-medium text-white hover:bg-white/20">
              {{ __('Log In') }}
            </a>
            </div>
          @endauth
      </div>
      <div class="-mr-2 flex sm:hidden">
        <!-- Mobile menu button -->
        <button type="button" command="--toggle" commandfor="mobile-menu"
                class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white focus:outline-2 focus:-outline-offset-1 focus:outline-indigo-500">
          <span class="absolute -inset-0.5"></span>
          <span class="sr-only">Open main menu</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
               aria-hidden="true" class="size-6 in-aria-expanded:hidden">
            <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
               aria-hidden="true" class="size-6 not-in-aria-expanded:hidden">
            <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <el-disclosure id="mobile-menu" hidden class="block sm:hidden">
    <div class="space-y-1 px-2 pt-2 pb-3">
      <x-partials.main-links/>
    </div>
    <div class="border-t border-white/10 pt-4 pb-3">
      @auth
      <div class="flex items-center px-5">
        <div class="shrink-0">
          <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
               alt="" class="size-10 rounded-full bg-gray-800 outline -outline-offset-1 outline-white/10"/>
        </div>
        <div class="ml-3">
          <div class="text-base font-medium text-white">
            {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
          </div>
          <div class="text-sm font-medium text-gray-400">{{ auth()->user()->email }}</div>
        </div>
        <button type="button"
                class="relative ml-auto shrink-0 rounded-full p-1 text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
          <span class="absolute -inset-1.5"></span>
          <span class="sr-only">View notifications</span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon"
               aria-hidden="true" class="size-6">
            <path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>
      </div>
      <div class="mt-3 space-y-1 px-2">
        <a href="{{ route('users.edit', auth()->user()) }}"
           class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-white/5 hover:text-white">
          {{ __('Your profile') }}
        </a>
        <a href="{{ route('purchases.index') }}"
           class="block rounded-md px-3 py-2 text-base font-medium text-gray-400 hover:bg-white/5 hover:text-white">
          {{ __('My purchases') }}
        </a>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit"
                  class="block w-full rounded-md px-3 py-2 text-left text-base font-medium text-gray-400 hover:bg-white/5 hover:text-white">
            {{ __('Sign out') }}
          </button>
        </form>
      </div>
      @else
        <div class="px-5">
          <a href="{{ route('login') }}"
             class="block rounded-md bg-white/10 px-3 py-2 text-center text-base font-medium text-white hover:bg-white/20">
            {{ __('Sign In') }}
          </a>
        </div>
      @endauth
    </div>
  </el-disclosure>
</nav>
