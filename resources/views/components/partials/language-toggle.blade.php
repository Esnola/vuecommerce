<div class="flex rounded-lg bg-zinc-800/5 dark:bg-white/10 h-10 p-px">
  <button type="button"
          wire:click="toggle"
          role="button"
          {{ $locale === 'es' ? 'disabled' : '' }}
          aria-checked="{{ $locale === 'es' ? 'true' : 'false' }}"
          class="flex w-22 items-center justify-between p-0.5 ">
    <div class="p-px h-full rounded-md w-full
    {{ $locale === 'es' ? 'bg-white dark:bg-white/30 pointer-events-none' : 'opacity-60' }}">
    <span class="flex w-full h-full items-center justify-center rounded-md data-checked:shadow-xs data-checked:text-zinc-800 dark:data-checked:text-white data-checked:bg-white dark:data-checked:bg-white/20 cursor-pointer">
      <span class="w-6 h-6 bg-no-repeat bg-center bg-contain"
            style="background-image: url('{{ asset('images/flags/es.svg') }}');"></span>
    </span>
    </div>
  </button>
  <button type="button"
          wire:click="toggle"
          role="button"
          {{ $locale === 'en' ? 'disabled' : '' }}
          aria-checked="{{ $locale === 'en' ? 'true' : 'false' }}"
          class="flex w-22 items-center justify-between p-0.5 ">
    <div class="p-px h-full rounded-md w-full
    {{ $locale === 'en' ? 'bg-white/5 border border-white/30 dark:bg-white/10 pointer-events-none ' : 'opacity-60' }}">
    <span class="flex w-full h-full items-center justify-center rounded-md data-checked:shadow-xs data-checked:text-zinc-800 dark:data-checked:text-white data-checked:bg-white dark:data-checked:bg-white/20 cursor-pointer">
      <span class="w-6 h-6 bg-no-repeat bg-center bg-contain rounded"
            style="background-image: url('{{ asset('/images/flags/gb.svg') }}');"></span>
    </span>
    </div>

  </button>
</div>
