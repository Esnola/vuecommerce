<?php
  
  if (!function_exists('profile_avatar')) {
    function profile_avatar($name = null, $photoUrl = null): string
    {
      // Si viene un modelo u objeto, detectar el nombre
      if (is_object($name)) {
        $name = $name->first_name
          ?? $name->name
          ?? $name->full_name
          ?? '';
      }
      
      // Si viene un array
      if (is_array($name)) {
        $name = $name['first_name']
          ?? $name['name']
          ?? $name['full_name']
          ?? '';
      }
      
      // Asegurar que $name sea string
      $name = trim((string)$name);
      
      // Si hay foto → devolverla
      if ($photoUrl) {
        return '<img src="' . $photoUrl . '" class="w-12 h-12 rounded-full object-cover" />';
      }
      
      // Iniciales seguras
      $initials = collect(explode(' ', $name))
        ->filter() // elimina strings vacíos
        ->map(fn($p) => mb_substr($p, 0, 1))
        ->join('');
      
      // Si sigue vacío → poner "?"
      if ($initials === '') {
        $initials = '?';
      }
      
      $hash = crc32($name ?: 'default');
      $r = max(80, ($hash & 0xFF0000) >> 16);
      $g = max(80, ($hash & 0x00FF00) >> 8);
      $b = max(80, ($hash & 0x0000FF));
      
      $color = "rgb($r, $g, $b)";
      
      return '
        <svg class="w-10 h-10 rounded-full" xmlns="http://www.w3.org/2000/svg">
            <rect width="100%" height="100%" rx="999" fill="' . $color . '"/>
            <text x="50%" y="50%" font-size="18" fill="white" dy=".35em"
                text-anchor="middle" font-family="sans-serif">
                ' . $initials . '
            </text>
        </svg>';
    }
  }
