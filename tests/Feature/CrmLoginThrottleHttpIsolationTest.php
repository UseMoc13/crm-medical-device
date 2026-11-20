<?php

namespace Tests\Feature;

use App\Services\CrmLoginThrottlePolicy as Policy;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PDO;
use Tests\TestCase;

/**
 * A7.7.2 end-to-end login guard tests using only a NEW isolated SQLite :memory:
 * connection. Does not migrate, seed or modify the configured CRM database.
 */
final class CrmLoginThrottleHttpIsolationTest extends TestCase
{
    private function withMemoryUsers(Closure $callback): void
    {
        self::assertTrue(app()->environment('testing'), 'Must only run with APP_ENV=testing');
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite is required for isolated login tests.');
        }

        $name = 'crm_a772_isolated_sqlite';
        $original = DB::getDefaultConnection();
        $existing = config('database.connections.'.$name);
        config()->set('database.connections.'.$name, [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        DB::purge($name);
        DB::setDefaultConnection($name);
        try {
            Schema::connection($name)->create('users', function (Blueprint $table): void {
                $table->uuid('user_id')->primary();
                $table->string('email')->unique();
                $table->string('password');
                $table->string('status', 20);
                $table->string('name')->nullable();
                $table->timestamps();
            });
            self::assertSame(':memory:', (string) DB::connection()->getDatabaseName());
            $callback();
        } finally {
            Auth::guard('web')->logout();
            Auth::forgetGuards();
            DB::setDefaultConnection($original);
            DB::disconnect($name);
            DB::purge($name);
            config()->set('database.connections.'.$name, $existing);
        }
        self::assertSame($original, DB::getDefaultConnection());
    }

    private function createUser(string $email, string $status = 'active'): void
    {
        DB::table('users')->insert([
            'user_id' => (string) Str::uuid(),
            'name' => 'Isolated CRM login test',
            'email' => $email,
            'password' => Hash::make('ValidPassword-A772!'),
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_invalid_password_counts_both_keys_and_success_clears_them(): void
    {
        $this->withMemoryUsers(function (): void {
            $email = 'a772-'.strtolower((string) Str::uuid()).'@example.test';
            $this->createUser($email);
            $keys = Policy::keys($email, '127.0.0.1');
            try {
                $this->post('/login', ['email' => $email, 'password' => 'incorrect'])
                    ->assertSessionHasErrors('email');
                self::assertSame(1, RateLimiter::attempts($keys['pair']));
                self::assertSame(1, RateLimiter::attempts($keys['identity']));

                $this->post('/login', ['email' => $email, 'password' => 'ValidPassword-A772!'])
                    ->assertRedirect(route('dashboard'));
                self::assertTrue(Auth::guard('web')->check());
                self::assertSame(0, RateLimiter::attempts($keys['pair']));
                self::assertSame(0, RateLimiter::attempts($keys['identity']));
            } finally {
                RateLimiter::clear($keys['pair']);
                RateLimiter::clear($keys['identity']);
            }
        });
    }

    public function test_identity_limit_rejects_even_correct_password(): void
    {
        $this->withMemoryUsers(function (): void {
            $email = 'a772-'.strtolower((string) Str::uuid()).'@example.test';
            $this->createUser($email);
            $keys = Policy::keys($email, '127.0.0.1');
            try {
                for ($i = 0; $i < Policy::IDENTITY_LIMIT; $i++) {
                    RateLimiter::hit($keys['identity'], Policy::IDENTITY_DECAY_SECONDS);
                }
                $this->post('/login', ['email' => $email, 'password' => 'ValidPassword-A772!'])
                    ->assertSessionHasErrors('email');
                self::assertFalse(Auth::guard('web')->check());
                self::assertSame(0, RateLimiter::attempts($keys['pair']));
                self::assertSame(Policy::IDENTITY_LIMIT, RateLimiter::attempts($keys['identity']));
            } finally {
                RateLimiter::clear($keys['pair']);
                RateLimiter::clear($keys['identity']);
            }
        });
    }

    public function test_inactive_account_is_logged_out_and_counts_both_limits(): void
    {
        $this->withMemoryUsers(function (): void {
            $email = 'a772-'.strtolower((string) Str::uuid()).'@example.test';
            $this->createUser($email, 'inactive');
            $keys = Policy::keys($email, '127.0.0.1');
            try {
                $this->post('/login', ['email' => $email, 'password' => 'ValidPassword-A772!'])
                    ->assertRedirect(route('login'))
                    ->assertSessionHasErrors('email');
                self::assertFalse(Auth::guard('web')->check());
                self::assertSame(1, RateLimiter::attempts($keys['pair']));
                self::assertSame(1, RateLimiter::attempts($keys['identity']));
            } finally {
                RateLimiter::clear($keys['pair']);
                RateLimiter::clear($keys['identity']);
            }
        });
    }
}
