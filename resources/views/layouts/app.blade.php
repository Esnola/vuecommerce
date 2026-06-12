<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('styles')
  @fluxAppearance
  @livewireStyles
  <title>{{  $title ?? "Vuecommerce" }}</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>
<body class="bg-gray-400 text-white font-sans">

<x-header/>

{{ $slot }}


<x-footer/>
@livewireScripts
@fluxScripts
</body>
</html>
