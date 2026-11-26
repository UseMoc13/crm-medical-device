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

        
{{-- A7105:activities.index --}}
@if(\App\Support\CrmViewAction::allows('activities.index'))
<a href="{{ route('activities.index') }}"
        class="{{ request()->routeIs('activities.*') ? 'active' : '' }}"
        data-label="Activities">
        <span class="ico">✓</span>
        <span class="nav-text">Activities</span>
        </a>
@endif
{{-- /A7105 --}}


        <a href="{{ route('calendar.index') }}" class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}" data-label="Calendar">
            <span class="ico">▣</span>
            <span class="nav-text">Calendar</span>
        </a>

        <a href="{{ route('todos.index') }}"
        class="{{ request()->routeIs('todos.*') ? 'active' : '' }}"
        data-label="To-Do">
            <span class="ico">☑</span>
            <span class="nav-text">To-Do</span>
        </a>

        <a href="{{ route('workspace-notifications.index') }}"
           class="{{ request()->routeIs('workspace-notifications.*') ? 'active' : '' }}"
           data-label="Notifications">
            <span class="ico">@include('workspace-notifications._bell_icon')</span>
            <span class="nav-text">Notifications</span>
        </a>

        <a href="#" data-label="Favorites">
            <span class="ico">★</span>
            <span class="nav-text">Favorites</span>
        </a>

        <a
            href="{{ route('announcements.index') }}"
            class="{{ request()->routeIs('announcements.*') ? 'active' : '' }}"
            data-label="Announcements">

            <span class="ico">▤</span>
            <span class="nav-text">Announcements</span>
        </a>

        {{-- =================================================
             CUSTOMER & LEAD
        ================================================== --}}

        <div class="nav-group">
            Customer & Lead
        </div>

        
{{-- A7105:customers.index --}}
@if(\App\Support\CrmViewAction::allows('customers.index'))
<a
            href="{{ route('customers.index') }}"
            class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"
            data-label="Customers">

            <span class="ico">♙</span>
            <span class="nav-text">Customers</span>

        </a>
@endif
{{-- /A7105 --}}



        
{{-- A7105:contacts.index --}}
@if(\App\Support\CrmViewAction::allows('contacts.index'))
<a
            href="{{ route('contacts.index') }}"
            class="{{ request()->routeIs('contacts.*') ? 'active' : '' }}"
            data-label="Contacts">

            <span class="ico">♧</span>
            <span class="nav-text">Contacts</span>

        </a>
@endif
{{-- /A7105 --}}



        
{{-- A7105:leads.index --}}
@if(\App\Support\CrmViewAction::allows('leads.index'))
<a
            href="{{ route('leads.index') }}"
            class="{{ request()->routeIs('leads.*') ? 'active' : '' }}"
            data-label="Leads">

            <span class="ico">●</span>
            <span class="nav-text">Leads</span>

        </a>
@endif
{{-- /A7105 --}}



        {{-- =================================================
             SALES CRM
        ================================================== --}}

        <div class="nav-group">
            Sales CRM
        </div>

        
{{-- A7105:opportunities.index --}}
@if(\App\Support\CrmViewAction::allows('opportunities.index'))
<a
            href="{{ route('opportunities.index') }}"
            class="{{ request()->routeIs('opportunities.*') ? 'active' : '' }}"
            data-label="opportunities">

            <span class="ico">◈</span>
            <span class="nav-text">Opportunities</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:quotations.index --}}
@if(\App\Support\CrmViewAction::allows('quotations.index'))
<a
            href="{{ route('quotations.index') }}"
            class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}"
            data-label="quotations">

            <span class="ico">▤</span>
            <span class="nav-text">Quotations</span>
        </a>
@endif
{{-- /A7105 --}}


        
        
{{-- A7105:sales-orders.index --}}
@if(\App\Support\CrmViewAction::allows('sales-orders.index'))
<a
            href="{{ route('sales-orders.index') }}"
            class="{{ request()->routeIs('sales-orders.*') ? 'active' : '' }}"
            data-label="Sales Orders"
>
            <span class="ico">▥</span>
            <span class="nav-text">Sales Orders</span>
        </a>
@endif
{{-- /A7105 --}}



        
{{-- A7105:contracts.index --}}
@if(\App\Support\CrmViewAction::allows('contracts.index'))
<a
            href="{{ route('contracts.index') }}"
            class="{{ request()->routeIs('contracts.*') ? 'active' : '' }}"
            data-label="Contracts"
        >
            <span class="ico">▱</span>
            <span class="nav-text">Contracts</span>
        </a>
