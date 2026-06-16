<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')
  @fluxAppearance
  @livewireStyles
  <title>{{  $title ?? "Vuecommerce" }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>
<body
        class="text-white font-sans dark:bg-gray-800 dark:text-gray-100"
        data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
        data-favorites-sync-url="{{ auth()->check() ? route('favorites.sync') : '' }}"
>

<x-header/>

{{ $slot }}
<x-cookies/>
<x-footer/>

@livewireScripts
@fluxScripts

</body>
</html>
