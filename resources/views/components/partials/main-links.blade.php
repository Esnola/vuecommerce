@props([
  'links' => [
    [
      'name' => 'Home',
      'href' => route('pages.index'),
      'active' => request()->routeIs('pages.index'),
    ],
    [
      'name' => 'Products',
      'href' => route('products.index'),
      'active' => request()->routeIs('products.*'),
    ],
    [
      'name' => 'Features',
      'href' => '#',
      'active' => null,
    ],
    [
      'name' => 'Marketplace',
      'href' => '#',
      'active' => null,
    ],
    [
      'name' => 'Company',
      'href' => '#',
      'active' => null,
    ],
  ],
])

@php
  if (auth()->check()) {
    $links[] = [
      'name' => 'Dashboard',
      'href' => route('dashboard'),
      'active' => request()->routeIs('dashboard'),
    ];
  }
@endphp

<div class="flex flex-col gap-6 sm:flex-row">
  @foreach ($links as $link)
    <a
      href="{{ $link['href'] }}"
      {{ $link['active'] ? 'aria-current="page"' : '' }}
      @class([
        'rounded-md px-2 py-1 text-sm/6 text-gray-400 transition-colors duration-300 hover:text-gray-300',
        'bg-gray-600 text-white' => $link['active'],
        'hover:bg-gray-600 hover:text-gray-200' => ! $link['active'],
      ])
    >
      {{ __($link['name']) }}
    </a>
  @endforeach
</div>