@endif
{{-- /A7105 --}}


    

        {{-- =================================================
             PRODUCT & INVENTORY
        ================================================== --}}

        <div class="nav-group">
            Product & Inventory
        </div>

        
{{-- A7105:products.index --}}
@if(\App\Support\CrmViewAction::allows('products.index'))
<a
            href="{{ route('products.index') }}"
            class="{{ request()->routeIs('products.*') ? 'active' : '' }}"
            data-label="products">

            <span class="ico">▦</span>
            <span class="nav-text">Products</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:product-categories.index --}}
@if(\App\Support\CrmViewAction::allows('product-categories.index'))
<a
            href="{{ route('product-categories.index') }}"
            class="{{ request()->routeIs('product-categories.*') ? 'active' : '' }}"
            data-label="categories">

            <span class="ico">▤</span>
            <span class="nav-text">Categories</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:brands.index --}}
@if(\App\Support\CrmViewAction::allows('brands.index'))
<a
            href="{{ route('brands.index') }}"
            class="{{ request()->routeIs('brands.*') ? 'active' : '' }}"
            data-label="brands">

            <span class="ico">◆</span>
            <span class="nav-text">Brands</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:warehouses.index --}}
@if(\App\Support\CrmViewAction::allows('warehouses.index'))
<a
            href="{{ route('warehouses.index') }}"
            class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}"
            data-label="warehouses">

            <span class="ico">⌂</span>
            <span class="nav-text">Warehouses</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:inventories.index --}}
@if(\App\Support\CrmViewAction::allows('inventories.index'))
<a
            href="{{ route('inventories.index') }}"
            class="{{ request()->routeIs('inventories.*') ? 'active' : '' }}"
            data-label="inventories">

            <span class="ico">▥</span>
            <span class="nav-text">Inventory</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:stock-movements.index --}}
@if(\App\Support\CrmViewAction::allows('stock-movements.index'))
<a
            href="{{ route('stock-movements.index') }}"
            class="{{ request()->routeIs('stock-movements.*') ? 'active' : '' }}"
            data-label="stock-movements">

            <span class="ico">↕</span>
            <span class="nav-text">Stock Movements</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:serial-numbers.index --}}
@if(\App\Support\CrmViewAction::allows('serial-numbers.index'))
<a
            href="{{ route('serial-numbers.index') }}"
            class="{{ request()->routeIs('serial-numbers.*') ? 'active' : '' }}"
            data-label="serial-numbers">

            <span class="ico">▣</span>
            <span class="nav-text">Serial Numbers</span>
        </a>
@endif
{{-- /A7105 --}}



        {{-- ================================================
             INSTALLATION & WARRANTY
        ================================================== --}}

        <div class="nav-group">
            Installation & Warranty
        </div>

        
{{-- A7105:deliveries.index --}}
@if(\App\Support\CrmViewAction::allows('deliveries.index'))
<a
            href="{{ route('deliveries.index') }}"
            class="{{ request()->routeIs('deliveries.*') ? 'active' : '' }}"
            data-label="Deliveries">

            <span class="ico">➜</span>
            <span class="nav-text">Deliveries</span>

        </a>
@endif
{{-- /A7105 --}}



        
{{-- A7105:installations.index --}}
@if(\App\Support\CrmViewAction::allows('installations.index'))
<a href="{{ route('installations.index') }}"
            class="{{ request()->routeIs('installations.*') ? 'active' : '' }}"
            data-label="Installations">

            <span class="ico">⚙</span>
            <span class="nav-text">Installations</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:warranties.index --}}
@if(\App\Support\CrmViewAction::allows('warranties.index'))
<a href="{{ route('warranties.index') }}"
            class="{{ request()->routeIs('warranties.*') ? 'active' : '' }}"
            data-label="Warranty">

            <span class="ico">✓</span>
            <span class="nav-text">Warranty</span>
        </a>
@endif
{{-- /A7105 --}}



        {{-- =================================================
             SERVICE & MAINTENANCE
        ================================================== --}}

        <div class="nav-group">
            Service & Maintenance
        </div>

        
{{-- A7105:service-tickets.index --}}
@if(\App\Support\CrmViewAction::allows('service-tickets.index'))
<a href="{{ route('service-tickets.index') }}"
            class="{{ request()->routeIs('service-tickets.*') ? 'active' : '' }}"
            data-label="service-tickets">
            
            <span class="ico">⚑</span>
            <span class="nav-text">Service Tickets</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:maintenance.index --}}
@if(\App\Support\CrmViewAction::allows('maintenance.index'))
<a href="{{ route('maintenance.index') }}"
            class="{{ request()->routeIs('maintenance.*') ? 'active' : '' }}"
            data-label="Maintenance">

            <span class="ico">⚙</span>
            <span class="nav-text">Maintenance</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:service-history.index --}}
