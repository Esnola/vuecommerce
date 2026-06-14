@props([
  'links'=>[
    [
      'name'=>'Home',
      'href'=>route('pages.index'),
      'active'=>request()->routeIs('pages.index')
    ],
    [
      'name'=>'Products',
      'href'=>route('products.index'),
      'active'=>request()->routeIs('products.*')
    ],
    [
      'name'=>'Features',
      'href'=>'#',
      'active'=>null
    ],
    [
      'name'=>'Marketplace',
      'href'=>'#',
      'active'=>null
    ],
    [
      'name'=>'Company',
      'href'=>'#',
      'active'=>null
    ]
  ]
])

@php
  if (auth()->user()?->is_admin) {
    $links[] = [
      'name' => 'Orders',
      'href' => route('orders.index'),
      'active' => request()->routeIs('orders.*'),
    ];
  }
@endphp

<div class="flex flex-col sm:flex-row gap-6 ">
  @foreach ($links as $link)
    <a href="{{ $link['href'] }}" {{$link['active'] ? 'aria-current="page"' : '' }}
            @class([
              'text-gray-400 hover:text-gray-300 px-2 py-1 rounded-md text-sm/6 transition-colors duration-300',
              'bg-gray-600 text-white' => $link['active'],
              'hover:bg-gray-600 hover:text-gray-200'
                  => !$link['active'],
          ])
    >
      {{ __($link['name']) }}
    </a>
  @endforeach
</div>
