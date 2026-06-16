<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('cookie banner renders with persistent consent actions', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('Cookie Notice')
        ->assertSee('vuecommerce_cookie_consent')
        ->assertSee('cursor-pointer')
        ->assertSee("rememberConsent('accepted')", false)
        ->assertSee("rememberConsent('denied')", false);
});

test('cookie banner is translated into spanish', function () {
    $response = $this->withSession(['locale' => 'es'])->get('/');

    $response->assertSuccessful()
        ->assertSee('Aviso de cookies')
        ->assertSee('Usamos cookies para mejorar tu experiencia en línea.')
        ->assertSee('Rechazar todo')
        ->assertSee('Aceptar todo');
});
