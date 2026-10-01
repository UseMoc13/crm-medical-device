<aside class="sidebar">

    {{-- Mobile Close Button --}}
    <button
        type="button"
        class="sidebar-close"
        id="sidebarClose"
        aria-label="Close sidebar">
        ×
    </button>


    {{-- =====================================================
         BRAND
    ====================================================== --}}

    <div class="brand">

        <img
            src="{{ asset('assets/logo.png') }}"
            alt="CRM Medical Device">

        <h3>CRM Medical Device</h3>

        <small>Customer Relationship Management</small>

    </div>


    {{-- =====================================================
         NAVIGATION
    ====================================================== --}}

    <nav class="nav">


        {{-- =================================================
             OVERVIEW
        ================================================== --}}

        <div class="nav-group">
            Overview
        </div>

        <a
            href="{{ route('dashboard') }}"
            class="{{ request()->routeIs('dashboard') ? 'active' : '' }}"
            data-label="Dashboard">

            <span class="ico">⌂</span>

            <span class="nav-text">
                Dashboard
            </span>

        </a>


        {{-- =================================================
             GENERAL & WORKSPACE
        ================================================== --}}

        <div class="nav-group">
            General & Workspace
        </div>

        <a href="#" data-label="Activities">
            <span class="ico">✓</span>
            <span class="nav-text">Activities</span>
        </a>

        <a href="#" data-label="Calendar">
            <span class="ico">▣</span>
            <span class="nav-text">Calendar</span>
        </a>

        <a href="#" data-label="To-Do">
            <span class="ico">☑</span>
            <span class="nav-text">To-Do</span>
        </a>

        <a href="#" data-label="Favorites">
            <span class="ico">★</span>
            <span class="nav-text">Favorites</span>
        </a>


        {{-- =================================================
             CUSTOMER & LEAD
        ================================================== --}}

        <div class="nav-group">
            Customer & Lead
        </div>

        <a
            href="{{ route('customers.index') }}"
            class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"
            data-label="Customers">

            <span class="ico">♙</span>
            <span class="nav-text">Customers</span>

        </a>


        <a
            href="{{ route('contacts.index') }}"
            class="{{ request()->routeIs('contacts.*') ? 'active' : '' }}"
            data-label="Contacts">

            <span class="ico">♧</span>
            <span class="nav-text">Contacts</span>

        </a>


        <a
            href="{{ route('leads.index') }}"
            class="{{ request()->routeIs('leads.*') ? 'active' : '' }}"
            data-label="Leads">

            <span class="ico">●</span>
            <span class="nav-text">Leads</span>

        </a>


        {{-- =================================================
             SALES CRM
        ================================================== --}}

        <div class="nav-group">
            Sales CRM
        </div>

        <a
            href="{{ route('opportunities.index') }}"
            class="{{ request()->routeIs('opportunities.*') ? 'active' : '' }}"
            data-label="opportunities">

            <span class="ico">◈</span>
            <span class="nav-text">Opportunities</span>
        </a>

        <a href="#" data-label="Quotations">
            <span class="ico">▤</span>
            <span class="nav-text">Quotations</span>
        </a>

        <a href="#" data-label="Sales Orders">
            <span class="ico">▥</span>
            <span class="nav-text">Sales Orders</span>
        </a>

        <a href="#" data-label="Contracts">
            <span class="ico">▱</span>
            <span class="nav-text">Contracts</span>
        </a>


        {{-- =================================================
             PRODUCT & INVENTORY
        ================================================== --}}

        <div class="nav-group">
            Product & Inventory
        </div>

        <a href="#" data-label="Products">
            <span class="ico">▦</span>
            <span class="nav-text">Products</span>
        </a>

        <a href="#" data-label="Categories">
            <span class="ico">▤</span>
            <span class="nav-text">Categories</span>
        </a>

        <a href="#" data-label="Brands">
            <span class="ico">◆</span>
            <span class="nav-text">Brands</span>
        </a>

        <a href="#" data-label="Warehouses">
            <span class="ico">⌂</span>
            <span class="nav-text">Warehouses</span>
        </a>

        <a href="#" data-label="Inventory">
            <span class="ico">▥</span>
            <span class="nav-text">Inventory</span>
        </a>

        <a href="#" data-label="Stock Movements">
            <span class="ico">↕</span>
            <span class="nav-text">Stock Movements</span>
        </a>

        <a href="#" data-label="Serial Numbers">
            <span class="ico">▣</span>
            <span class="nav-text">Serial Numbers</span>
        </a>


        {{-- =================================================
             INSTALLATION & WARRANTY
        ================================================== --}}

        <div class="nav-group">
            Installation & Warranty
        </div>

        <a href="#" data-label="Deliveries">
            <span class="ico">➜</span>
            <span class="nav-text">Deliveries</span>
        </a>

        <a href="#" data-label="Installations">
            <span class="ico">⚙</span>
            <span class="nav-text">Installations</span>
        </a>

        <a href="#" data-label="Warranty">
            <span class="ico">✓</span>
            <span class="nav-text">Warranty</span>
        </a>


        {{-- =================================================
             SERVICE & MAINTENANCE
        ================================================== --}}

        <div class="nav-group">
            Service & Maintenance
        </div>

        <a href="#" data-label="Service Tickets">
            <span class="ico">⚑</span>
            <span class="nav-text">Service Tickets</span>
        </a>

        <a href="#" data-label="Maintenance">
            <span class="ico">⚙</span>
            <span class="nav-text">Maintenance</span>
        </a>

        <a href="#" data-label="Service History">
            <span class="ico">◷</span>
            <span class="nav-text">Service History</span>
        </a>


        {{-- =================================================
             BILLING & REPORTING
        ================================================== --}}

        <div class="nav-group">
            Billing & Reporting
        </div>

        <a href="#" data-label="Invoices">
            <span class="ico">▤</span>
            <span class="nav-text">Invoices</span>
        </a>

        <a href="#" data-label="Reports">
            <span class="ico">▥</span>
            <span class="nav-text">Reports</span>
        </a>


        {{-- =================================================
             ADMINISTRATION
        ================================================== --}}

        <div class="nav-group">
            Administration
        </div>

        <a
            href="{{ route('users.index') }}"
            class="{{ request()->routeIs('users.*') ? 'active' : '' }}"
            data-label="Users">

            <span class="ico">♙</span>
            <span class="nav-text">Users</span>
        </a>

        <a
            href="{{ route('roles.index') }}"
            class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"
            data-label="Roles">

            <span class="ico">⚙</span>
            <span class="nav-text">Roles</span>

        </a>

        <a href="#" data-label="Branches">
            <span class="ico">⌂</span>
            <span class="nav-text">Branches</span>
        </a>

        <a href="#" data-label="Notifications">
            <span class="ico">🔔</span>
            <span class="nav-text">Notifications</span>
        </a>

        <a href="#" data-label="Audit Logs">
            <span class="ico">◉</span>
            <span class="nav-text">Audit Logs</span>
        </a>

    </nav>


    {{-- =====================================================
         SIDEBAR FOOTER
    ====================================================== --}}

    <div class="sidebar-footer">

        <a href="#" data-label="Settings">
            <span class="ico">⚙</span>
            <span class="nav-text">Settings</span>
        </a>

        <a href="#" data-label="Logout">
            <span class="ico">⇥</span>
            <span class="nav-text">Logout</span>
        </a>

    </div>

</aside>


{{-- Mobile Overlay --}}
<div
    class="sidebar-overlay"
    id="sidebarOverlay">
</div>