<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CrmPermissionResolver;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\TestCase;

/**
 * A7.7.7: full web stack, no DB fixtures, migrations, uploads, or actual CRM rows.
 * Checks response caching on paths that short-circuit before controller execution.
 */
final class CrmSecurityResponseBoundaryHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::assertSame('testing', app()->environment());
        self::assertSame('sqlite', config('database.default'));
        self::assertSame(':memory:', (string) config('database.connections.sqlite.database'));
    }

    private function assertPrivate(TestResponse $response): void
    {
        $cache = strtolower((string) $response->baseResponse->headers->get('Cache-Control'));
        self::assertStringContainsString('private', $cache);
        self::assertStringContainsString('no-store', $cache);
        self::assertSame('nosniff', $response->baseResponse->headers->get('X-Content-Type-Options'));
    }

    public function test_guest_get_redirect_still_has_no_store(): void
    {
        $response = $this->get(route('products.index'));
        $response->assertRedirect(route('login'));
        $this->assertPrivate($response);
    }

    public function test_guest_json_denial_still_has_no_store(): void
    {
        $response = $this->getJson(route('products.index'));
        $response->assertUnauthorized();
        $this->assertPrivate($response);
    }

    public function test_guest_unsafe_post_is_not_replayed_and_no_store(): void
    {
        $response = $this->post(route('products.store'), []);
        $response->assertRedirect(route('login'));
        $this->assertPrivate($response);
    }

    public function test_rbac_forbidden_response_still_has_no_store(): void
    {
        $user = new User();
        $user->setAttribute('user_id', (string) Str::uuid());
        $user->setAttribute('status', 'active');
        $this->actingAs($user, 'web');
        $resolver = Mockery::mock(CrmPermissionResolver::class);
        $resolver->shouldReceive('allows')->andReturn(false);
        app()->instance(CrmPermissionResolver::class, $resolver);

        $response = $this->get(route('products.index'));
        $response->assertForbidden();
        $this->assertPrivate($response);
    }

    public function test_inactive_user_redirect_still_has_no_store(): void
    {
        $user = new User();
        $user->setAttribute('user_id', (string) Str::uuid());
        $user->setAttribute('status', 'inactive');
        $this->actingAs($user, 'web');
        $response = $this->get(route('products.index'));
        $response->assertRedirect(route('login'));
        $this->assertPrivate($response);
    }

    public function test_anonymous_login_page_is_not_cached(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
        $this->assertPrivate($response);
    }
}
