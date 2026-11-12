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
                    <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" title="Sign out" style="border:1px solid #dce5ee;background:#fff;color:#17284f;border-radius:8px;padding:9px 12px;cursor:pointer;font-size:12px;font-weight:600;">Sign out</button>
                    </form>

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