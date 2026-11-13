<?php

use App\Enums\UserRole;
use App\Models\User;

test('all GreenFund screens are available from the shared application layout', function () {
    $operator = User::factory()->create(['role' => UserRole::Operator]);
    $this->actingAs($operator);

    foreach (['dashboard', 'map', 'reports', 'submit', 'tasks', 'inventory', 'maintenance', 'analytics', 'settings'] as $page) {
        $response = $this->get(route('greenfund.screen', ['page' => $page]));

        $response->assertOk()
            ->assertSee('ЖасылҚор')
            ->assertSee(route('greenfund.screen', ['page' => 'reports']), false);
    }
});

test('citizens can access community screens but not staff operations', function () {
    $citizen = User::factory()->create(['role' => UserRole::Citizen]);

    $this->actingAs($citizen)
        ->get(route('greenfund.screen', ['page' => 'reports']))
        ->assertOk()
        ->assertSee('My environmental reports');

    $this->actingAs($citizen)
        ->get(route('greenfund.screen', ['page' => 'tasks']))
        ->assertForbidden();
});

test('staff-only screens require authentication', function () {
    $this->get(route('greenfund.screen', ['page' => 'reports']))
        ->assertRedirect(route('login'));
});

test('unknown GreenFund screens return not found', function () {
    $this->get('/greenfund/not-a-screen')->assertNotFound();
});

test('the application homepage opens the GreenFund dashboard', function () {
    $this->get('/')->assertRedirect('/greenfund/dashboard');
});
