@props(['product'])

<section {{ $attributes->class(['space-y-6']) }}>
  <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
    <div>
      <h2 class="text-2xl font-bold tracking-tight text-gray-800 dark:text-white sm:text-3xl">
        {{ __('Comments') }}
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ trans_choice(':count customer review|:count customer reviews', $product->reviews->count(), ['count' => $product->reviews->count()]) }}
      </p>
    </div>

    @if ($product->reviews->isNotEmpty())
      <flux:badge color="amber" icon="star">
        {{ number_format((float) $product->reviews->avg('rating'), 1) }} / 5
      </flux:badge>
    @endif
  </div>

  <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    @forelse ($product->reviews as $review)
      <article
              class="flex h-full flex-col gap-4 rounded-xl border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-white/5">
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <h3 class="truncate font-semibold text-gray-800 dark:text-white">
              {{ $review->user->first_name }} {{ $review->user->last_name }}
            </h3>
            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
              {{ $review->user->email }}
            </p>
          </div>

          <flux:badge color="amber" icon="star">
            {{ $review->rating }} / 5
          </flux:badge>
        </div>

        <p class="grow text-sm leading-6 text-gray-600 dark:text-gray-300">
          {{ $review->comment }}
        </p>

        <time
                class="text-xs text-gray-500 dark:text-gray-400"
                datetime="{{ $review->created_at->toISOString() }}"
        >
          {{ $review->created_at->translatedFormat('j F Y') }}
        </time>
      </article>
    @empty
      <div class="rounded-xl border border-dashed border-gray-300 p-8 text-center md:col-span-2 xl:col-span-3 dark:border-white/20">
        <p class="text-sm text-gray-500 dark:text-gray-400">
          {{ __('This product has no comments yet.') }}
        </p>
      </div>
    @endforelse
  </div>
</section>
