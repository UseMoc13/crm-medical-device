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

    @php
    $crmViewer = auth()->user();
    $crmAcl = app(\App\Services\CrmPermissionResolver::class);
    $crmSuperAdmin = $crmAcl->isSuperAdmin($crmViewer);
    $crmAdmin = $crmAcl->hasRole($crmViewer, 'Admin');
@endphp

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

        <a href="#" data-label="Notifications">
            <span class="ico">🔔</span>
            <span class="nav-text">Notifications</span>
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

@if($crmAcl->allows($crmViewer, 'customer_lead.access'))
        <div class="nav-group">
            Customer & Lead
        </div>

        @if($crmAcl->allows($crmViewer, 'customer_lead.access'))
<a
            href="{{ route('customers.index') }}"
            class="{{ request()->routeIs('customers.*') ? 'active' : '' }}"
            data-label="Customers">

            <span class="ico">♙</span>
            <span class="nav-text">Customers</span>

        </a>
@endif


        @if($crmAcl->allows($crmViewer, 'customer_lead.access'))
<a
            href="{{ route('contacts.index') }}"
            class="{{ request()->routeIs('contacts.*') ? 'active' : '' }}"
            data-label="Contacts">

            <span class="ico">♧</span>
            <span class="nav-text">Contacts</span>

        </a>
@endif


        @if($crmAcl->allows($crmViewer, 'customer_lead.access'))
<a
            href="{{ route('leads.index') }}"
            class="{{ request()->routeIs('leads.*') ? 'active' : '' }}"
            data-label="Leads">

            <span class="ico">●</span>
            <span class="nav-text">Leads</span>

        </a>
@endif



@endif
        {{-- =================================================
             SALES CRM
        ================================================== --}}

@if($crmAcl->allows($crmViewer, 'sales_crm.access'))
        <div class="nav-group">
            Sales CRM
        </div>

        @if($crmAcl->allows($crmViewer, 'sales_crm.access'))
<a
            href="{{ route('opportunities.index') }}"
            class="{{ request()->routeIs('opportunities.*') ? 'active' : '' }}"
            data-label="opportunities">

            <span class="ico">◈</span>
            <span class="nav-text">Opportunities</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'sales_crm.access'))
<a
            href="{{ route('quotations.index') }}"
            class="{{ request()->routeIs('quotations.*') ? 'active' : '' }}"
            data-label="quotations">

            <span class="ico">▤</span>
            <span class="nav-text">Quotations</span>
        </a>
@endif

        
        @if($crmAcl->allows($crmViewer, 'sales_crm.access'))
<a
            href="{{ route('sales-orders.index') }}"
            class="{{ request()->routeIs('sales-orders.*') ? 'active' : '' }}"
            data-label="Sales Orders"
>
            <span class="ico">▥</span>
            <span class="nav-text">Sales Orders</span>
        </a>
@endif


        @if($crmAcl->allows($crmViewer, 'sales_crm.access'))
<a
            href="{{ route('contracts.index') }}"
            class="{{ request()->routeIs('contracts.*') ? 'active' : '' }}"
            data-label="Contracts"
        >
            <span class="ico">▱</span>
            <span class="nav-text">Contracts</span>
        </a>
@endif

    


@endif
        {{-- =================================================
             PRODUCT & INVENTORY
        ================================================== --}}

@if(($crmAcl->allows($crmViewer, 'catalog.access') || $crmAcl->allows($crmViewer, 'inventory.access')))
        <div class="nav-group">
            Product & Inventory
        </div>

        @if($crmAcl->allows($crmViewer, 'catalog.access'))
<a
            href="{{ route('products.index') }}"
            class="{{ request()->routeIs('products.*') ? 'active' : '' }}"
            data-label="products">

            <span class="ico">▦</span>
            <span class="nav-text">Products</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'catalog.access'))
