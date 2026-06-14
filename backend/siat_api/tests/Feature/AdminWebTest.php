<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_page_is_accessible(): void
    {
        $this->seed();

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard Admin Akademik')
            ->assertSee('Mahasiswa')
            ->assertSee('Dosen');
    }

    public function test_admin_menu_pages_are_accessible(): void
    {
        $this->seed();

        $this->get('/admin/students')
            ->assertOk()
            ->assertSee('Data Mahasiswa');

        $this->get('/admin/lecturers')
            ->assertOk()
            ->assertSee('Data Dosen');

        $this->get('/admin/approvals')
            ->assertOk()
            ->assertSee('Approval');

        $this->get('/admin/audit-logs')
            ->assertOk()
            ->assertSee('Audit Log');
    }
}
