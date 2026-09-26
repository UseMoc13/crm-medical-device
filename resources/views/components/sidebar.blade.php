<aside class="sidebar">

    <button
        type="button"
        class="sidebar-close"
        id="sidebarClose"
        aria-label="Close sidebar"
    >
        ×
    </button>

    <div class="brand">

        <img
            src="{{ asset('assets/logo.png') }}"
            alt="CRM Medical Device"
        >

        <h3>CRM Medical Device</h3>

        <small>Customer Relationship Management</small>

    </div>

    <nav class="nav">

        {{-- Overview --}}
        <div class="nav-group">
            Overview
        </div>

        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
        >
            <span class="ico">⌂</span>
            Dashboard
        </a>


        {{-- General & Workspace --}}
        <div class="nav-group">
            General & Workspace
        </div>

        <a href="#">
            <span class="ico">✓</span>
            Activities
        </a>

        <a href="#">
            <span class="ico">▣</span>
            Calendar
        </a>

        <a href="#">
            <span class="ico">☑</span>
            To-Do
        </a>

        <a href="#">
            <span class="ico">★</span>
            Favorites
        </a>


        {{-- Customer & Lead --}}
        <div class="nav-group">
            Customer & Lead
        </div>

        <a href="#">
            <span class="ico">♙</span>
            Customers
        </a>

        <a href="#">
            <span class="ico">♧</span>
            Contacts
        </a>

        <a href="#">
            <span class="ico">●</span>
            Leads
        </a>


        {{-- Sales CRM --}}
        <div class="nav-group">
            Sales CRM
        </div>

        <a href="#">
            <span class="ico">◈</span>
            Opportunities
        </a>

        <a href="#">
            <span class="ico">▤</span>
            Quotations
        </a>

        <a href="#">
            <span class="ico">▥</span>
            Sales Orders
        </a>

        <a href="#">
            <span class="ico">▱</span>
            Contracts
        </a>


        {{-- Product & Inventory --}}
        <div class="nav-group">
            Product & Inventory
        </div>

        <a href="#">
            <span class="ico">▦</span>
            Products
        </a>

        <a href="#">
            <span class="ico">▤</span>
            Categories
        </a>

        <a href="#">
            <span class="ico">◆</span>
            Brands
        </a>

        <a href="#">
            <span class="ico">⌂</span>
            Warehouses
        </a>

        <a href="#">
            <span class="ico">▥</span>
            Inventory
        </a>

        <a href="#">
            <span class="ico">↕</span>
            Stock Movements
        </a>

        <a href="#">
            <span class="ico">▣</span>
            Serial Numbers
        </a>


        {{-- Installation & Warranty --}}
        <div class="nav-group">
            Installation & Warranty
        </div>

        <a href="#">
            <span class="ico">➜</span>
            Deliveries
        </a>

        <a href="#">
            <span class="ico">⚙</span>
            Installations
        </a>

        <a href="#">
            <span class="ico">✓</span>
            Warranty
        </a>


        {{-- Service & Maintenance --}}
        <div class="nav-group">
            Service & Maintenance
        </div>

        <a href="#">
            <span class="ico">⚑</span>
            Service Tickets
        </a>

        <a href="#">
            <span class="ico">⚙</span>
            Maintenance
        </a>

        <a href="#">
            <span class="ico">◷</span>
            Service History
        </a>


        {{-- Billing & Reporting --}}
        <div class="nav-group">
            Billing & Reporting
        </div>

        <a href="#">
            <span class="ico">▤</span>
            Invoices
        </a>

        <a href="#">
            <span class="ico">▥</span>
            Reports
        </a>


        {{-- Administration --}}
        <div class="nav-group">
            Administration
        </div>

        <a href="#">
            <span class="ico">♙</span>
            Users
        </a>

        <a href="#">
            <span class="ico">⚙</span>
            Roles & Permissions
        </a>

        <a href="#">
            <span class="ico">⌂</span>
            Branches
        </a>

        <a href="#">
            <span class="ico">🔔</span>
            Notifications
        </a>

        <a href="#">
            <span class="ico">◉</span>
            Audit Logs
        </a>

    </nav>

</aside>

<div
    class="sidebar-overlay"
    id="sidebarOverlay"
></div>