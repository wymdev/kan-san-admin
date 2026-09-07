<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminScreensTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_lists_and_forms_render_with_shared_components(): void
    {
        $this->seed(PermissionTableSeeder::class);
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::all());
        $this->actingAs($user);
        $this->withoutExceptionHandling();
        foreach ([
            '/customers', '/customers/create', '/tickets', '/tickets/create', '/purchases',
            '/roles', '/users', '/drawinfos', '/draw_results', '/daily-quotes', '/daily-quotes/create',
            '/announcements', '/app-banners', '/app-versions', '/activity-logs', '/login-activities',
            '/secondary-tickets', '/secondary-transactions', '/secondary-sales/dashboard',
            '/analytics', '/analytics/customers', '/tickets?search=123', '/customers?search=TEST',
            '/activity-logs?search=127', '/login-activities?search=127', '/purchases?search=123',
            '/secondary-transactions?search=123', '/secondary-tickets?search=123',
        ] as $url) {
            $response = $this->get($url);
            $response->assertOk();
            $response->assertSee('admin-theme', false);
            $response->assertDontSee('<x-ui.', false);
        }
    }
}
