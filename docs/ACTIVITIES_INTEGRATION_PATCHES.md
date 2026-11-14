# A7.2 — Minimal integration changes (if local web.php/sidebar differ)

**Do not overwrite a newer local routes/web.php or sidebar.** The included full files are reference replacements built from the most recent A6.13.1 routes patch and an older November 3 sidebar snapshot.

To merge manually:

## routes/web.php
Append exactly once at the end, outside groups:

```php
require __DIR__ . '/activities.php';
```

## resources/views/components/sidebar.blade.php
Replace only the Activities anchor:

```blade
<a href="{{ route('activities.index') }}" class="{{ request()->routeIs('activities.*') ? 'active' : '' }}" data-label="Activities">
```

**Calendar and To-Do remain href=#** because those Laravel modules are not yet implemented.

The Activities routes reject unauthenticated requests with HTTP 403. Never remove this gate in development to get a page visible. Implement or connect real login first, and test with authenticated users.
