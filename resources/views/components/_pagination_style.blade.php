<style>
/* =============================================================
   SHARED PAGINATION — CRM Medical Device
   Applies to default Laravel ->links() globally via pagination view.
   Not a module-specific stylesheet.
   ============================================================= */
nav.crm-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 6px;
    width: 100%;
    margin: 14px 0 4px;
    font-family: inherit;
}
nav.crm-pagination .crm-page-button,
nav.crm-pagination .crm-page-ellipsis {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    box-sizing: border-box;
    width: auto;
    min-width: 36px;
    height: 36px;
    padding: 0 11px;
    border: 1px solid #dce3ed;
    border-radius: 8px;
    background: #ffffff;
    color: #34415c;
    font-size: 12px;
    font-weight: 600;
    line-height: 1;
    white-space: nowrap;
    text-decoration: none;
    box-shadow: none;
}
nav.crm-pagination .crm-page-step { gap: 7px; padding: 0 13px; }
nav.crm-pagination a.crm-page-button:hover,
nav.crm-pagination a.crm-page-button:focus-visible {
    background: #f1f7f6;
    border-color: #15966f;
    color: #117858;
    outline-offset: 2px;
}
nav.crm-pagination .crm-page-current {
    background: #e7f5ee;
    border-color: #15966f;
    color: #117858;
    font-weight: 750;
}
nav.crm-pagination .crm-page-disabled {
    background: #f8fafc;
    border-color: #e7ecf2;
    color: #9aa6b5;
    cursor: not-allowed;
}
nav.crm-pagination .crm-page-ellipsis {
    border-color: transparent;
    background: transparent;
    color: #7b8799;
    padding: 0 4px;
    min-width: 22px;
}
/* Also protect any legacy Laravel SVG paginator from oversized icons. */
nav[role="navigation"][aria-label="Pagination Navigation"] svg,
.pagination svg { width: 16px !important; height: 16px !important; max-width: 16px !important; max-height: 16px !important; vertical-align: middle; flex-shrink: 0; }
@media (max-width: 600px) {
    nav.crm-pagination { justify-content: center; gap: 5px; }
    nav.crm-pagination .crm-page-button { height: 34px; min-width: 34px; padding: 0 9px; font-size: 11px; }
    nav.crm-pagination .crm-page-step { padding: 0 10px; }
}
</style>
