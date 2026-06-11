<?php

test('blocks search indexing when SEO_ALLOW_INDEXING is false', function () {
    config(['seo.allow_indexing' => false]);

    $response = $this->get(route('home'));

    $response
        ->assertSuccessful()
        ->assertSee('<meta name="robots" content="noindex, nofollow, noarchive">', false)
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');

    $this->get('/robots.txt')
        ->assertSuccessful()
        ->assertSee('Disallow: /')
        ->assertDontSee('Allow: /', false);
});

test('allows search indexing when SEO_ALLOW_INDEXING is true', function () {
    config(['seo.allow_indexing' => true]);

    $response = $this->get(route('home'));

    $response
        ->assertSuccessful()
        ->assertDontSee('noindex', false)
        ->assertHeaderMissing('X-Robots-Tag');

    $this->get('/robots.txt')
        ->assertSuccessful()
        ->assertSee('Allow: /');
});
