<div x-data="{
        bannerVisible: false,
        bannerVisibleAfter: 300,
        storageKey: 'vuecommerce_cookie_consent',

        init() {
            if (this.storedConsent() !== null) {
                return;
            }

            setTimeout(() => {
                this.bannerVisible = true;
            }, this.bannerVisibleAfter);
        },

        accept() {
            this.rememberConsent('accepted');
        },

        deny() {
            this.rememberConsent('denied');
        },

        rememberConsent(value) {
            try {
                window.localStorage.setItem(this.storageKey, value);
            } catch (error) {
                //
            }

            document.cookie = `${this.storageKey}=${value}; path=/; max-age=31536000; SameSite=Lax`;
            this.bannerVisible = false;
        },

        storedConsent() {
            try {
                const localConsent = window.localStorage.getItem(this.storageKey);

                if (localConsent !== null) {
                    return localConsent;
                }
            } catch (error) {
                //
            }

            return document.cookie
                .split('; ')
                .find((cookie) => cookie.startsWith(`${this.storageKey}=`))
                ?.split('=')[1] ?? null;
        },
    }"
     x-show="bannerVisible"
     x-transition:enter="transition ease-out duration-500"
     x-transition:enter-start="translate-y-full"
     x-transition:enter-end="translate-y-0"
     x-transition:leave="transition ease-in duration-300"
     x-transition:leave-start="translate-y-0"
     x-transition:leave-end="translate-y-full"
     x-init="init()"
     class="fixed bottom-0 right-0 z-50 w-full h-auto duration-300 ease-out sm:px-5 sm:pb-5 sm:w-104 lg:w-full"
     role="region"
     aria-label="{{ __('Cookie consent') }}"
     x-cloak>
  <div class="flex flex-col items-center justify-between w-full h-full max-w-4xl p-6 mx-auto bg-white border-t shadow-lg dark:border-gray-700 dark:bg-gray-900 lg:p-8 lg:flex-row sm:border-0 sm:rounded-xl">
    <div class="flex flex-col items-start h-full pb-6 text-xs lg:items-center lg:flex-row lg:pb-0 lg:pr-6 lg:space-x-5 text-neutral-600 dark:text-gray-300">
      <img src="{{ asset('images/cookie.png') }}"
           alt=""
           class="w-8 h-8 sm:w-12 sm:h-12 lg:w-16 lg:h-16">
      <div class="pt-6 lg:pt-0">
        <h4 class="w-full mb-1 text-xl font-bold leading-none -translate-y-1 text-neutral-900 dark:text-white">{{ __('Cookie Notice') }}</h4>
        <p class="">{{ __('We use cookies to make your online experience better.') }} <span class="hidden lg:inline">{{ __('By continuing to browse, you give us your digital consent to indulge you with some sweet, data-filled treats.') }}</span>
        </p>
      </div>
    </div>
    <div class="flex items-end justify-end w-full pl-3 space-x-3 lg:shrink-0 lg:w-auto">
      <button type="button"
              @click="deny()"
              class="inline-flex items-center justify-center shrink-0 w-1/2 px-4 py-2 text-sm font-medium tracking-wide transition-colors duration-200 bg-white border-2 rounded-md cursor-pointer lg:w-auto text-neutral-600 hover:text-neutral-700 border-neutral-950 dark:border-gray-500 dark:bg-gray-900 dark:text-gray-200 dark:hover:text-white focus:ring-2 focus:ring-offset-2 focus:ring-neutral-900 focus:shadow-outline focus:outline-none">
        {{ __('Deny All') }}
      </button>
      <button type="button"
              @click="accept()"
              class="inline-flex items-center justify-center flex-shrink-0 w-1/2 px-4 py-2 text-sm font-medium tracking-wide text-white transition-colors duration-200 border-2 rounded-md cursor-pointer lg:w-auto bg-neutral-950 border-neutral-950 hover:bg-neutral-900 focus:ring-2 focus:ring-offset-2 focus:ring-neutral-900 focus:shadow-outline focus:outline-none">
        {{ __('Accept All') }}
      </button>
    </div>
  </div>
</div>
