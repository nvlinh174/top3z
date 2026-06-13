<?php

use App\Enums\ActivityEventType;
use App\Enums\ActivitySource;
use App\Filament\Widgets\AdminStatsOverview;
use App\Filament\Widgets\SiteTrafficStatsOverview;
use App\Filament\Widgets\TopPagesTable;
use App\Models\ActivityEvent;
use App\Models\User;
use App\Support\ActivityTracker;
use Livewire\Livewire;

test('home page records a page view event', function () {
    $this->get(route('home'))->assertSuccessful();

    expect(ActivityEvent::query()->where('event_type', ActivityEventType::PageView)->count())->toBe(1)
        ->and(ActivityEvent::query()->first()?->route_name)->toBe('home');
});

test('admin page views are not recorded', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertSuccessful();

    expect(ActivityEvent::query()->count())->toBe(0);
});

test('community search records search and page view events', function () {
    createCommunityPost(['slug' => 'bai-tim-kiem']);

    $this->get(route('community.index', ['q' => 'tim kiem']))
        ->assertSuccessful();

    expect(ActivityEvent::query()->where('event_type', ActivityEventType::Search)->count())->toBe(1)
        ->and(ActivityEvent::query()->where('event_type', ActivityEventType::PageView)->count())->toBe(1)
        ->and(ActivityEvent::query()->where('event_type', ActivityEventType::Search)->first()?->metadata)
        ->toBe(['query' => 'tim kiem']);
});

test('workshop interest records an activity event', function () {
    $workshop = createWorkshopArticle(['slug' => 'workshop-tracked-interest']);

    $this->post(route('workshops.interest.store', $workshop))
        ->assertRedirect(route('workshops.show', $workshop));

    $event = ActivityEvent::query()
        ->where('event_type', ActivityEventType::WorkshopInterest)
        ->first();

    expect($event)->not->toBeNull()
        ->and($event->subject_id)->toBe($workshop->getKey())
        ->and($event->route_name)->toBe('workshops.interest.store');
});

test('community post view records post view event when counted', function () {
    $post = createCommunityPost(['slug' => 'bai-track-view', 'views_count' => 0]);

    $this->get(route('community.show', $post))->assertSuccessful();

    expect(ActivityEvent::query()->where('event_type', ActivityEventType::PostView)->count())->toBe(1)
        ->and(ActivityEvent::query()->where('event_type', ActivityEventType::PageView)->count())->toBe(1);
});

test('android client header is stored as activity source', function () {
    $this->withHeaders(['X-Top3z-Client' => 'android'])
        ->get(route('home'))
        ->assertSuccessful();

    expect(ActivityEvent::query()->first()?->source)->toBe(ActivitySource::Android);
});

test('activity tracker counts unique visitors by session hash', function () {
    $now = now();
    $sharedHash = hash('sha256', 'shared-session');
    $otherHash = hash('sha256', 'other-session');

    foreach ([$sharedHash, $sharedHash, $otherHash] as $hash) {
        ActivityEvent::query()->create([
            'event_type' => ActivityEventType::PageView,
            'session_hash' => $hash,
            'source' => ActivitySource::Web,
            'occurred_at' => $now,
        ]);
    }

    $start = $now->copy()->startOfDay();
    $end = $now->copy()->endOfDay();

    expect(ActivityTracker::countEvents(ActivityEventType::PageView, $start, $end))->toBe(3)
        ->and(ActivityTracker::uniqueVisitors($start, $end))->toBe(2);
});

test('login page records a page view event', function () {
    $this->get(route('login'))->assertSuccessful();

    expect(ActivityEvent::query()
        ->where('event_type', ActivityEventType::PageView)
        ->where('route_name', 'login')
        ->count())->toBe(1);
});

test('activity tracker resolves route labels', function () {
    expect(ActivityTracker::routeLabel('home'))->toBe('Trang chủ')
        ->and(ActivityTracker::routeLabel('community.create'))->toBe('Viết bài mới')
        ->and(ActivityTracker::routeLabel('unknown.route'))->toBe('unknown.route');
});

test('admin dashboard shows site traffic widgets', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin')
        ->assertSuccessful();

    Livewire::test(SiteTrafficStatsOverview::class)
        ->assertSee('Lưu lượng website');

    Livewire::test(TopPagesTable::class)
        ->assertSee('Top trang 7 ngày');

    Livewire::test(AdminStatsOverview::class)
        ->assertSee('Vận hành nội dung');
});

test('user registration records register activity event', function () {
    $this->post('/register', [
        'name' => 'Member Test',
        'email' => 'member-track@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('home'));

    expect(ActivityEvent::query()->where('event_type', ActivityEventType::Register)->count())->toBe(1);
});
