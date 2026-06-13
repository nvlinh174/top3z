<?php

use App\Enums\ActivityEventType;
use App\Models\ActivityEvent;
use App\Models\User;
use App\Support\ActivityTracker;

test('login records metadata for member login history', function () {
    $user = User::factory()->create([
        'email' => 'login-history@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->post('/login', [
        'email' => 'login-history@example.com',
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $event = ActivityEvent::query()
        ->where('event_type', ActivityEventType::Login)
        ->where('user_id', $user->id)
        ->first();

    expect($event)->not->toBeNull()
        ->and($event->metadata)->toHaveKeys(['ip_hash', 'user_agent'])
        ->and($event->route_name)->toBe('login');
});

test('admin can view member login history page', function () {
    $admin = User::factory()->admin()->create();
    $member = User::factory()->create();

    ActivityEvent::query()->create([
        'event_type' => ActivityEventType::Login,
        'user_id' => $member->id,
        'session_hash' => hash('sha256', 'login-session'),
        'source' => 'web',
        'route_name' => 'login',
        'metadata' => ['user_agent' => 'PHPUnit'],
        'occurred_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get('/admin/member-login-histories')
        ->assertSuccessful()
        ->assertSee($member->name)
        ->assertSee('PHPUnit');
});

test('activity tracker caches dashboard summary', function () {
    ActivityEvent::query()->create([
        'event_type' => ActivityEventType::PageView,
        'session_hash' => hash('sha256', 'cache-test'),
        'source' => 'web',
        'occurred_at' => now(),
    ]);

    $start = now()->startOfDay();
    $end = now()->endOfDay();

    expect(ActivityTracker::summaryForPeriod($start, $end)['page_views'])->toBe(1);

    ActivityEvent::query()->create([
        'event_type' => ActivityEventType::PageView,
        'session_hash' => hash('sha256', 'cache-test-2'),
        'source' => 'web',
        'occurred_at' => now(),
    ]);

    expect(ActivityTracker::summaryForPeriod($start, $end)['page_views'])->toBe(1);

    ActivityTracker::flushDashboardCache($start, $end);

    expect(ActivityTracker::summaryForPeriod($start, $end)['page_views'])->toBe(2);
});
