@props([
  'product',
  'favorite' => false,
])

<button
  type="button"
  data-favorite-button
  data-product-id="{{ $product->getKey() }}"
  data-toggle-url="{{ route('favorites.toggle', $product) }}"
  data-is-favorite="{{ $favorite ? 'true' : 'false' }}"
  data-add-label="{{ __('Add to favorites') }}"
  data-remove-label="{{ __('Remove from favorites') }}"
  data-added-label="{{ __('Added to favorites') }}"
  data-removed-label="{{ __('Removed from favorites') }}"
  aria-pressed="{{ $favorite ? 'true' : 'false' }}"
  {{ $attributes->class([
    'group absolute right-0 z-10 inline-flex cursor-pointer items-center justify-center rounded-tr-lg p-3 transition focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 disabled:cursor-wait disabled:opacity-60',
    'text-rose-600' => $favorite,
    'text-gray-400' => ! $favorite,
  ]) }}
>
  <svg
    data-favorite-icon
    viewBox="0 0 24 24"
    fill="{{ $favorite ? 'currentColor' : 'none' }}"
    stroke="currentColor"
    stroke-width="1.5"
    aria-hidden="true"
    class="size-6 shrink-0 transition-colors duration-100 group-hover:fill-rose-600 group-hover:text-rose-600"
  >
    <path
      d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"
      stroke-linecap="round"
      stroke-linejoin="round"
    />
  </svg>
  <span data-favorite-label class="sr-only">
    {{ $favorite ? __('Remove from favorites') : __('Add to favorites') }}
  </span>
  <span
    data-favorite-feedback
    role="status"
    aria-live="polite"
    class="pointer-events-none absolute right-3/4 top-1/2 -translate-y-1/2 whitespace-nowrap rounded-md bg-gray-900 px-3 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition duration-150 dark:bg-white dark:text-gray-900"
  ></span>
</button>
@once
  @vite('resources/js/favorites.js')
@endonce
