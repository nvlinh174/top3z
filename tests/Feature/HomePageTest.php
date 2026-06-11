<?php

test('home page renders makerspace layout', function () {
    $response = $this->get(route('home'));

    $response
        ->assertSuccessful()
        ->assertSee('Nơi bạn')
        ->assertSee('build')
        ->assertSee('Xem lịch workshop')
        ->assertDontSee('tabler', false);
});

test('placeholder routes render within layout', function () {
    $this->get(route('workshops.index'))
        ->assertSuccessful()
        ->assertSee('Lịch workshop');

    $this->get(route('community.index'))
        ->assertSuccessful()
        ->assertSee('Cộng đồng');
});