@if(\App\Support\CrmViewAction::allows('service-history.index'))
<a href="{{ route('service-history.index') }}"
            class="{{ request()->routeIs('service-history.*') ? 'active' : '' }}"
            data-label="Service History">

            <span class="ico">◷</span>
            <span class="nav-text">Service History</span>
        </a>
@endif
{{-- /A7105 --}}



        {{-- =================================================
             BILLING & REPORTING
        ================================================== --}}

        <div class="nav-group">
            Billing & Reporting
        </div>

        
{{-- A7105:invoices.index --}}
@if(\App\Support\CrmViewAction::allows('invoices.index'))
<a href="{{ route('invoices.index') }}"
            class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}"
            data-label="Invoices">

            <span class="ico">▤</span>
            <span class="nav-text">Invoices</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:reports.index --}}
@if(\App\Support\CrmViewAction::allows('reports.index'))
<a href="{{ route('reports.index') }}"
            class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"
            data-label="Reports">

            <span class="ico">▥</span>
            <span class="nav-text">Reports</span>
        </a>
@endif
{{-- /A7105 --}}



        {{-- =================================================
             ADMINISTRATION
        ================================================== --}}

        <div class="nav-group">
            Administration
        </div>

        
{{-- A7105:announcement-management.index --}}
@if(\App\Support\CrmViewAction::allows('announcement-management.index'))
<a
            href="{{ route('announcement-management.index') }}"
            class="{{ request()->routeIs('announcement-management.*') ? 'active' : '' }}"
            data-label="Announcement Management">

            <span class="ico">⚙</span>
            <span class="nav-text">Announcement Management</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:users.index --}}
@if(\App\Support\CrmViewAction::allows('users.index'))
<a
            href="{{ route('users.index') }}"
            class="{{ request()->routeIs('users.*') ? 'active' : '' }}"
            data-label="Users">

            <span class="ico">♙</span>
            <span class="nav-text">Users</span>
        </a>
@endif
{{-- /A7105 --}}


        
{{-- A7105:roles.index --}}
@if(\App\Support\CrmViewAction::allows('roles.index'))
<a
            href="{{ route('roles.index') }}"
            class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"
            data-label="Roles">

            <span class="ico">⚙</span>
            <span class="nav-text">Roles</span>

        </a>
@endif
{{-- /A7105 --}}


        @if(auth()->check() && app(\App\Services\CrmPermissionResolver::class)->isSuperAdmin(auth()->user()))
        
{{-- A7105:organization.index --}}
@if(\App\Support\CrmViewAction::allows('organization.index'))
<a href="{{ route('organization.index') }}"
           class="{{ request()->routeIs('organization.*') ? 'active' : '' }}"
           data-label="Organization">
            <span class="ico">♧</span>
            <span class="nav-text">Organization</span>
        </a>
@endif
{{-- /A7105 --}}

        @endif
        @if(auth()->check() && app(\App\Services\CrmPermissionResolver::class)->isSuperAdmin(auth()->user()))
        
{{-- A7105:teams.index --}}
@if(\App\Support\CrmViewAction::allows('teams.index'))
<a href="{{ route('teams.index') }}"
           class="{{ request()->routeIs('teams.*') ? 'active' : '' }}"
           data-label="Teams">
            <span class="ico">♟</span>
            <span class="nav-text">Teams</span>
        </a>
@endif
{{-- /A7105 --}}

        @endif
        @if(auth()->check() && app(\App\Services\CrmPermissionResolver::class)->isSuperAdmin(auth()->user()))
        
{{-- A7105:branches.index --}}
@if(\App\Support\CrmViewAction::allows('branches.index'))
<a href="{{ route('branches.index') }}"
           class="{{ request()->routeIs('branches.*') ? 'active' : '' }}"
           data-label="Branches">
            <span class="ico">⌂</span>
            <span class="nav-text">Branches</span>
        </a>
@endif
{{-- /A7105 --}}

        @endif


                @if(app(\App\Services\WorkspaceAuditAccess::class)->canView(auth('web')->user()))
        <a href="{{ route('workspace-audit.index') }}"
           class="{{ request()->routeIs('workspace-audit.*') ? 'active' : '' }}"
           data-label="Audit Logs">
            <span class="ico" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v5l3 2"/><circle cx="12" cy="12" r="9"/></svg></span>
            <span class="nav-text">Audit Logs</span>
        </a>
        @endif

        

    </nav>


    {{-- =====================================================
         SIDEBAR FOOTER
    ====================================================== --}}

    <div class="sidebar-footer">

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