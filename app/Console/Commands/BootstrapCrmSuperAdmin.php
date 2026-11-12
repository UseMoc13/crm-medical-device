<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Throwable;

/** One-time LOCAL-ONLY privileged bootstrap, no default credentials. */
class BootstrapCrmSuperAdmin extends Command
{
    protected $signature = 'crm:super-admin:bootstrap {--email= : Email for a NEW account}';
    protected $description = 'Provision the first protected Super Admin on a local CRM installation.';

    public function handle(): int
    {
        if (! app()->environment('local')) {
            $this->error('This command is restricted to APP_ENV=local. Use an audited operations process for other environments.');
            return self::FAILURE;
        }

        $email = Str::lower(trim((string) ($this->option('email') ?: $this->ask('Super Admin email (new account)'))));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
            $this->error('Enter a valid email address (max 150 chars).');
            return self::FAILURE;
        }

        if (DB::table('users')->whereRaw('LOWER(email) = ?', [$email])->exists()) {
            $this->error('This email already belongs to an account. Bootstrap requires a NEW user, and never modifies an existing password.');
            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Display name', 'CRM Super Administrator'));
        if ($name === '' || mb_strlen($name) > 150) {
            $this->error('Display name must contain 1–150 characters.');
            return self::FAILURE;
        }

        $password = (string) $this->secret('New password (hidden, min 8 chars)');
        $confirmation = (string) $this->secret('Confirm password (hidden)');
        if (strlen($password) < 8 || strlen($password) > 200 || ! hash_equals($password, $confirmation)) {
            $this->error('Password must be 8–200 characters and match confirmation.');
            return self::FAILURE;
        }
        unset($confirmation);

        if (! $this->confirm('Create the first protected Super Admin account?', false)) {
            $this->warn('No changes were made.');
            return self::SUCCESS;
        }

        try {
            DB::transaction(function () use ($email, $name, $password): void {
                // Locking serializes competing bootstraps on this local database.
                DB::select('SELECT pg_advisory_xact_lock(20261112, 190000)');
                DB::table('roles')->lockForUpdate()->get();
                $existing = DB::table('user_roles as ur')
                    ->join('roles as r', 'r.role_id', '=', 'ur.role_id')
                    ->whereRaw('LOWER(r.role_name) = ?', ['super admin'])
                    ->exists();
                if ($existing) {
                    throw new \RuntimeException('Super Admin already assigned. Refusing repeat bootstrap.');
                }

                if (DB::table('users')->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                    throw new \RuntimeException('Email already used.');
                }

                $roles = DB::table('roles')->whereRaw('LOWER(role_name) = ?', ['super admin'])->get();
                if ($roles->count() > 1) {
                    throw new \RuntimeException('Ambiguous Super Admin role names. Resolve duplicates before bootstrap.');
                }
                if ($roles->isEmpty()) {
                    $roleId = (string) Str::uuid();
                    DB::table('roles')->insert([
                        'role_id' => $roleId,
                        'role_name' => 'Super Admin',
                        'description' => 'Protected system administrator — all permissions, workflow validation still applies.',
                        'is_system' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $role = $roles->first();
                    if (! $role->is_system) {
                        throw new \RuntimeException('Unprotected Super Admin-named role exists. Audit it before provisioning.');
                    }
                    $roleId = $role->role_id;
                }

                $userId = (string) Str::uuid();
                DB::table('users')->insert([
                    'user_id' => $userId,
                    'role_id' => $roleId, // A1/A2 legacy compatibility
                    'branch_id' => null,
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make($password),
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('user_roles')->insert([
                    'user_role_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'role_id' => $roleId,
                    'is_primary' => true,
                    'assigned_by' => null,
                    'created_at' => now(),
                ]);
                // No password, token, or secret is logged.
                DB::table('audit_logs')->insert([
                    'audit_log_id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'action' => 'INSERT',
                    'table_name' => 'user_roles',
                    'record_id' => $userId,
                    'old_value' => null,
                    'new_value' => 'Initial protected Super Admin bootstrap via local CLI',
                    'created_at' => now(),
                ]);
            }, 3);
        } catch (Throwable $e) {
            $this->error('Bootstrap failed: ' . $e->getMessage());
            return self::FAILURE;
        } finally {
            unset($password);
        }

        $this->info('Protected Super Admin created successfully. Use your email and privately chosen password to log in.');
        return self::SUCCESS;
    }
}
