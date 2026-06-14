<div class="flex p-px rounded-md h-10">
    <el-dropdown open class="block relative">
        <button
            class="inline-flex focus:z-10 relative inset-ring-1 inset-ring-gray-700 items-center hover:bg-white/20 px-2 py-2 rounded-md text-gray-400 cursor-pointer">
            <span class="sr-only">{{ __('Open options') }}</span>
            <img src="{{ $locale === 'es' ? asset('images/flags/es.svg') : asset('images/flags/gb.svg') }}"
                class="size-6">
        </button>
        <el-menu anchor="bottom end" popover
            class="bg-gray-800 data-closed:opacity-0 ml-1 rounded-md outline-1 outline-white/10 -outline-offset-1 w-56 max-w-fit text-white/70 data-closed:scale-95 origin-top-right transition transition-discrete data-enter:duration-100 data-leave:duration-75 data-leave:ease-in data-enter:ease-out data-closed:transform">
            <div class="space-y-2 px-4 py-2">
                <button type="button" wire:click="toggle" role="button" {{ $locale === 'es' ? 'disabled' : '' }}
                    aria-checked="{{ $locale === 'es' ? 'true' : 'false' }}"
                    class="flex justify-between items-center gap-x-4 data-checked:bg-white dark:data-checked:bg-white/20 data-checked:shadow-xs p-0.5 rounded-md w-full h-full data-checked:text-zinc-800 dark:data-checked:text-white cursor-pointer">
                    <img src="{{ asset('images/flags/es.svg') }}" alt="{{ __('Spanish') }}" class="w-6 h-6">
                    <h6>{{ __('Spanish') }}</h6>
                </button>
                <button type="button" wire:click="toggle" role="button" {{ $locale === 'en' ? 'disabled' : '' }}
                    aria-checked="{{ $locale === 'en' ? 'true' : 'false' }}"
                    class="flex justify-between items-center gap-x-4 data-checked:bg-white dark:data-checked:bg-white/20 data-checked:shadow-xs p-0.5 rounded-md w-full h-full data-checked:text-zinc-800 dark:data-checked:text-white cursor-pointer">
                    <img src="{{ asset('images/flags/gb.svg') }}" alt="{{ __('English') }}" class="w-6 h-6">
                    <h6>{{ __('English') }}</h6>
                </button>
            </div>
        </el-menu>
    </el-dropdown>
</div>
