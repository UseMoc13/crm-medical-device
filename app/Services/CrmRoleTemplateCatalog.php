<?php

declare(strict_types=1);

namespace App\Services;

/**
 * A7.10.6: UI presets for existing A6 page/workflow grants.
 * Not a role or permission migration. Template selection is an explicit edit action.
 * Scope grants are NOT generated here; NULL must never be promoted to 'all'.
 */
final class CrmRoleTemplateCatalog
{
    /**
     * Page codes: V=view, C=create, U=update, D=delete.
     * All page/action keys must exist in CrmRoleAccessCatalog::PAGES/ACTIONS.
     * Workflows must exist in CrmWorkflowAccess::ACTIONS and require the page action.
     *
     * @var array<string,array{label:string,description:string,pages:array<string,string>,workflows:list<string>,names?:list<string>}>
     */
    private const TEMPLATES = [
        // Existing preset IDs retained, avoiding saved-option breakage.
        'admin' => [
            'label' => 'Announcement Manager (legacy Admin)',
            'description' => 'Manage announcements only. Does not grant Super Admin access or organization/role administration.',
            'pages' => ['announcement_management'=>'VCUD'], 'workflows'=>[],
            'names'=>['Admin','Announcement Manager'],
        ],
        'sales' => [
            'label' => 'Sales Executive · CRM Cycle',
            'description' => 'Manage sales leads, opportunities, quotations and conversion to sales orders; no delete for approved documents.',
            'pages' => ['customers'=>'VCU','contacts'=>'VCU','leads'=>'VCU','activities'=>'VCU','opportunities'=>'VCU','quotations'=>'VCU','sales_orders'=>'VC','contracts'=>'VCU','products'=>'V','product_categories'=>'V','brands'=>'V'],
            'workflows'=>[], 'names'=>['Sales','Sales Executive'],
        ],
        'warehouse' => [
            'label' => 'Warehouse Supervisor',
            'description' => 'Maintain product catalog and warehouses, record stock movements and manage serials. Destructive stock operations are not auto-granted.',
            'pages'=>['products'=>'VCUD','product_categories'=>'VCUD','brands'=>'VCUD','warehouses'=>'VCUD','inventories'=>'V','stock_movements'=>'VC','serial_numbers'=>'VCU'],
            'workflows'=>[], 'names'=>['Warehouse','Warehouse Supervisor'],
        ],
        'delivery_staff' => [
            'label' => 'Delivery & Logistics Staff',
            'description' => 'Prepare dispatch and update delivery or installation progress; no deletion of operational records.',
            'pages'=>['products'=>'V','serial_numbers'=>'V','inventories'=>'V','sales_orders'=>'V','deliveries'=>'VCU','installations'=>'VU'],
            'workflows'=>['deliveries.status','installations.status','installations.checklist'],
            'names'=>['Delivery Staff','Delivery'],
        ],
        'technician' => [
            'label' => 'Field Service Technician',
            'description' => 'Work assigned installations, service tickets, parts and maintenance. No delete of service history or completed work.',
            'pages'=>['products'=>'V','serial_numbers'=>'V','installations'=>'VU','warranties'=>'V','service_tickets'=>'VCU','maintenance'=>'VCU','service_history'=>'V'],
            'workflows'=>['installations.checklist','service_tickets.status','service_tickets.parts_create','service_tickets.parts_update','maintenance.status'],
            'names'=>['Technician','Field Technician'],
        ],
        'finance' => [
            'label' => 'Finance & Billing',
            'description' => 'Manage invoices and payment status; view orders, contracts and reports. Reporting privacy policy still applies.',
            'pages'=>['sales_orders'=>'V','contracts'=>'V','invoices'=>'VCU','reports'=>'V'],
            'workflows'=>['invoices.status'], 'names'=>['Finance'],
        ],
        'management' => [
            'label' => 'Management · Read Only',
            'description' => 'Read operations and reports, without create, edit or delete. Financial report visibility remains subject to privacy policy.',
            'pages'=>['customers'=>'V','leads'=>'V','opportunities'=>'V','quotations'=>'V','sales_orders'=>'V','products'=>'V','inventories'=>'V','deliveries'=>'V','installations'=>'V','service_tickets'=>'V','maintenance'=>'V','reports'=>'V'],
            'workflows'=>[], 'names'=>['Management','Manager'],
        ],
        // New role-specific presets: least privilege by default.
        'customer_service' => [
            'label' => 'Customer Service',
            'description' => 'Support customers, contacts and service intake without deleting records or closing technician workflows.',
            'pages'=>['customers'=>'VU','contacts'=>'VCU','leads'=>'VCU','activities'=>'VCU','products'=>'V','warranties'=>'V','service_tickets'=>'VCU','service_history'=>'V'],
            'workflows'=>[], 'names'=>['Customer Service'],
        ],
        'sales_support' => [
            'label' => 'Sales Support',
            'description' => 'Prepare leads, quotations and opportunities; view converted orders, but cannot delete records.',
            'pages'=>['customers'=>'VCU','contacts'=>'VCU','leads'=>'VCU','activities'=>'VCU','opportunities'=>'VCU','quotations'=>'VCU','sales_orders'=>'V','products'=>'V','product_categories'=>'V','brands'=>'V'],
            'workflows'=>[], 'names'=>['Sales Support'],
        ],
        'sales_manager' => [
            'label' => 'Sales Manager',
            'description' => 'Review and advance sales pipeline, quotations, orders and contracts, without broad deletion or finance reporting by default.',
            'pages'=>['customers'=>'VCU','contacts'=>'VCU','leads'=>'VCU','activities'=>'VCU','opportunities'=>'VCU','quotations'=>'VCU','sales_orders'=>'VC','contracts'=>'VCU','products'=>'V','product_categories'=>'V','brands'=>'V'],
            'workflows'=>[], 'names'=>['Sales Manager'],
        ],
        'lead_specialist' => [
            'label' => 'Lead Qualification Specialist',
            'description' => 'Manage contacts, leads and qualification activities; opportunity details are read only.',
            'pages'=>['customers'=>'V','contacts'=>'VCU','leads'=>'VCU','activities'=>'VCU','opportunities'=>'V','products'=>'V'],
            'workflows'=>[], 'names'=>['Lead Specialist','Lead Qualification Specialist'],
        ],
        'catalog_manager' => [
            'label' => 'Product Catalog Manager',
            'description' => 'Maintain products, categories and brands. Inventory ledger and warehouse balances are read only.',
            'pages'=>['products'=>'VCUD','product_categories'=>'VCUD','brands'=>'VCUD','inventories'=>'V','warehouses'=>'V'],
            'workflows'=>[], 'names'=>['Catalog Manager','Product Catalog Manager'],
        ],
        'warehouse_operator' => [
            'label' => 'Warehouse Operator',
            'description' => 'View warehouse stock, register stock movements and serial numbers; cannot delete product master data.',
            'pages'=>['products'=>'V','product_categories'=>'V','brands'=>'V','warehouses'=>'V','inventories'=>'V','stock_movements'=>'VC','serial_numbers'=>'VCU'],
            'workflows'=>[], 'names'=>['Warehouse Operator'],
        ],
        'inventory_controller' => [
            'label' => 'Inventory Controller',
            'description' => 'Reconcile stock movements and track serials without destructive master-data rights.',
            'pages'=>['products'=>'V','warehouses'=>'V','inventories'=>'V','stock_movements'=>'VC','serial_numbers'=>'VU'],
            'workflows'=>[], 'names'=>['Inventory Controller'],
        ],
        'logistics_coordinator' => [
            'label' => 'Logistics Coordinator',
            'description' => 'Coordinate order fulfilment, delivery items and shipment statuses; no delete.',
            'pages'=>['products'=>'V','serial_numbers'=>'V','warehouses'=>'V','inventories'=>'V','sales_orders'=>'V','deliveries'=>'VCU','installations'=>'V'],
            'workflows'=>['deliveries.status'], 'names'=>['Logistics Coordinator'],
        ],
        'installation_coordinator' => [
            'label' => 'Installation Coordinator',
            'description' => 'Schedule and complete installation checklists; authorize warranty activation from eligible installations.',
            'pages'=>['products'=>'V','serial_numbers'=>'V','deliveries'=>'V','installations'=>'VCU','warranties'=>'V','service_history'=>'V'],
            'workflows'=>['installations.status','installations.checklist','installations.activate_warranty'],
            'names'=>['Installation Coordinator'],
        ],
        'service_coordinator' => [
            'label' => 'Service & Maintenance Coordinator',
            'description' => 'Coordinate tickets and maintenance scheduling, plus workflow updates, without deleting service history.',
            'pages'=>['products'=>'V','serial_numbers'=>'V','installations'=>'V','warranties'=>'VU','service_tickets'=>'VCU','maintenance'=>'VCU','service_history'=>'V'],
            'workflows'=>['service_tickets.status','service_tickets.parts_create','service_tickets.parts_update','maintenance.status'],
            'names'=>['Service Coordinator','Service & Maintenance Coordinator'],
        ],
        'warranty_coordinator' => [
            'label' => 'Warranty Coordinator',
            'description' => 'Maintain warranty records and review installations, serials and service history; no historical deletion.',
            'pages'=>['products'=>'V','serial_numbers'=>'V','installations'=>'V','warranties'=>'VCU','service_tickets'=>'V','service_history'=>'V'],
            'workflows'=>[], 'names'=>['Warranty Coordinator'],
        ],
        'billing_clerk' => [
            'label' => 'Billing Clerk / Accounts Receivable',
            'description' => 'Create and edit invoices without payment-status transition or deleting financial records.',
            'pages'=>['customers'=>'V','sales_orders'=>'V','contracts'=>'V','invoices'=>'VCU'],
            'workflows'=>[], 'names'=>['Billing Clerk','Accounts Receivable'],
        ],
        'finance_manager' => [
            'label' => 'Finance Manager',
            'description' => 'Review finance reports and manage invoice lifecycle. Reporting privacy still needs stakeholder approval.',
            'pages'=>['customers'=>'V','sales_orders'=>'V','contracts'=>'V','invoices'=>'VCUD','reports'=>'V'],
            'workflows'=>['invoices.status'], 'names'=>['Finance Manager'],
        ],
        'qa_auditor' => [
            'label' => 'Device Quality Auditor · Read Only',
            'description' => 'Inspect device serials, installation, warranty, maintenance and ticket history, without changing records.',
            'pages'=>['products'=>'V','serial_numbers'=>'V','deliveries'=>'V','installations'=>'V','warranties'=>'V','service_tickets'=>'V','maintenance'=>'V','service_history'=>'V'],
            'workflows'=>[], 'names'=>['Quality Auditor','Device Quality Auditor'],
        ],
        'crm_readonly' => [
            'label' => 'CRM Observer · Read Only',
            'description' => 'Read operational CRM screens without finance reports, admin configuration, or write actions.',
            'pages'=>['customers'=>'V','contacts'=>'V','leads'=>'V','activities'=>'V','opportunities'=>'V','quotations'=>'V','sales_orders'=>'V','contracts'=>'V','products'=>'V','brands'=>'V','product_categories'=>'V','warehouses'=>'V','inventories'=>'V','stock_movements'=>'V','serial_numbers'=>'V','deliveries'=>'V','installations'=>'V','warranties'=>'V','service_tickets'=>'V','maintenance'=>'V','service_history'=>'V'],
            'workflows'=>[], 'names'=>['CRM Observer','CRM Read Only'],
        ],
    ];

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::TEMPLATES);
    }

    /** @return array<string,string> */
    public static function labels(): array
    {
        $out = [];
        foreach (self::TEMPLATES as $key => $data) $out[$key] = $data['label'];
        return $out;
    }

    /** @return array<string,string> */
    public static function descriptions(): array
    {
        $out = [];
        foreach (self::TEMPLATES as $key => $data) $out[$key] = $data['description'];
        return $out;
    }

    /** @return array<string,array<string,list<string>>> */
    public static function allPages(): array
    {
        $out = [];
        foreach (self::keys() as $key) $out[$key] = self::pages($key);
        return $out;
    }

    /** @return array<string,list<string>> */
    public static function pages(string $key): array
    {
        $raw = self::TEMPLATES[$key]['pages'] ?? [];
        $mapping = ['V'=>'view','C'=>'create','U'=>'update','D'=>'delete'];
        $out = [];
        foreach ($raw as $slug => $coded) {
            $actions = [];
            foreach (str_split($coded) as $letter) {
                if (!isset($mapping[$letter])) throw new \LogicException('Unknown template action code.');
                $actions[] = $mapping[$letter];
            }
            $out[$slug] = array_values(array_unique($actions));
        }
        return $out;
    }

    /** @return list<string> */
    public static function workflows(string $key): array
    {
        return self::TEMPLATES[$key]['workflows'] ?? [];
    }

    public static function forRoleName(string $roleName): string
    {
        $needle = strtolower(trim($roleName));
        foreach (self::TEMPLATES as $key => $definition) {
            foreach ($definition['names'] ?? [] as $name) {
                if ($needle === strtolower($name)) return $key;
            }
        }
        return '';
    }
}