<a
            href="{{ route('product-categories.index') }}"
            class="{{ request()->routeIs('product-categories.*') ? 'active' : '' }}"
            data-label="categories">

            <span class="ico">▤</span>
            <span class="nav-text">Categories</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'catalog.access'))
<a
            href="{{ route('brands.index') }}"
            class="{{ request()->routeIs('brands.*') ? 'active' : '' }}"
            data-label="brands">

            <span class="ico">◆</span>
            <span class="nav-text">Brands</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'inventory.access'))
<a
            href="{{ route('warehouses.index') }}"
            class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}"
            data-label="warehouses">

            <span class="ico">⌂</span>
            <span class="nav-text">Warehouses</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'inventory.access'))
<a
            href="{{ route('inventories.index') }}"
            class="{{ request()->routeIs('inventories.*') ? 'active' : '' }}"
            data-label="inventories">

            <span class="ico">▥</span>
            <span class="nav-text">Inventory</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'inventory.access'))
<a
            href="{{ route('stock-movements.index') }}"
            class="{{ request()->routeIs('stock-movements.*') ? 'active' : '' }}"
            data-label="stock-movements">

            <span class="ico">↕</span>
            <span class="nav-text">Stock Movements</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'inventory.access'))
<a
            href="{{ route('serial-numbers.index') }}"
            class="{{ request()->routeIs('serial-numbers.*') ? 'active' : '' }}"
            data-label="serial-numbers">

            <span class="ico">▣</span>
            <span class="nav-text">Serial Numbers</span>
        </a>
@endif



@endif
        {{-- ================================================
             INSTALLATION & WARRANTY
        ================================================== --}}

@if(($crmAcl->allows($crmViewer, 'delivery.access') || $crmAcl->allows($crmViewer, 'installation.access') || $crmAcl->allows($crmViewer, 'service.access')))
        <div class="nav-group">
            Installation & Warranty
        </div>

        @if($crmAcl->allows($crmViewer, 'delivery.access'))
<a
            href="{{ route('deliveries.index') }}"
            class="{{ request()->routeIs('deliveries.*') ? 'active' : '' }}"
            data-label="Deliveries">

            <span class="ico">➜</span>
            <span class="nav-text">Deliveries</span>

        </a>
@endif


        @if($crmAcl->allows($crmViewer, 'installation.access'))
<a href="{{ route('installations.index') }}"
            class="{{ request()->routeIs('installations.*') ? 'active' : '' }}"
            data-label="Installations">

            <span class="ico">⚙</span>
            <span class="nav-text">Installations</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'service.access'))
<a href="{{ route('warranties.index') }}"
            class="{{ request()->routeIs('warranties.*') ? 'active' : '' }}"
            data-label="Warranty">

            <span class="ico">✓</span>
            <span class="nav-text">Warranty</span>
        </a>
@endif



@endif
        {{-- =================================================
             SERVICE & MAINTENANCE
        ================================================== --}}

@if($crmAcl->allows($crmViewer, 'service.access'))
        <div class="nav-group">
            Service & Maintenance
        </div>

        @if($crmAcl->allows($crmViewer, 'service.access'))
<a href="{{ route('service-tickets.index') }}"
            class="{{ request()->routeIs('service-tickets.*') ? 'active' : '' }}"
            data-label="service-tickets">
            
            <span class="ico">⚑</span>
            <span class="nav-text">Service Tickets</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'service.access'))
<a href="{{ route('maintenance.index') }}"
            class="{{ request()->routeIs('maintenance.*') ? 'active' : '' }}"
            data-label="Maintenance">

            <span class="ico">⚙</span>
            <span class="nav-text">Maintenance</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'service.access'))
<a href="{{ route('service-history.index') }}"
            class="{{ request()->routeIs('service-history.*') ? 'active' : '' }}"
            data-label="Service History">

            <span class="ico">◷</span>
            <span class="nav-text">Service History</span>
        </a>
@endif



@endif
        {{-- =================================================
             BILLING & REPORTING
        ================================================== --}}

