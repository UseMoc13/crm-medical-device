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

    <div class="app">

        @include('components.sidebar')

        <main class="main">

            <div class="topbar">

                <button
                    class="icon-btn"
                    id="sidebarToggle"
                    type="button"
                    aria-label="Toggle Sidebar"
                >
                    ☰
                </button>

                <div class="search">
                    🔍
                    <input
                        type="text"
                        placeholder="Search..."
                    >
                </div>

                <div class="top-actions">

                    <button class="icon-btn" type="button">
                        🔔
                    </button>

                    <div class="profile">

                        <div class="avatar">
                            A
                        </div>

                        <div>
                            <strong>Administrator</strong>
                            <small>Administrator</small>
                        </div>

                    </div>

                </div>

            </div>

            <section class="content">

                @yield('content')

            </section>

        </main>

    </div>

</body>
</html>