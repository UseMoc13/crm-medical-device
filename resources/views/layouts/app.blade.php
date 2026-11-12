<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'CRM Medical Device')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')

<style>
.crm-toast-area{position:fixed;left:50%;right:auto;top:20px;transform:translateX(-50%);z-index:50000;display:grid;gap:10px;width:min(390px,calc(100vw - 36px));pointer-events:none}
.crm-toast{pointer-events:auto;display:flex;align-items:flex-start;gap:11px;background:#fff;border:1px solid #dce5ee;border-left:4px solid #15966f;border-radius:10px;padding:14px;box-shadow:0 14px 36px rgba(23,40,79,.16);animation:crmToastIn .25s ease}
.crm-toast-error{border-left-color:#d14b52}.crm-toast-symbol{display:grid;place-items:center;flex:0 0 25px;height:25px;border-radius:50%;background:#e7f6ef;color:#128165;font-weight:800}
.crm-toast-error .crm-toast-symbol{background:#fff0f0;color:#bb3841}.crm-toast div{flex:1;min-width:0}.crm-toast strong{display:block;color:#17284f;font-size:13px}.crm-toast p{margin:4px 0 0;color:#52617a;font-size:12px;line-height:1.55;overflow-wrap:anywhere}
.crm-toast-close{border:none;background:transparent;color:#8590a0;font-size:21px;cursor:pointer;padding:0 4px}.crm-toast.is-leaving{opacity:0;transform:translateY(-10px);transition:.25s}
@keyframes crmToastIn{from{opacity:0;transform:translateY(-12px)}to{opacity:1;transform:translateY(0)}}
/* Always-visible sign-out button for CRM authentication */
.crm-signout-form{margin:0;display:flex;align-items:center;flex-shrink:0}
.crm-signout-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 13px;border:1px solid #d4dee9;background:#fff;border-radius:9px;color:#17284f;font:600 12px/1.3 inherit;font-family:inherit;cursor:pointer;white-space:nowrap;transition:background .18s,border-color .18s,color .18s}
.crm-signout-btn:hover,.crm-signout-btn:focus-visible{background:#fff1f2;border-color:#f0b7bc;color:#b42335}
.crm-signout-btn:focus-visible{outline:2px solid #2ba7a0;outline-offset:2px}
@media(max-width:520px){.crm-signout-btn{padding:9px 10px}.crm-signout-label{display:none}.top-actions{gap:10px}}
</style>

</head>

<body>

    <div class="app" id="app">

        @include('components.sidebar')

        <main class="main">

            <header class="topbar">

                <button
                    class="icon-btn"
                    id="sidebarToggle"
                    type="button"
                    aria-label="Toggle Sidebar"
                    aria-expanded="true"
                >
                    ☰
                </button>



                <div class="top-actions">

                    <button
                        class="icon-btn"
                        type="button"
                        aria-label="Notifications"
                    >
                        🔔
                    </button>

                    <div class="profile">

                        <div class="avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? "U", 0, 1)) }}
                        </div>

                        <div class="profile-info">
                            <strong>{{ auth()->user()->name ?? "User" }}</strong>
                            <small>{{ auth()->user()?->roles()->wherePivot("is_primary", true)->first()?->role_name ?? "Staff" }}</small>
                        </div>

                    </div>
                    {{-- POST logout with CSRF; never use a GET link for session termination. --}}
                    @auth
                    <form action="{{ route('logout') }}" method="POST" class="crm-signout-form">
                        @csrf
                        <button type="submit" class="crm-signout-btn" title="Sign out of CRM" aria-label="Sign out of CRM">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10 17l5-5-5-5M15 12H3"/>
                                <path d="M12 3h6a3 3 0 0 1 3 3v12a3 3 0 0 1-3 3h-6"/>
                            </svg>
                            <span class="crm-signout-label">Sign out</span>
                        </button>
                    </form>
                    @endauth

                </div>

            </header>

            <section class="content">

                @yield('content')

            </section>

        </main>

    </div>


    @if(session('success') || session('error') || $errors->any())
    <div class="crm-toast-area" id="crmToastArea" role="status" aria-live="polite">
        @if(session('success'))
            <div class="crm-toast crm-toast-success"><span class="crm-toast-symbol">✓</span><div><strong>Success</strong><p>{{ session('success') }}</p></div><button type="button" aria-label="Dismiss notification" class="crm-toast-close">×</button></div>
        @endif
        @if(session('error'))
            <div class="crm-toast crm-toast-error"><span class="crm-toast-symbol">!</span><div><strong>Action could not be completed</strong><p>{{ session('error') }}</p></div><button type="button" aria-label="Dismiss notification" class="crm-toast-close">×</button></div>
        @endif
        @if($errors->any())
            <div class="crm-toast crm-toast-error"><span class="crm-toast-symbol">!</span><div><strong>Please check your input</strong><p>{{ $errors->first() }}</p></div><button type="button" aria-label="Dismiss notification" class="crm-toast-close">×</button></div>
        @endif
    </div>
    @endif
    <script>
    document.querySelectorAll('.crm-toast').forEach(toast => {
        const dismiss = () => { toast.classList.add('is-leaving'); setTimeout(() => toast.remove(), 260); };
        toast.querySelector('.crm-toast-close')?.addEventListener('click', dismiss);
        setTimeout(dismiss, 4800);
    });
    </script>

</body>

</html>