@if(($crmAcl->allows($crmViewer, 'billing.access') || $crmAcl->allows($crmViewer, 'reporting.access')))
        <div class="nav-group">
            Billing & Reporting
        </div>

        @if($crmAcl->allows($crmViewer, 'billing.access'))
<a href="{{ route('invoices.index') }}"
            class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}"
            data-label="Invoices">

            <span class="ico">▤</span>
            <span class="nav-text">Invoices</span>
        </a>
@endif

        @if($crmAcl->allows($crmViewer, 'reporting.access'))
<a href="{{ route('reports.index') }}"
            class="{{ request()->routeIs('reports.*') ? 'active' : '' }}"
            data-label="Reports">

            <span class="ico">▥</span>
            <span class="nav-text">Reports</span>
        </a>
@endif



@endif
        {{-- =================================================
             ADMINISTRATION
        ================================================== --}}

@if(($crmSuperAdmin || $crmAdmin))
        <div class="nav-group">
            Administration
        </div>


        @if($crmAcl->allows($crmViewer, 'announcements.manage'))
<a
            href="{{ route('announcement-management.index') }}"
            class="{{ request()->routeIs('announcement-management.*') ? 'active' : '' }}"
            data-label="Announcement Management">

            <span class="ico">⚙</span>
            <span class="nav-text">Announcement Management</span>
        </a>
@endif

        @if(($crmSuperAdmin || $crmAdmin))
<a
            href="{{ route('users.index') }}"
            class="{{ request()->routeIs('users.*') ? 'active' : '' }}"
            data-label="Users">

            <span class="ico">♙</span>
            <span class="nav-text">Users</span>
        </a>
@endif

        @if($crmSuperAdmin)
<a
            href="{{ route('roles.index') }}"
            class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"
            data-label="Roles">

            <span class="ico">⚙</span>
            <span class="nav-text">Roles</span>

        </a>
@endif

        <a href="#" data-label="Branches">
            <span class="ico">⌂</span>
            <span class="nav-text">Branches</span>
        </a>


        <a href="#" data-label="Audit Logs">
            <span class="ico">◉</span>
            <span class="nav-text">Audit Logs</span>
        </a>

        


@endif
    </nav>


    {{-- =====================================================
         SIDEBAR FOOTER
    ====================================================== --}}

    <div class="sidebar-footer">

        <a href="#" data-label="Settings">
            <span class="ico">⚙</span>
            <span class="nav-text">Settings</span>
        </a>

        {{-- Logout must use POST + CSRF. Keep this control in the existing sidebar footer. --}}
        @auth
            <form method="POST" action="{{ route('logout') }}" class="crm-sidebar-logout-form">
                @csrf
                <button type="submit" class="crm-sidebar-logout-button" data-label="Logout" aria-label="Logout">
                    <span class="ico" aria-hidden="true">⇥</span>
                    <span class="nav-text">Logout</span>
                </button>
            </form>
        @endauth

    </div>

</aside>
{{-- Match sidebar footer links without modifying shared CSS. --}}
<style>
.sidebar-footer .crm-sidebar-logout-form{margin:0;padding:0}
.sidebar-footer .crm-sidebar-logout-button{
    display:flex;align-items:center;gap:11px;width:100%;padding:10px 11px;
    margin:2px 0;border:0;border-radius:9px;background:transparent;
    color:#dce5fb;font:inherit;font-size:13px;text-align:left;cursor:pointer;
    transition:background .18s ease,color .18s ease,padding .25s ease;
}
.sidebar-footer .crm-sidebar-logout-button:hover{background:rgba(43,167,160,.20);color:#fff}
.sidebar-footer .crm-sidebar-logout-button:focus-visible{outline:2px solid #2ba7a0;outline-offset:2px}
.sidebar-footer .crm-sidebar-logout-button .ico{flex-shrink:0}
</style>



{{-- Mobile Overlay --}}
<div
    class="sidebar-overlay"
    id="sidebarOverlay">
</div>