<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\CrmPermissionResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureCrmOrganizationAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $actor = $request->user('web');
        abort_unless($actor instanceof User && strtolower((string) $actor->status) === 'active', 403);
        abort_unless(app(CrmPermissionResolver::class)->isSuperAdmin($actor), 403,
            'Only Super Admin can configure organization structure.');

        return $next($request);
    }
}
