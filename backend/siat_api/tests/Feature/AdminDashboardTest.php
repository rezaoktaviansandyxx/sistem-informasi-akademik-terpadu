<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    public function test_admin_dashboard_route_is_accessible(): void
    {
        $this->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard Admin Akademik');
    }
}
