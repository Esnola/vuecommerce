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

<div class="flex flex-col sm:flex-row gap-6 ">
  @foreach ($links as $link)
    <a href="{{ $link['href'] }}"
       class="{{ $link['active']  ? 'bg-gray-800 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white' }} rounded-md  px-3 py-2 text-sm font-medium"
    >
      {{ __($link['name']) }}
    </a>
  @endforeach
</div>
