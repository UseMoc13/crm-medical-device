<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title', 'CRM Medical Device')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

                <div class="search">
                    <span>🔍</span>

                    <input
                        type="text"
                        placeholder="Search customer, lead, product, ticket..."
                    >
                </div>

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
                            A
                        </div>

                        <div class="profile-info">
                            <strong>Administrator</strong>
                            <small>Administrator</small>
                        </div>

                    </div>

                </div>

            </header>

            <section class="content">

                @yield('content')

            </section>

        </main>

    </div>

</body>

</html>