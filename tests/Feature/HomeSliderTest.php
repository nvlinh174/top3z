<?php

use App\Filament\Pages\ManageHomeSlider;
use App\Models\HomeSlide;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('home page shows active slider images above hero', function () {
    $slide = HomeSlide::factory()->withImage()->create([
        'title' => 'Slide hiển thị',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    HomeSlide::factory()->withImage()->inactive()->create([
        'title' => 'Slide ẩn',
        'sort_order' => 2,
    ]);

    $response = $this->get(route('home'))->assertSuccessful();

    expect($response->content())->toContain('aria-label="Slider trang chủ"')
        ->and($response->content())->toContain($slide->imageUrl())
        ->and(substr_count($response->content(), 'object-cover'))->toBe(1);
});

test('home page hides slider section when no active slides exist', function () {
    HomeSlide::factory()->withImage()->inactive()->create();

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertDontSee('x-data="homeSlider', false);
});

test('home page keeps hero section when slider is present', function () {
    HomeSlide::factory()->withImage()->create();

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('Nơi bạn')
        ->assertSee('build');
});

test('admin can access home slider management page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(ManageHomeSlider::getUrl())
        ->assertSuccessful()
        ->assertSee('Slider trang chủ')
        ->assertSee('Thêm ảnh');
});

test('admin can add slides from uploaded images', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(ManageHomeSlider::class)
        ->call('addSlidesFromUploads', [
            UploadedFile::fake()->image('workshop-banner.jpg', 1920, 600),
        ])
        ->assertHasNoFormErrors();

    $slide = HomeSlide::query()->first();

    expect($slide)->not->toBeNull()
        ->and($slide->title)->toBe('Workshop Banner')
        ->and($slide->is_active)->toBeTrue()
        ->and($slide->getFirstMedia('image'))->not->toBeNull();
});

test('admin can toggle slide visibility from table', function () {
    $admin = User::factory()->admin()->create();
    $slide = HomeSlide::factory()->withImage()->create(['is_active' => true]);

    $this->actingAs($admin);

    Livewire::test(ManageHomeSlider::class)
        ->call('updateTableColumnState', 'is_active', (string) $slide->getKey(), false);

    expect($slide->refresh()->is_active)->toBeFalse();
});

test('admin can delete slide from table', function () {
    $admin = User::factory()->admin()->create();
    $slide = HomeSlide::factory()->withImage()->create();

    $this->actingAs($admin);

    Livewire::test(ManageHomeSlider::class)
        ->callTableAction('delete', $slide);

    expect(HomeSlide::query()->find($slide->getKey()))->toBeNull();
});

test('members cannot access home slider management page', function () {
    $member = User::factory()->create();

    $this->actingAs($member)
        ->get(ManageHomeSlider::getUrl())
        ->assertForbidden();
});
