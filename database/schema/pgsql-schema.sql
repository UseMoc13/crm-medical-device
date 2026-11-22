\set ON_ERROR_STOP on
-- CRM baseline A7.9.3.2. PostgreSQL 18. Empty public schema only.
-- Laravel removes its empty migration repository before loading this file.
BEGIN;
SET LOCAL lock_timeout = '5s';
DO $crm_baseline_guard$
BEGIN
    IF EXISTS (SELECT 1 FROM pg_catalog.pg_class c
               JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
               WHERE n.nspname = 'public') THEN
        RAISE EXCEPTION 'CRM baseline requires an empty public schema. No existing objects will be replaced.';
    END IF;
END;
$crm_baseline_guard$;
-- START ORIGINAL SCHEMA
--
-- PostgreSQL database dump
--


-- Dumped from database version 18.6
-- Dumped by pg_dump version 18.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.activities (
    activity_id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid NOT NULL,
    customer_id uuid,
    lead_id uuid,
    opportunity_id uuid,
    activity_type character varying(50) NOT NULL,
    subject character varying(150) NOT NULL,
    description text,
    activity_date timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    created_by uuid,
    updated_by uuid,
    ends_at timestamp(0) without time zone
);


--
-- Name: announcement_attachments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.announcement_attachments (
    attachment_id uuid DEFAULT gen_random_uuid() NOT NULL,
    announcement_id uuid NOT NULL,
    original_name character varying(255) NOT NULL,
    file_path character varying(500) NOT NULL,
    mime_type character varying(150),
    file_size bigint DEFAULT '0'::bigint NOT NULL,
    media_type character varying(30) DEFAULT 'document'::character varying NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    uploaded_by uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: announcement_media; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.announcement_media (
    media_id uuid DEFAULT gen_random_uuid() NOT NULL,
    announcement_id uuid NOT NULL,
    original_name character varying(255) NOT NULL,
    file_path character varying(500) NOT NULL,
    mime_type character varying(150),
    file_size bigint DEFAULT '0'::bigint NOT NULL,
    media_type character varying(30) DEFAULT 'image'::character varying NOT NULL,
    alt_text character varying(255),
    sort_order integer DEFAULT 0 NOT NULL,
    uploaded_by uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: announcements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.announcements (
    announcement_id uuid DEFAULT gen_random_uuid() NOT NULL,
    title character varying(150) NOT NULL,
    slug character varying(180),
    category character varying(100),
    summary text,
    content text NOT NULL,
    thumbnail_path character varying(500),
    status character varying(20) DEFAULT 'draft'::character varying NOT NULL,
    is_pinned boolean DEFAULT false NOT NULL,
    pin_order integer,
    scheduled_at timestamp(0) without time zone,
    published_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_by uuid,
    updated_by uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    thumbnail_fit character varying(10) DEFAULT 'cover'::character varying NOT NULL,
    thumbnail_position character varying(10) DEFAULT 'center'::character varying NOT NULL,
    thumbnail_background_style character varying(15) DEFAULT 'solid'::character varying NOT NULL,
    thumbnail_background character varying(7) DEFAULT '#17284f'::character varying NOT NULL,
    thumbnail_gradient_start character varying(7) DEFAULT '#17284f'::character varying NOT NULL,
    thumbnail_gradient_end character varying(7) DEFAULT '#2ba7a0'::character varying NOT NULL
);


--
-- Name: audit_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.audit_logs (
    audit_log_id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    action character varying(30) NOT NULL,
    table_name character varying(100) NOT NULL,
    record_id uuid,
    old_value text,
    new_value text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: branches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.branches (
    branch_id uuid DEFAULT gen_random_uuid() NOT NULL,
    branch_code character varying(30) NOT NULL,
    branch_name character varying(100) NOT NULL,
    address text,
    phone character varying(30),
    status character varying(20) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: brands; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.brands (
    brand_id uuid DEFAULT gen_random_uuid() NOT NULL,
    brand_name character varying(100) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: contacts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.contacts (
    contact_id uuid DEFAULT gen_random_uuid() NOT NULL,
    customer_id uuid NOT NULL,
    name character varying(150) NOT NULL,
    "position" character varying(100),
    department character varying(100),
    phone character varying(30),
    email character varying(150),
    contact_type character varying(50),
    is_primary boolean NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: contracts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.contracts (
    contract_id uuid DEFAULT gen_random_uuid() NOT NULL,
    sales_order_id uuid NOT NULL,
    contract_number character varying(50) NOT NULL,
    start_date date NOT NULL,
    end_date date,
    contract_type character varying(50),
    status character varying(30) NOT NULL,
    document_path character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: crm_team_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.crm_team_members (
    team_member_id uuid DEFAULT gen_random_uuid() NOT NULL,
    team_id uuid NOT NULL,
    user_id uuid NOT NULL,
    member_role character varying(16) DEFAULT 'member'::character varying NOT NULL,
    joined_at timestamp(0) without time zone,
    left_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: crm_teams; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.crm_teams (
    team_id uuid DEFAULT gen_random_uuid() NOT NULL,
    team_code character varying(32) NOT NULL,
    team_name character varying(150) NOT NULL,
    team_type character varying(16) NOT NULL,
    status character varying(16) DEFAULT 'active'::character varying NOT NULL,
    branch_id uuid,
    leader_user_id uuid,
    created_by_user_id uuid,
    description text,
    starts_on date,
    ends_on date,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


--
-- Name: customer_code_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.customer_code_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: customers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.customers (
    customer_id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    customer_code character varying(30) NOT NULL,
    customer_name character varying(150) NOT NULL,
    customer_type character varying(50),
    phone character varying(30),
    email character varying(150),
    address text,
    city character varying(100),
    province character varying(100),
    status character varying(30) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: deliveries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.deliveries (
    delivery_id uuid DEFAULT gen_random_uuid() NOT NULL,
    sales_order_id uuid NOT NULL,
    delivery_number character varying(50) NOT NULL,
    delivery_date date,
    delivery_address text,
    status character varying(30) NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: delivery_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.delivery_items (
    delivery_item_id uuid DEFAULT gen_random_uuid() NOT NULL,
    delivery_id uuid NOT NULL,
    serial_number_id uuid NOT NULL,
    quantity integer NOT NULL,
    warehouse_id uuid
);


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: installation_checklists; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.installation_checklists (
    checklist_id uuid DEFAULT gen_random_uuid() NOT NULL,
    installation_id uuid NOT NULL,
    item_name character varying(150) NOT NULL,
    is_completed boolean NOT NULL,
    notes text,
    completed_at timestamp without time zone,
    created_by uuid,
    updated_by uuid,
    completed_by uuid
);


--
-- Name: installations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.installations (
    installation_id uuid DEFAULT gen_random_uuid() NOT NULL,
    delivery_id uuid NOT NULL,
    user_id uuid NOT NULL,
    installation_date date,
    location text,
    status character varying(30) NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: inventories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.inventories (
    inventory_id uuid DEFAULT gen_random_uuid() NOT NULL,
    warehouse_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity integer NOT NULL,
    minimum_stock integer,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: invoice_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.invoice_items (
    invoice_item_id uuid DEFAULT gen_random_uuid() NOT NULL,
    invoice_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(18,2) NOT NULL,
    discount numeric(18,2),
    subtotal numeric(18,2) NOT NULL
);


--
-- Name: invoices; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.invoices (
    invoice_id uuid DEFAULT gen_random_uuid() NOT NULL,
    sales_order_id uuid NOT NULL,
    invoice_number character varying(50) NOT NULL,
    invoice_date date NOT NULL,
    due_date date,
    subtotal numeric(18,2) NOT NULL,
    tax numeric(18,2),
    discount numeric(18,2),
    total_amount numeric(18,2) NOT NULL,
    status character varying(30) NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: leads; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.leads (
    lead_id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid,
    customer_id uuid,
    lead_code character varying(30) NOT NULL,
    company_name character varying(150) NOT NULL,
    contact_name character varying(150),
    phone character varying(30),
    email character varying(150),
    source character varying(50),
    status character varying(30) NOT NULL,
    qualification text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: maintenance_records; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.maintenance_records (
    maintenance_record_id uuid DEFAULT gen_random_uuid() NOT NULL,
    maintenance_schedule_id uuid NOT NULL,
    user_id uuid NOT NULL,
    performed_at timestamp without time zone NOT NULL,
    condition_before text,
    action_taken text,
    condition_after text,
    result character varying(50),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: maintenance_schedules; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.maintenance_schedules (
    maintenance_schedule_id uuid DEFAULT gen_random_uuid() NOT NULL,
    serial_number_id uuid NOT NULL,
    user_id uuid,
    scheduled_date date NOT NULL,
    maintenance_type character varying(50) NOT NULL,
    status character varying(30) NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.notifications (
    notification_id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid NOT NULL,
    title character varying(150) NOT NULL,
    message text NOT NULL,
    type character varying(30),
    is_read boolean NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: opportunities; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.opportunities (
    opportunity_id uuid DEFAULT gen_random_uuid() NOT NULL,
    customer_id uuid NOT NULL,
    lead_id uuid,
    user_id uuid NOT NULL,
    opportunity_code character varying(30) NOT NULL,
    name character varying(150) NOT NULL,
    description text,
    stage character varying(50) NOT NULL,
    estimated_value numeric(18,2),
    expected_close_date date,
    status character varying(30) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: opportunity_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.opportunity_items (
    opportunity_item_id uuid DEFAULT gen_random_uuid() NOT NULL,
    opportunity_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity integer NOT NULL,
    estimated_price numeric(18,2),
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone
);


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permissions (
    permission_id uuid DEFAULT gen_random_uuid() NOT NULL,
    permission_name character varying(100) NOT NULL,
    module character varying(50) NOT NULL,
    action character varying(30) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: product_categories; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.product_categories (
    category_id uuid DEFAULT gen_random_uuid() NOT NULL,
    category_name character varying(100) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: products; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.products (
    product_id uuid DEFAULT gen_random_uuid() NOT NULL,
    category_id uuid NOT NULL,
    brand_id uuid NOT NULL,
    product_code character varying(50) NOT NULL,
    product_name character varying(150) NOT NULL,
    product_type character varying(50) NOT NULL,
    specification text,
    unit character varying(20) NOT NULL,
    price numeric(18,2),
    warranty_period integer,
    status character varying(30) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: products_product_code_sequence; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.products_product_code_sequence (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: products_product_code_sequence_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.products_product_code_sequence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: products_product_code_sequence_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.products_product_code_sequence_id_seq OWNED BY public.products_product_code_sequence.id;


--
-- Name: quotation_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.quotation_items (
    quotation_item_id uuid DEFAULT gen_random_uuid() NOT NULL,
    quotation_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(18,2) NOT NULL,
    discount numeric(18,2),
    subtotal numeric(18,2) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: quotations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.quotations (
    quotation_id uuid DEFAULT gen_random_uuid() NOT NULL,
    opportunity_id uuid NOT NULL,
    quotation_number character varying(50) NOT NULL,
    quotation_date date NOT NULL,
    valid_until date,
    subtotal numeric(18,2) NOT NULL,
    discount numeric(18,2),
    tax numeric(18,2),
    total_amount numeric(18,2) NOT NULL,
    status character varying(30) NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    discount_type character varying(20) DEFAULT 'amount'::character varying NOT NULL,
    tax_type character varying(20) DEFAULT 'amount'::character varying NOT NULL,
    created_by uuid,
    updated_by uuid
);


--
-- Name: role_permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.role_permissions (
    role_permission_id uuid DEFAULT gen_random_uuid() NOT NULL,
    role_id uuid NOT NULL,
    permission_id uuid NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    data_scope character varying(20),
    CONSTRAINT role_permissions_data_scope_check CHECK (((data_scope IS NULL) OR ((data_scope)::text = ANY ((ARRAY['all'::character varying, 'branch'::character varying, 'team'::character varying, 'assigned'::character varying, 'own'::character varying])::text[]))))
);


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    role_id uuid DEFAULT gen_random_uuid() NOT NULL,
    role_name character varying(50) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    is_system boolean DEFAULT false NOT NULL,
    parent_role_id uuid
);


--
-- Name: sales_order_items; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sales_order_items (
    sales_order_item_id uuid DEFAULT gen_random_uuid() NOT NULL,
    sales_order_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(18,2) NOT NULL,
    discount numeric(18,2),
    subtotal numeric(18,2) NOT NULL
);


--
-- Name: sales_orders; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sales_orders (
    sales_order_id uuid DEFAULT gen_random_uuid() NOT NULL,
    quotation_id uuid NOT NULL,
    sales_order_number character varying(50) NOT NULL,
    order_date date NOT NULL,
    delivery_date date,
    subtotal numeric(18,2) NOT NULL,
    tax numeric(18,2),
    total_amount numeric(18,2) NOT NULL,
    status character varying(30) NOT NULL,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: serial_numbers; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.serial_numbers (
    serial_number_id uuid DEFAULT gen_random_uuid() NOT NULL,
    product_id uuid NOT NULL,
    customer_id uuid,
    serial_number character varying(100) NOT NULL,
    status character varying(30) NOT NULL,
    purchase_date date,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: service_ticket_parts; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.service_ticket_parts (
    service_ticket_part_id uuid DEFAULT gen_random_uuid() NOT NULL,
    service_ticket_id uuid NOT NULL,
    product_id uuid NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(18,2),
    notes text
);


--
-- Name: service_ticket_resolution_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.service_ticket_resolution_logs (
    resolution_log_id uuid NOT NULL,
    service_ticket_id uuid NOT NULL,
    status_from character varying(30) NOT NULL,
    status_to character varying(30) NOT NULL,
    resolution text NOT NULL,
    created_at timestamp(0) without time zone NOT NULL
);


--
-- Name: service_ticket_work_logs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.service_ticket_work_logs (
    work_log_id uuid NOT NULL,
    service_ticket_id uuid NOT NULL,
    entry_type character varying(30) NOT NULL,
    status_snapshot character varying(30) NOT NULL,
    notes text NOT NULL,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: service_tickets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.service_tickets (
    service_ticket_id uuid DEFAULT gen_random_uuid() NOT NULL,
    customer_id uuid NOT NULL,
    serial_number_id uuid NOT NULL,
    user_id uuid,
    ticket_number character varying(50) NOT NULL,
    title character varying(150) NOT NULL,
    description text,
    priority character varying(20) NOT NULL,
    status character varying(30) NOT NULL,
    opened_at timestamp without time zone NOT NULL,
    closed_at timestamp without time zone,
    resolution text,
    created_by uuid,
    updated_by uuid
);


--
-- Name: stock_movements; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.stock_movements (
    stock_movement_id uuid DEFAULT gen_random_uuid() NOT NULL,
    warehouse_id uuid NOT NULL,
    product_id uuid NOT NULL,
    movement_type character varying(30) NOT NULL,
    quantity integer NOT NULL,
    reference_type character varying(50),
    reference_id uuid,
    notes text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    reversal_of_id uuid,
    reversed_by_id uuid,
    correction_reason text,
    created_by uuid,
    updated_by uuid
);


--
-- Name: user_reporting_lines; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_reporting_lines (
    reporting_line_id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid NOT NULL,
    manager_user_id uuid NOT NULL,
    updated_by_user_id uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: user_roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.user_roles (
    user_role_id uuid DEFAULT gen_random_uuid() NOT NULL,
    user_id uuid NOT NULL,
    role_id uuid NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    assigned_by uuid,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    user_id uuid DEFAULT gen_random_uuid() NOT NULL,
    role_id uuid NOT NULL,
    branch_id uuid,
    name character varying(150) NOT NULL,
    email character varying(150) NOT NULL,
    password character varying(255) NOT NULL,
    phone character varying(30),
    status character varying(20) NOT NULL,
    last_login_at timestamp without time zone,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


--
-- Name: warehouses; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.warehouses (
    warehouse_id uuid DEFAULT gen_random_uuid() NOT NULL,
    warehouse_code character varying(30) NOT NULL,
    warehouse_name character varying(100) NOT NULL,
    address text,
    status character varying(20) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: warranties; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.warranties (
    warranty_id uuid DEFAULT gen_random_uuid() NOT NULL,
    serial_number_id uuid NOT NULL,
    customer_id uuid NOT NULL,
    warranty_number character varying(50) NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    warranty_type character varying(50),
    status character varying(30) NOT NULL,
    terms text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_by uuid,
    updated_by uuid
);


--
-- Name: workspace_audit_events; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_audit_events (
    audit_event_id uuid NOT NULL,
    actor_user_id uuid,
    event_type character varying(70) NOT NULL,
    subject_type character varying(30) NOT NULL,
    subject_id uuid,
    details json,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: workspace_calendar_day_exclusions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_calendar_day_exclusions (
    exclusion_id uuid DEFAULT gen_random_uuid() NOT NULL,
    calendar_event_id uuid NOT NULL,
    excluded_date date NOT NULL
);


--
-- Name: workspace_calendar_events; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_calendar_events (
    calendar_event_id uuid DEFAULT gen_random_uuid() NOT NULL,
    owner_user_id uuid NOT NULL,
    title character varying(150) NOT NULL,
    description text,
    visibility character varying(16) DEFAULT 'personal'::character varying NOT NULL,
    starts_at timestamp(0) without time zone NOT NULL,
    ends_at timestamp(0) without time zone,
    is_all_day boolean DEFAULT false NOT NULL,
    location character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: workspace_collaboration_entries; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_collaboration_entries (
    entry_id uuid DEFAULT gen_random_uuid() NOT NULL,
    activity_id uuid,
    task_id uuid,
    author_user_id uuid NOT NULL,
    entry_type character varying(20) NOT NULL,
    body text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT wce_one_resource CHECK (((activity_id IS NOT NULL) <> (task_id IS NOT NULL))),
    CONSTRAINT wce_valid_type CHECK (((entry_type)::text = ANY ((ARRAY['comment'::character varying, 'progress'::character varying])::text[])))
);


--
-- Name: workspace_collaboration_members; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_collaboration_members (
    member_id uuid DEFAULT gen_random_uuid() NOT NULL,
    activity_id uuid,
    task_id uuid,
    user_id uuid NOT NULL,
    access_role character varying(20) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT wcm_one_resource CHECK (((activity_id IS NOT NULL) <> (task_id IS NOT NULL))),
    CONSTRAINT wcm_valid_role CHECK (((access_role)::text = ANY ((ARRAY['viewer'::character varying, 'contributor'::character varying])::text[])))
);


--
-- Name: workspace_note_mentions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_note_mentions (
    mention_id uuid DEFAULT gen_random_uuid() NOT NULL,
    note_id uuid NOT NULL,
    target_type character varying(12) NOT NULL,
    target_id uuid NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT workspace_note_mentions_type_check CHECK (((target_type)::text = ANY ((ARRAY['activity'::character varying, 'todo'::character varying, 'calendar'::character varying])::text[])))
);


--
-- Name: workspace_notification_preferences; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_notification_preferences (
    user_id uuid NOT NULL,
    assignment_enabled boolean DEFAULT true NOT NULL,
    collaboration_enabled boolean DEFAULT true CONSTRAINT workspace_notification_preferenc_collaboration_enabled_not_null NOT NULL,
    submission_enabled boolean DEFAULT true NOT NULL,
    review_enabled boolean DEFAULT true NOT NULL,
    reminder_note_enabled boolean DEFAULT true CONSTRAINT workspace_notification_preferenc_reminder_note_enabled_not_null NOT NULL,
    reminder_calendar_enabled boolean DEFAULT true CONSTRAINT workspace_notification_prefe_reminder_calendar_enabled_not_null NOT NULL,
    reminder_activity_enabled boolean DEFAULT true CONSTRAINT workspace_notification_prefe_reminder_activity_enabled_not_null NOT NULL,
    reminder_todo_enabled boolean DEFAULT true CONSTRAINT workspace_notification_preferenc_reminder_todo_enabled_not_null NOT NULL,
    reminder_lead_minutes smallint DEFAULT '1440'::smallint CONSTRAINT workspace_notification_preferenc_reminder_lead_minutes_not_null NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    crm_status_enabled boolean DEFAULT false NOT NULL
);


--
-- Name: workspace_notifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_notifications (
    notification_id uuid DEFAULT gen_random_uuid() NOT NULL,
    recipient_user_id uuid NOT NULL,
    actor_user_id uuid,
    kind character varying(60) NOT NULL,
    title character varying(160) NOT NULL,
    message character varying(255) NOT NULL,
    target_type character varying(20) NOT NULL,
    target_id uuid NOT NULL,
    dedupe_key character varying(160) NOT NULL,
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: workspace_personal_notes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_personal_notes (
    note_id uuid DEFAULT gen_random_uuid() NOT NULL,
    owner_user_id uuid NOT NULL,
    title character varying(150) NOT NULL,
    body text,
    is_pinned boolean DEFAULT false NOT NULL,
    archived_at timestamp(0) without time zone,
    remind_at timestamp(0) without time zone,
    task_id uuid,
    calendar_event_id uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    activity_id uuid,
    revision integer DEFAULT 0 NOT NULL
);


--
-- Name: workspace_task_assignments; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_task_assignments (
    assignment_id uuid DEFAULT gen_random_uuid() NOT NULL,
    task_id uuid NOT NULL,
    assigned_user_id uuid NOT NULL,
    completion_required boolean DEFAULT true NOT NULL,
    submission_required boolean DEFAULT false NOT NULL,
    status character varying(20) DEFAULT 'assigned'::character varying NOT NULL,
    completed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    review_status character varying(24) DEFAULT 'not_submitted'::character varying NOT NULL,
    reviewed_by_user_id uuid,
    reviewed_at timestamp(0) without time zone,
    review_note text,
    CONSTRAINT wta_review_status_check CHECK (((review_status)::text = ANY ((ARRAY['not_submitted'::character varying, 'pending_review'::character varying, 'approved'::character varying, 'revision_requested'::character varying, 'legacy_accepted'::character varying])::text[])))
);


--
-- Name: workspace_task_reviews; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_task_reviews (
    review_id uuid DEFAULT gen_random_uuid() NOT NULL,
    task_id uuid NOT NULL,
    assignment_id uuid NOT NULL,
    actor_user_id uuid NOT NULL,
    decision character varying(24) NOT NULL,
    note text,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT wtr_decision_check CHECK (((decision)::text = ANY ((ARRAY['approved'::character varying, 'revision_requested'::character varying])::text[])))
);


--
-- Name: workspace_task_submission_files; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_task_submission_files (
    file_id uuid DEFAULT gen_random_uuid() NOT NULL,
    assignment_id uuid NOT NULL,
    uploaded_by_user_id uuid NOT NULL,
    original_name character varying(255) NOT NULL,
    stored_path character varying(500) NOT NULL,
    mime_type character varying(150),
    file_size bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: workspace_tasks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.workspace_tasks (
    task_id uuid DEFAULT gen_random_uuid() NOT NULL,
    owner_user_id uuid NOT NULL,
    assigned_user_id uuid,
    title character varying(150) NOT NULL,
    description text,
    visibility character varying(16) DEFAULT 'personal'::character varying NOT NULL,
    status character varying(20) DEFAULT 'todo'::character varying NOT NULL,
    priority character varying(12) DEFAULT 'medium'::character varying NOT NULL,
    due_at timestamp(0) without time zone,
    completed_at timestamp(0) without time zone,
    customer_id uuid,
    lead_id uuid,
    opportunity_id uuid,
    activity_id uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    starts_at timestamp(0) without time zone
);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: products_product_code_sequence id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products_product_code_sequence ALTER COLUMN id SET DEFAULT nextval('public.products_product_code_sequence_id_seq'::regclass);


--
-- Name: activities activities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_pkey PRIMARY KEY (activity_id);


--
-- Name: announcement_attachments announcement_attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_attachments
    ADD CONSTRAINT announcement_attachments_pkey PRIMARY KEY (attachment_id);


--
-- Name: announcement_media announcement_media_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_media
    ADD CONSTRAINT announcement_media_pkey PRIMARY KEY (media_id);


--
-- Name: announcements announcements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_pkey PRIMARY KEY (announcement_id);


--
-- Name: announcements announcements_slug_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_slug_unique UNIQUE (slug);


--
-- Name: audit_logs audit_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT audit_logs_pkey PRIMARY KEY (audit_log_id);


--
-- Name: branches branches_branch_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branches
    ADD CONSTRAINT branches_branch_code_key UNIQUE (branch_code);


--
-- Name: branches branches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.branches
    ADD CONSTRAINT branches_pkey PRIMARY KEY (branch_id);


--
-- Name: brands brands_brand_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.brands
    ADD CONSTRAINT brands_brand_name_key UNIQUE (brand_name);


--
-- Name: brands brands_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.brands
    ADD CONSTRAINT brands_pkey PRIMARY KEY (brand_id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: workspace_calendar_day_exclusions calendar_event_day_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_calendar_day_exclusions
    ADD CONSTRAINT calendar_event_day_unique UNIQUE (calendar_event_id, excluded_date);


--
-- Name: contacts contacts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contacts
    ADD CONSTRAINT contacts_pkey PRIMARY KEY (contact_id);


--
-- Name: contracts contracts_contract_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_contract_number_key UNIQUE (contract_number);


--
-- Name: contracts contracts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_pkey PRIMARY KEY (contract_id);


--
-- Name: crm_team_members crm_team_member_user_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_team_members
    ADD CONSTRAINT crm_team_member_user_unique UNIQUE (team_id, user_id);


--
-- Name: crm_team_members crm_team_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_team_members
    ADD CONSTRAINT crm_team_members_pkey PRIMARY KEY (team_member_id);


--
-- Name: crm_teams crm_teams_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_teams
    ADD CONSTRAINT crm_teams_pkey PRIMARY KEY (team_id);


--
-- Name: crm_teams crm_teams_team_code_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_teams
    ADD CONSTRAINT crm_teams_team_code_unique UNIQUE (team_code);


--
-- Name: customers customers_customer_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_customer_code_key UNIQUE (customer_code);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (customer_id);


--
-- Name: deliveries deliveries_delivery_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_delivery_number_key UNIQUE (delivery_number);


--
-- Name: deliveries deliveries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_pkey PRIMARY KEY (delivery_id);


--
-- Name: delivery_items delivery_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.delivery_items
    ADD CONSTRAINT delivery_items_pkey PRIMARY KEY (delivery_item_id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: installation_checklists installation_checklists_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installation_checklists
    ADD CONSTRAINT installation_checklists_pkey PRIMARY KEY (checklist_id);


--
-- Name: installations installations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installations
    ADD CONSTRAINT installations_pkey PRIMARY KEY (installation_id);


--
-- Name: inventories inventories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventories
    ADD CONSTRAINT inventories_pkey PRIMARY KEY (inventory_id);


--
-- Name: invoice_items invoice_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice_items
    ADD CONSTRAINT invoice_items_pkey PRIMARY KEY (invoice_item_id);


--
-- Name: invoices invoices_invoice_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT invoices_invoice_number_key UNIQUE (invoice_number);


--
-- Name: invoices invoices_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT invoices_pkey PRIMARY KEY (invoice_id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: leads leads_lead_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_lead_code_key UNIQUE (lead_code);


--
-- Name: leads leads_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_pkey PRIMARY KEY (lead_id);


--
-- Name: maintenance_records maintenance_records_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_records
    ADD CONSTRAINT maintenance_records_pkey PRIMARY KEY (maintenance_record_id);


--
-- Name: maintenance_schedules maintenance_schedules_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_pkey PRIMARY KEY (maintenance_schedule_id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (notification_id);


--
-- Name: opportunities opportunities_opportunity_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT opportunities_opportunity_code_key UNIQUE (opportunity_code);


--
-- Name: opportunities opportunities_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT opportunities_pkey PRIMARY KEY (opportunity_id);


--
-- Name: opportunity_items opportunity_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunity_items
    ADD CONSTRAINT opportunity_items_pkey PRIMARY KEY (opportunity_item_id);


--
-- Name: permissions permissions_permission_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_permission_name_key UNIQUE (permission_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (permission_id);


--
-- Name: product_categories product_categories_category_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_categories
    ADD CONSTRAINT product_categories_category_name_key UNIQUE (category_name);


--
-- Name: product_categories product_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_categories
    ADD CONSTRAINT product_categories_pkey PRIMARY KEY (category_id);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (product_id);


--
-- Name: products products_product_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_product_code_key UNIQUE (product_code);


--
-- Name: products_product_code_sequence products_product_code_sequence_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products_product_code_sequence
    ADD CONSTRAINT products_product_code_sequence_pkey PRIMARY KEY (id);


--
-- Name: quotation_items quotation_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotation_items
    ADD CONSTRAINT quotation_items_pkey PRIMARY KEY (quotation_item_id);


--
-- Name: quotations quotations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotations
    ADD CONSTRAINT quotations_pkey PRIMARY KEY (quotation_id);


--
-- Name: quotations quotations_quotation_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotations
    ADD CONSTRAINT quotations_quotation_number_key UNIQUE (quotation_number);


--
-- Name: role_permissions role_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT role_permissions_pkey PRIMARY KEY (role_permission_id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (role_id);


--
-- Name: roles roles_role_name_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_role_name_key UNIQUE (role_name);


--
-- Name: sales_order_items sales_order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sales_order_items
    ADD CONSTRAINT sales_order_items_pkey PRIMARY KEY (sales_order_item_id);


--
-- Name: sales_orders sales_orders_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT sales_orders_pkey PRIMARY KEY (sales_order_id);


--
-- Name: sales_orders sales_orders_sales_order_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT sales_orders_sales_order_number_key UNIQUE (sales_order_number);


--
-- Name: serial_numbers serial_numbers_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.serial_numbers
    ADD CONSTRAINT serial_numbers_pkey PRIMARY KEY (serial_number_id);


--
-- Name: serial_numbers serial_numbers_serial_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.serial_numbers
    ADD CONSTRAINT serial_numbers_serial_number_key UNIQUE (serial_number);


--
-- Name: service_ticket_parts service_ticket_parts_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_ticket_parts
    ADD CONSTRAINT service_ticket_parts_pkey PRIMARY KEY (service_ticket_part_id);


--
-- Name: service_ticket_resolution_logs service_ticket_resolution_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_ticket_resolution_logs
    ADD CONSTRAINT service_ticket_resolution_logs_pkey PRIMARY KEY (resolution_log_id);


--
-- Name: service_ticket_work_logs service_ticket_work_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_ticket_work_logs
    ADD CONSTRAINT service_ticket_work_logs_pkey PRIMARY KEY (work_log_id);


--
-- Name: service_tickets service_tickets_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_tickets
    ADD CONSTRAINT service_tickets_pkey PRIMARY KEY (service_ticket_id);


--
-- Name: service_tickets service_tickets_ticket_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_tickets
    ADD CONSTRAINT service_tickets_ticket_number_key UNIQUE (ticket_number);


--
-- Name: stock_movements stock_movements_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_pkey PRIMARY KEY (stock_movement_id);


--
-- Name: user_reporting_lines user_reporting_lines_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_reporting_lines
    ADD CONSTRAINT user_reporting_lines_pkey PRIMARY KEY (reporting_line_id);


--
-- Name: user_reporting_lines user_reporting_lines_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_reporting_lines
    ADD CONSTRAINT user_reporting_lines_user_id_unique UNIQUE (user_id);


--
-- Name: user_roles user_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_pkey PRIMARY KEY (user_role_id);


--
-- Name: user_roles user_roles_user_role_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_user_role_unique UNIQUE (user_id, role_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (user_id);


--
-- Name: warehouses warehouses_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warehouses
    ADD CONSTRAINT warehouses_pkey PRIMARY KEY (warehouse_id);


--
-- Name: warehouses warehouses_warehouse_code_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warehouses
    ADD CONSTRAINT warehouses_warehouse_code_key UNIQUE (warehouse_code);


--
-- Name: warranties warranties_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warranties
    ADD CONSTRAINT warranties_pkey PRIMARY KEY (warranty_id);


--
-- Name: warranties warranties_warranty_number_key; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warranties
    ADD CONSTRAINT warranties_warranty_number_key UNIQUE (warranty_number);


--
-- Name: workspace_audit_events workspace_audit_events_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_audit_events
    ADD CONSTRAINT workspace_audit_events_pkey PRIMARY KEY (audit_event_id);


--
-- Name: workspace_calendar_day_exclusions workspace_calendar_day_exclusions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_calendar_day_exclusions
    ADD CONSTRAINT workspace_calendar_day_exclusions_pkey PRIMARY KEY (exclusion_id);


--
-- Name: workspace_calendar_events workspace_calendar_events_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_calendar_events
    ADD CONSTRAINT workspace_calendar_events_pkey PRIMARY KEY (calendar_event_id);


--
-- Name: workspace_collaboration_entries workspace_collaboration_entries_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_entries
    ADD CONSTRAINT workspace_collaboration_entries_pkey PRIMARY KEY (entry_id);


--
-- Name: workspace_collaboration_members workspace_collaboration_members_activity_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_members
    ADD CONSTRAINT workspace_collaboration_members_activity_id_user_id_unique UNIQUE (activity_id, user_id);


--
-- Name: workspace_collaboration_members workspace_collaboration_members_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_members
    ADD CONSTRAINT workspace_collaboration_members_pkey PRIMARY KEY (member_id);


--
-- Name: workspace_collaboration_members workspace_collaboration_members_task_id_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_members
    ADD CONSTRAINT workspace_collaboration_members_task_id_user_id_unique UNIQUE (task_id, user_id);


--
-- Name: workspace_note_mentions workspace_note_mention_target_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_note_mentions
    ADD CONSTRAINT workspace_note_mention_target_unique UNIQUE (note_id, target_type, target_id);


--
-- Name: workspace_note_mentions workspace_note_mentions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_note_mentions
    ADD CONSTRAINT workspace_note_mentions_pkey PRIMARY KEY (mention_id);


--
-- Name: workspace_notification_preferences workspace_notification_preferences_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_notification_preferences
    ADD CONSTRAINT workspace_notification_preferences_pkey PRIMARY KEY (user_id);


--
-- Name: workspace_notifications workspace_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_notifications
    ADD CONSTRAINT workspace_notifications_pkey PRIMARY KEY (notification_id);


--
-- Name: workspace_personal_notes workspace_personal_notes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_personal_notes
    ADD CONSTRAINT workspace_personal_notes_pkey PRIMARY KEY (note_id);


--
-- Name: workspace_task_assignments workspace_task_assignments_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_assignments
    ADD CONSTRAINT workspace_task_assignments_pkey PRIMARY KEY (assignment_id);


--
-- Name: workspace_task_assignments workspace_task_assignments_task_id_assigned_user_id_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_assignments
    ADD CONSTRAINT workspace_task_assignments_task_id_assigned_user_id_unique UNIQUE (task_id, assigned_user_id);


--
-- Name: workspace_task_reviews workspace_task_reviews_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_reviews
    ADD CONSTRAINT workspace_task_reviews_pkey PRIMARY KEY (review_id);


--
-- Name: workspace_task_submission_files workspace_task_submission_files_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_submission_files
    ADD CONSTRAINT workspace_task_submission_files_pkey PRIMARY KEY (file_id);


--
-- Name: workspace_tasks workspace_tasks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_tasks
    ADD CONSTRAINT workspace_tasks_pkey PRIMARY KEY (task_id);


--
-- Name: workspace_notifications wsn_user_dedupe_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_notifications
    ADD CONSTRAINT wsn_user_dedupe_unique UNIQUE (recipient_user_id, dedupe_key);


--
-- Name: activities_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activities_created_by_index ON public.activities USING btree (created_by);


--
-- Name: activities_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX activities_updated_by_index ON public.activities USING btree (updated_by);


--
-- Name: announcement_attachments_media_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX announcement_attachments_media_type_index ON public.announcement_attachments USING btree (media_type);


--
-- Name: announcement_media_media_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX announcement_media_media_type_index ON public.announcement_media USING btree (media_type);


--
-- Name: announcements_is_pinned_pin_order_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX announcements_is_pinned_pin_order_index ON public.announcements USING btree (is_pinned, pin_order);


--
-- Name: announcements_published_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX announcements_published_at_index ON public.announcements USING btree (published_at);


--
-- Name: announcements_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX announcements_status_index ON public.announcements USING btree (status);


--
-- Name: brands_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX brands_created_by_index ON public.brands USING btree (created_by);


--
-- Name: brands_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX brands_updated_by_index ON public.brands USING btree (updated_by);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: contacts_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX contacts_created_by_index ON public.contacts USING btree (created_by);


--
-- Name: contacts_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX contacts_updated_by_index ON public.contacts USING btree (updated_by);


--
-- Name: contracts_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX contracts_created_by_index ON public.contracts USING btree (created_by);


--
-- Name: contracts_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX contracts_updated_by_index ON public.contracts USING btree (updated_by);


--
-- Name: crm_team_members_team_id_left_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX crm_team_members_team_id_left_at_index ON public.crm_team_members USING btree (team_id, left_at);


--
-- Name: crm_team_members_user_id_left_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX crm_team_members_user_id_left_at_index ON public.crm_team_members USING btree (user_id, left_at);


--
-- Name: crm_teams_branch_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX crm_teams_branch_id_status_index ON public.crm_teams USING btree (branch_id, status);


--
-- Name: crm_teams_team_type_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX crm_teams_team_type_status_index ON public.crm_teams USING btree (team_type, status);


--
-- Name: customers_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX customers_created_by_index ON public.customers USING btree (created_by);


--
-- Name: customers_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX customers_updated_by_index ON public.customers USING btree (updated_by);


--
-- Name: deliveries_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX deliveries_created_by_index ON public.deliveries USING btree (created_by);


--
-- Name: deliveries_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX deliveries_updated_by_index ON public.deliveries USING btree (updated_by);


--
-- Name: idx_activities_customer_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_activities_customer_id ON public.activities USING btree (customer_id);


--
-- Name: idx_activities_lead_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_activities_lead_id ON public.activities USING btree (lead_id);


--
-- Name: idx_activities_opportunity_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_activities_opportunity_id ON public.activities USING btree (opportunity_id);


--
-- Name: idx_activities_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_activities_user_id ON public.activities USING btree (user_id);


--
-- Name: idx_audit_logs_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_audit_logs_user_id ON public.audit_logs USING btree (user_id);


--
-- Name: idx_contacts_customer_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_contacts_customer_id ON public.contacts USING btree (customer_id);


--
-- Name: idx_contracts_sales_order_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_contracts_sales_order_id ON public.contracts USING btree (sales_order_id);


--
-- Name: idx_customers_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_customers_user_id ON public.customers USING btree (user_id);


--
-- Name: idx_deliveries_sales_order_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_deliveries_sales_order_id ON public.deliveries USING btree (sales_order_id);


--
-- Name: idx_delivery_items_delivery_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_delivery_items_delivery_id ON public.delivery_items USING btree (delivery_id);


--
-- Name: idx_delivery_items_serial_number_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_delivery_items_serial_number_id ON public.delivery_items USING btree (serial_number_id);


--
-- Name: idx_installation_checklists_installation_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_installation_checklists_installation_id ON public.installation_checklists USING btree (installation_id);


--
-- Name: idx_installations_delivery_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_installations_delivery_id ON public.installations USING btree (delivery_id);


--
-- Name: idx_installations_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_installations_user_id ON public.installations USING btree (user_id);


--
-- Name: idx_inventories_product_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inventories_product_id ON public.inventories USING btree (product_id);


--
-- Name: idx_inventories_warehouse_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_inventories_warehouse_id ON public.inventories USING btree (warehouse_id);


--
-- Name: idx_invoice_items_invoice_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_invoice_items_invoice_id ON public.invoice_items USING btree (invoice_id);


--
-- Name: idx_invoice_items_product_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_invoice_items_product_id ON public.invoice_items USING btree (product_id);


--
-- Name: idx_invoices_sales_order_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_invoices_sales_order_id ON public.invoices USING btree (sales_order_id);


--
-- Name: idx_leads_customer_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_leads_customer_id ON public.leads USING btree (customer_id);


--
-- Name: idx_leads_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_leads_user_id ON public.leads USING btree (user_id);


--
-- Name: idx_maintenance_records_schedule_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_maintenance_records_schedule_id ON public.maintenance_records USING btree (maintenance_schedule_id);


--
-- Name: idx_maintenance_records_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_maintenance_records_user_id ON public.maintenance_records USING btree (user_id);


--
-- Name: idx_maintenance_schedules_serial_number_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_maintenance_schedules_serial_number_id ON public.maintenance_schedules USING btree (serial_number_id);


--
-- Name: idx_maintenance_schedules_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_maintenance_schedules_user_id ON public.maintenance_schedules USING btree (user_id);


--
-- Name: idx_notifications_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_notifications_user_id ON public.notifications USING btree (user_id);


--
-- Name: idx_opportunities_customer_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_opportunities_customer_id ON public.opportunities USING btree (customer_id);


--
-- Name: idx_opportunities_lead_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_opportunities_lead_id ON public.opportunities USING btree (lead_id);


--
-- Name: idx_opportunities_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_opportunities_user_id ON public.opportunities USING btree (user_id);


--
-- Name: idx_opportunity_items_opportunity_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_opportunity_items_opportunity_id ON public.opportunity_items USING btree (opportunity_id);


--
-- Name: idx_opportunity_items_product_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_opportunity_items_product_id ON public.opportunity_items USING btree (product_id);


--
-- Name: idx_products_brand_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_products_brand_id ON public.products USING btree (brand_id);


--
-- Name: idx_products_category_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_products_category_id ON public.products USING btree (category_id);


--
-- Name: idx_quotation_items_product_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_quotation_items_product_id ON public.quotation_items USING btree (product_id);


--
-- Name: idx_quotation_items_quotation_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_quotation_items_quotation_id ON public.quotation_items USING btree (quotation_id);


--
-- Name: idx_quotations_opportunity_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_quotations_opportunity_id ON public.quotations USING btree (opportunity_id);


--
-- Name: idx_role_permissions_permission_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_role_permissions_permission_id ON public.role_permissions USING btree (permission_id);


--
-- Name: idx_role_permissions_role_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_role_permissions_role_id ON public.role_permissions USING btree (role_id);


--
-- Name: idx_sales_order_items_order_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_sales_order_items_order_id ON public.sales_order_items USING btree (sales_order_id);


--
-- Name: idx_sales_order_items_product_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_sales_order_items_product_id ON public.sales_order_items USING btree (product_id);


--
-- Name: idx_sales_orders_quotation_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_sales_orders_quotation_id ON public.sales_orders USING btree (quotation_id);


--
-- Name: idx_serial_numbers_customer_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_serial_numbers_customer_id ON public.serial_numbers USING btree (customer_id);


--
-- Name: idx_serial_numbers_product_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_serial_numbers_product_id ON public.serial_numbers USING btree (product_id);


--
-- Name: idx_service_ticket_parts_product_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_service_ticket_parts_product_id ON public.service_ticket_parts USING btree (product_id);


--
-- Name: idx_service_ticket_parts_ticket_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_service_ticket_parts_ticket_id ON public.service_ticket_parts USING btree (service_ticket_id);


--
-- Name: idx_service_tickets_customer_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_service_tickets_customer_id ON public.service_tickets USING btree (customer_id);


--
-- Name: idx_service_tickets_serial_number_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_service_tickets_serial_number_id ON public.service_tickets USING btree (serial_number_id);


--
-- Name: idx_service_tickets_user_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_service_tickets_user_id ON public.service_tickets USING btree (user_id);


--
-- Name: idx_stock_movements_product_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_stock_movements_product_id ON public.stock_movements USING btree (product_id);


--
-- Name: idx_stock_movements_warehouse_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_stock_movements_warehouse_id ON public.stock_movements USING btree (warehouse_id);


--
-- Name: idx_users_branch_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_branch_id ON public.users USING btree (branch_id);


--
-- Name: idx_users_role_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_users_role_id ON public.users USING btree (role_id);


--
-- Name: idx_warranties_customer_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_warranties_customer_id ON public.warranties USING btree (customer_id);


--
-- Name: idx_warranties_serial_number_id; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_warranties_serial_number_id ON public.warranties USING btree (serial_number_id);


--
-- Name: installations_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX installations_created_by_index ON public.installations USING btree (created_by);


--
-- Name: installations_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX installations_updated_by_index ON public.installations USING btree (updated_by);


--
-- Name: invoices_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX invoices_created_by_index ON public.invoices USING btree (created_by);


--
-- Name: invoices_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX invoices_updated_by_index ON public.invoices USING btree (updated_by);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: leads_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX leads_created_by_index ON public.leads USING btree (created_by);


--
-- Name: leads_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX leads_updated_by_index ON public.leads USING btree (updated_by);


--
-- Name: maintenance_schedules_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX maintenance_schedules_created_by_index ON public.maintenance_schedules USING btree (created_by);


--
-- Name: maintenance_schedules_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX maintenance_schedules_updated_by_index ON public.maintenance_schedules USING btree (updated_by);


--
-- Name: opportunities_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX opportunities_created_by_index ON public.opportunities USING btree (created_by);


--
-- Name: opportunities_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX opportunities_updated_by_index ON public.opportunities USING btree (updated_by);


--
-- Name: product_categories_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX product_categories_created_by_index ON public.product_categories USING btree (created_by);


--
-- Name: product_categories_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX product_categories_updated_by_index ON public.product_categories USING btree (updated_by);


--
-- Name: products_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_created_by_index ON public.products USING btree (created_by);


--
-- Name: products_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX products_updated_by_index ON public.products USING btree (updated_by);


--
-- Name: quotations_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX quotations_created_by_index ON public.quotations USING btree (created_by);


--
-- Name: quotations_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX quotations_updated_by_index ON public.quotations USING btree (updated_by);


--
-- Name: role_permissions_unique_pair; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX role_permissions_unique_pair ON public.role_permissions USING btree (role_id, permission_id);


--
-- Name: sales_orders_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sales_orders_created_by_index ON public.sales_orders USING btree (created_by);


--
-- Name: sales_orders_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sales_orders_updated_by_index ON public.sales_orders USING btree (updated_by);


--
-- Name: serial_numbers_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX serial_numbers_created_by_index ON public.serial_numbers USING btree (created_by);


--
-- Name: serial_numbers_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX serial_numbers_updated_by_index ON public.serial_numbers USING btree (updated_by);


--
-- Name: service_tickets_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX service_tickets_created_by_index ON public.service_tickets USING btree (created_by);


--
-- Name: service_tickets_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX service_tickets_updated_by_index ON public.service_tickets USING btree (updated_by);


--
-- Name: st_work_logs_ticket_date_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX st_work_logs_ticket_date_index ON public.service_ticket_work_logs USING btree (service_ticket_id, created_at);


--
-- Name: stock_movements_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stock_movements_created_by_index ON public.stock_movements USING btree (created_by);


--
-- Name: stock_movements_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX stock_movements_updated_by_index ON public.stock_movements USING btree (updated_by);


--
-- Name: ticket_resolution_logs_by_ticket; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX ticket_resolution_logs_by_ticket ON public.service_ticket_resolution_logs USING btree (service_ticket_id, created_at);


--
-- Name: user_reporting_lines_manager_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_reporting_lines_manager_user_id_index ON public.user_reporting_lines USING btree (manager_user_id);


--
-- Name: user_roles_assigned_by_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_roles_assigned_by_idx ON public.user_roles USING btree (assigned_by);


--
-- Name: user_roles_one_primary_per_user; Type: INDEX; Schema: public; Owner: -
--

CREATE UNIQUE INDEX user_roles_one_primary_per_user ON public.user_roles USING btree (user_id) WHERE (is_primary = true);


--
-- Name: user_roles_role_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX user_roles_role_idx ON public.user_roles USING btree (role_id);


--
-- Name: warehouses_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX warehouses_created_by_index ON public.warehouses USING btree (created_by);


--
-- Name: warehouses_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX warehouses_updated_by_index ON public.warehouses USING btree (updated_by);


--
-- Name: warranties_created_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX warranties_created_by_index ON public.warranties USING btree (created_by);


--
-- Name: warranties_updated_by_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX warranties_updated_by_index ON public.warranties USING btree (updated_by);


--
-- Name: workspace_audit_events_actor_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_audit_events_actor_user_id_index ON public.workspace_audit_events USING btree (actor_user_id);


--
-- Name: workspace_audit_events_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_audit_events_created_at_index ON public.workspace_audit_events USING btree (created_at);


--
-- Name: workspace_audit_events_event_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_audit_events_event_type_index ON public.workspace_audit_events USING btree (event_type);


--
-- Name: workspace_audit_events_subject_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_audit_events_subject_id_index ON public.workspace_audit_events USING btree (subject_id);


--
-- Name: workspace_audit_events_subject_type_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_audit_events_subject_type_index ON public.workspace_audit_events USING btree (subject_type);


--
-- Name: workspace_calendar_events_owner_user_id_starts_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_calendar_events_owner_user_id_starts_at_index ON public.workspace_calendar_events USING btree (owner_user_id, starts_at);


--
-- Name: workspace_collaboration_entries_activity_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_collaboration_entries_activity_id_created_at_index ON public.workspace_collaboration_entries USING btree (activity_id, created_at);


--
-- Name: workspace_collaboration_entries_task_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_collaboration_entries_task_id_created_at_index ON public.workspace_collaboration_entries USING btree (task_id, created_at);


--
-- Name: workspace_collaboration_members_user_id_access_role_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_collaboration_members_user_id_access_role_index ON public.workspace_collaboration_members USING btree (user_id, access_role);


--
-- Name: workspace_note_mention_target_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_note_mention_target_idx ON public.workspace_note_mentions USING btree (target_type, target_id);


--
-- Name: workspace_notes_owner_activity_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_notes_owner_activity_idx ON public.workspace_personal_notes USING btree (owner_user_id, activity_id);


--
-- Name: workspace_notes_owner_calendar_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_notes_owner_calendar_idx ON public.workspace_personal_notes USING btree (owner_user_id, calendar_event_id);


--
-- Name: workspace_personal_notes_owner_user_id_is_pinned_updated_at_ind; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_personal_notes_owner_user_id_is_pinned_updated_at_ind ON public.workspace_personal_notes USING btree (owner_user_id, is_pinned, updated_at);


--
-- Name: workspace_personal_notes_owner_user_id_remind_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_personal_notes_owner_user_id_remind_at_index ON public.workspace_personal_notes USING btree (owner_user_id, remind_at);


--
-- Name: workspace_task_assignments_assigned_user_id_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_task_assignments_assigned_user_id_status_index ON public.workspace_task_assignments USING btree (assigned_user_id, status);


--
-- Name: workspace_task_submission_files_assignment_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_task_submission_files_assignment_id_created_at_index ON public.workspace_task_submission_files USING btree (assignment_id, created_at);


--
-- Name: workspace_tasks_assigned_user_id_status_due_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_tasks_assigned_user_id_status_due_at_index ON public.workspace_tasks USING btree (assigned_user_id, status, due_at);


--
-- Name: workspace_tasks_due_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_tasks_due_at_index ON public.workspace_tasks USING btree (due_at);


--
-- Name: workspace_tasks_owner_user_id_visibility_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX workspace_tasks_owner_user_id_visibility_status_index ON public.workspace_tasks USING btree (owner_user_id, visibility, status);


--
-- Name: wsa_subject_time_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wsa_subject_time_idx ON public.workspace_audit_events USING btree (subject_type, subject_id, created_at);


--
-- Name: wsn_inbox_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wsn_inbox_idx ON public.workspace_notifications USING btree (recipient_user_id, read_at, created_at);


--
-- Name: wta_task_review_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wta_task_review_idx ON public.workspace_task_assignments USING btree (task_id, review_status);


--
-- Name: wtr_assignment_history_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wtr_assignment_history_idx ON public.workspace_task_reviews USING btree (assignment_id, created_at);


--
-- Name: wtr_task_history_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX wtr_task_history_idx ON public.workspace_task_reviews USING btree (task_id, created_at);


--
-- Name: activities activities_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: activities activities_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT activities_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: announcement_attachments announcement_attachments_announcement_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_attachments
    ADD CONSTRAINT announcement_attachments_announcement_id_foreign FOREIGN KEY (announcement_id) REFERENCES public.announcements(announcement_id) ON DELETE CASCADE;


--
-- Name: announcement_attachments announcement_attachments_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_attachments
    ADD CONSTRAINT announcement_attachments_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: announcement_media announcement_media_announcement_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_media
    ADD CONSTRAINT announcement_media_announcement_id_foreign FOREIGN KEY (announcement_id) REFERENCES public.announcements(announcement_id) ON DELETE CASCADE;


--
-- Name: announcement_media announcement_media_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcement_media
    ADD CONSTRAINT announcement_media_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: announcements announcements_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: announcements announcements_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.announcements
    ADD CONSTRAINT announcements_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: brands brands_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.brands
    ADD CONSTRAINT brands_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: brands brands_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.brands
    ADD CONSTRAINT brands_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: workspace_calendar_day_exclusions calendar_exclusion_event_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_calendar_day_exclusions
    ADD CONSTRAINT calendar_exclusion_event_fk FOREIGN KEY (calendar_event_id) REFERENCES public.workspace_calendar_events(calendar_event_id) ON DELETE CASCADE;


--
-- Name: contacts contacts_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contacts
    ADD CONSTRAINT contacts_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: contacts contacts_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contacts
    ADD CONSTRAINT contacts_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: contracts contracts_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: contracts contracts_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT contracts_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: crm_team_members crm_team_members_team_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_team_members
    ADD CONSTRAINT crm_team_members_team_id_foreign FOREIGN KEY (team_id) REFERENCES public.crm_teams(team_id) ON DELETE CASCADE;


--
-- Name: crm_team_members crm_team_members_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_team_members
    ADD CONSTRAINT crm_team_members_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: crm_teams crm_teams_branch_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_teams
    ADD CONSTRAINT crm_teams_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES public.branches(branch_id) ON DELETE SET NULL;


--
-- Name: crm_teams crm_teams_created_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_teams
    ADD CONSTRAINT crm_teams_created_by_user_id_foreign FOREIGN KEY (created_by_user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: crm_teams crm_teams_leader_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.crm_teams
    ADD CONSTRAINT crm_teams_leader_user_id_foreign FOREIGN KEY (leader_user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: customers customers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: customers customers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: deliveries deliveries_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: deliveries deliveries_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: delivery_items delivery_items_warehouse_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.delivery_items
    ADD CONSTRAINT delivery_items_warehouse_id_foreign FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(warehouse_id);


--
-- Name: activities fk_activities_customer; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT fk_activities_customer FOREIGN KEY (customer_id) REFERENCES public.customers(customer_id);


--
-- Name: activities fk_activities_lead; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT fk_activities_lead FOREIGN KEY (lead_id) REFERENCES public.leads(lead_id);


--
-- Name: activities fk_activities_opportunity; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT fk_activities_opportunity FOREIGN KEY (opportunity_id) REFERENCES public.opportunities(opportunity_id);


--
-- Name: activities fk_activities_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.activities
    ADD CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: audit_logs fk_audit_logs_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.audit_logs
    ADD CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: contacts fk_contacts_customer; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contacts
    ADD CONSTRAINT fk_contacts_customer FOREIGN KEY (customer_id) REFERENCES public.customers(customer_id);


--
-- Name: contracts fk_contracts_sales_order; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.contracts
    ADD CONSTRAINT fk_contracts_sales_order FOREIGN KEY (sales_order_id) REFERENCES public.sales_orders(sales_order_id);


--
-- Name: customers fk_customers_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT fk_customers_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: deliveries fk_deliveries_sales_order; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT fk_deliveries_sales_order FOREIGN KEY (sales_order_id) REFERENCES public.sales_orders(sales_order_id);


--
-- Name: delivery_items fk_delivery_items_delivery; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.delivery_items
    ADD CONSTRAINT fk_delivery_items_delivery FOREIGN KEY (delivery_id) REFERENCES public.deliveries(delivery_id);


--
-- Name: delivery_items fk_delivery_items_serial; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.delivery_items
    ADD CONSTRAINT fk_delivery_items_serial FOREIGN KEY (serial_number_id) REFERENCES public.serial_numbers(serial_number_id);


--
-- Name: installation_checklists fk_installation_checklists_installation; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installation_checklists
    ADD CONSTRAINT fk_installation_checklists_installation FOREIGN KEY (installation_id) REFERENCES public.installations(installation_id);


--
-- Name: installations fk_installations_delivery; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installations
    ADD CONSTRAINT fk_installations_delivery FOREIGN KEY (delivery_id) REFERENCES public.deliveries(delivery_id);


--
-- Name: installations fk_installations_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installations
    ADD CONSTRAINT fk_installations_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: inventories fk_inventories_product; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventories
    ADD CONSTRAINT fk_inventories_product FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: inventories fk_inventories_warehouse; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.inventories
    ADD CONSTRAINT fk_inventories_warehouse FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(warehouse_id);


--
-- Name: invoice_items fk_invoice_items_invoice; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice_items
    ADD CONSTRAINT fk_invoice_items_invoice FOREIGN KEY (invoice_id) REFERENCES public.invoices(invoice_id);


--
-- Name: invoice_items fk_invoice_items_product; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoice_items
    ADD CONSTRAINT fk_invoice_items_product FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: invoices fk_invoices_sales_order; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT fk_invoices_sales_order FOREIGN KEY (sales_order_id) REFERENCES public.sales_orders(sales_order_id);


--
-- Name: leads fk_leads_customer; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT fk_leads_customer FOREIGN KEY (customer_id) REFERENCES public.customers(customer_id);


--
-- Name: leads fk_leads_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT fk_leads_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: maintenance_records fk_maintenance_records_schedule; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_records
    ADD CONSTRAINT fk_maintenance_records_schedule FOREIGN KEY (maintenance_schedule_id) REFERENCES public.maintenance_schedules(maintenance_schedule_id);


--
-- Name: maintenance_records fk_maintenance_records_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_records
    ADD CONSTRAINT fk_maintenance_records_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: maintenance_schedules fk_maintenance_schedules_serial; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT fk_maintenance_schedules_serial FOREIGN KEY (serial_number_id) REFERENCES public.serial_numbers(serial_number_id);


--
-- Name: maintenance_schedules fk_maintenance_schedules_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT fk_maintenance_schedules_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: notifications fk_notifications_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: opportunities fk_opportunities_customer; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT fk_opportunities_customer FOREIGN KEY (customer_id) REFERENCES public.customers(customer_id);


--
-- Name: opportunities fk_opportunities_lead; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT fk_opportunities_lead FOREIGN KEY (lead_id) REFERENCES public.leads(lead_id);


--
-- Name: opportunities fk_opportunities_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT fk_opportunities_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: opportunity_items fk_opportunity_items_opportunity; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunity_items
    ADD CONSTRAINT fk_opportunity_items_opportunity FOREIGN KEY (opportunity_id) REFERENCES public.opportunities(opportunity_id);


--
-- Name: opportunity_items fk_opportunity_items_product; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunity_items
    ADD CONSTRAINT fk_opportunity_items_product FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: products fk_products_brand; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT fk_products_brand FOREIGN KEY (brand_id) REFERENCES public.brands(brand_id);


--
-- Name: products fk_products_category; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES public.product_categories(category_id);


--
-- Name: quotation_items fk_quotation_items_product; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotation_items
    ADD CONSTRAINT fk_quotation_items_product FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: quotation_items fk_quotation_items_quotation; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotation_items
    ADD CONSTRAINT fk_quotation_items_quotation FOREIGN KEY (quotation_id) REFERENCES public.quotations(quotation_id);


--
-- Name: quotations fk_quotations_opportunity; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotations
    ADD CONSTRAINT fk_quotations_opportunity FOREIGN KEY (opportunity_id) REFERENCES public.opportunities(opportunity_id);


--
-- Name: role_permissions fk_role_permissions_permission; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES public.permissions(permission_id);


--
-- Name: role_permissions fk_role_permissions_role; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.role_permissions
    ADD CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES public.roles(role_id);


--
-- Name: sales_order_items fk_sales_order_items_order; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sales_order_items
    ADD CONSTRAINT fk_sales_order_items_order FOREIGN KEY (sales_order_id) REFERENCES public.sales_orders(sales_order_id);


--
-- Name: sales_order_items fk_sales_order_items_product; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sales_order_items
    ADD CONSTRAINT fk_sales_order_items_product FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: sales_orders fk_sales_orders_quotation; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT fk_sales_orders_quotation FOREIGN KEY (quotation_id) REFERENCES public.quotations(quotation_id);


--
-- Name: serial_numbers fk_serial_numbers_customer; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.serial_numbers
    ADD CONSTRAINT fk_serial_numbers_customer FOREIGN KEY (customer_id) REFERENCES public.customers(customer_id);


--
-- Name: serial_numbers fk_serial_numbers_product; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.serial_numbers
    ADD CONSTRAINT fk_serial_numbers_product FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: service_ticket_parts fk_service_ticket_parts_product; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_ticket_parts
    ADD CONSTRAINT fk_service_ticket_parts_product FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: service_ticket_parts fk_service_ticket_parts_ticket; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_ticket_parts
    ADD CONSTRAINT fk_service_ticket_parts_ticket FOREIGN KEY (service_ticket_id) REFERENCES public.service_tickets(service_ticket_id);


--
-- Name: service_tickets fk_service_tickets_customer; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_tickets
    ADD CONSTRAINT fk_service_tickets_customer FOREIGN KEY (customer_id) REFERENCES public.customers(customer_id);


--
-- Name: service_tickets fk_service_tickets_serial; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_tickets
    ADD CONSTRAINT fk_service_tickets_serial FOREIGN KEY (serial_number_id) REFERENCES public.serial_numbers(serial_number_id);


--
-- Name: service_tickets fk_service_tickets_user; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_tickets
    ADD CONSTRAINT fk_service_tickets_user FOREIGN KEY (user_id) REFERENCES public.users(user_id);


--
-- Name: stock_movements fk_stock_movements_product; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT fk_stock_movements_product FOREIGN KEY (product_id) REFERENCES public.products(product_id);


--
-- Name: stock_movements fk_stock_movements_warehouse; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT fk_stock_movements_warehouse FOREIGN KEY (warehouse_id) REFERENCES public.warehouses(warehouse_id);


--
-- Name: users fk_users_branch; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT fk_users_branch FOREIGN KEY (branch_id) REFERENCES public.branches(branch_id);


--
-- Name: users fk_users_role; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES public.roles(role_id);


--
-- Name: warranties fk_warranties_customer; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warranties
    ADD CONSTRAINT fk_warranties_customer FOREIGN KEY (customer_id) REFERENCES public.customers(customer_id);


--
-- Name: warranties fk_warranties_serial; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warranties
    ADD CONSTRAINT fk_warranties_serial FOREIGN KEY (serial_number_id) REFERENCES public.serial_numbers(serial_number_id);


--
-- Name: installation_checklists installation_checklists_completed_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installation_checklists
    ADD CONSTRAINT installation_checklists_completed_by_foreign FOREIGN KEY (completed_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: installation_checklists installation_checklists_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installation_checklists
    ADD CONSTRAINT installation_checklists_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: installation_checklists installation_checklists_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installation_checklists
    ADD CONSTRAINT installation_checklists_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: installations installations_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installations
    ADD CONSTRAINT installations_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: installations installations_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.installations
    ADD CONSTRAINT installations_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: invoices invoices_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT invoices_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: invoices invoices_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.invoices
    ADD CONSTRAINT invoices_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: leads leads_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: leads leads_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: maintenance_schedules maintenance_schedules_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: maintenance_schedules maintenance_schedules_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.maintenance_schedules
    ADD CONSTRAINT maintenance_schedules_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: opportunities opportunities_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT opportunities_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: opportunities opportunities_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT opportunities_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: product_categories product_categories_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_categories
    ADD CONSTRAINT product_categories_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: product_categories product_categories_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.product_categories
    ADD CONSTRAINT product_categories_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: products products_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: products products_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: quotations quotations_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotations
    ADD CONSTRAINT quotations_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: quotations quotations_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.quotations
    ADD CONSTRAINT quotations_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: roles roles_parent_role_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_parent_role_fk FOREIGN KEY (parent_role_id) REFERENCES public.roles(role_id) ON DELETE SET NULL;


--
-- Name: sales_orders sales_orders_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT sales_orders_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: sales_orders sales_orders_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sales_orders
    ADD CONSTRAINT sales_orders_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: serial_numbers serial_numbers_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.serial_numbers
    ADD CONSTRAINT serial_numbers_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: serial_numbers serial_numbers_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.serial_numbers
    ADD CONSTRAINT serial_numbers_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: service_ticket_resolution_logs service_ticket_resolution_logs_service_ticket_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_ticket_resolution_logs
    ADD CONSTRAINT service_ticket_resolution_logs_service_ticket_id_foreign FOREIGN KEY (service_ticket_id) REFERENCES public.service_tickets(service_ticket_id) ON DELETE CASCADE;


--
-- Name: service_ticket_work_logs service_ticket_work_logs_service_ticket_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_ticket_work_logs
    ADD CONSTRAINT service_ticket_work_logs_service_ticket_id_foreign FOREIGN KEY (service_ticket_id) REFERENCES public.service_tickets(service_ticket_id) ON DELETE CASCADE;


--
-- Name: service_tickets service_tickets_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_tickets
    ADD CONSTRAINT service_tickets_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: service_tickets service_tickets_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.service_tickets
    ADD CONSTRAINT service_tickets_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: stock_movements stock_movements_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: stock_movements stock_movements_reversal_of_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_reversal_of_id_foreign FOREIGN KEY (reversal_of_id) REFERENCES public.stock_movements(stock_movement_id) ON DELETE SET NULL;


--
-- Name: stock_movements stock_movements_reversed_by_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_reversed_by_id_foreign FOREIGN KEY (reversed_by_id) REFERENCES public.stock_movements(stock_movement_id) ON DELETE SET NULL;


--
-- Name: stock_movements stock_movements_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.stock_movements
    ADD CONSTRAINT stock_movements_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: user_reporting_lines user_reporting_lines_manager_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_reporting_lines
    ADD CONSTRAINT user_reporting_lines_manager_user_id_foreign FOREIGN KEY (manager_user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: user_reporting_lines user_reporting_lines_updated_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_reporting_lines
    ADD CONSTRAINT user_reporting_lines_updated_by_user_id_foreign FOREIGN KEY (updated_by_user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: user_reporting_lines user_reporting_lines_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_reporting_lines
    ADD CONSTRAINT user_reporting_lines_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: user_roles user_roles_assigned_by_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_assigned_by_fk FOREIGN KEY (assigned_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: user_roles user_roles_role_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_role_fk FOREIGN KEY (role_id) REFERENCES public.roles(role_id) ON DELETE RESTRICT;


--
-- Name: user_roles user_roles_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.user_roles
    ADD CONSTRAINT user_roles_user_fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: warehouses warehouses_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warehouses
    ADD CONSTRAINT warehouses_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: warehouses warehouses_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warehouses
    ADD CONSTRAINT warehouses_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: warranties warranties_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warranties
    ADD CONSTRAINT warranties_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: warranties warranties_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.warranties
    ADD CONSTRAINT warranties_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: workspace_calendar_events workspace_calendar_events_owner_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_calendar_events
    ADD CONSTRAINT workspace_calendar_events_owner_user_id_foreign FOREIGN KEY (owner_user_id) REFERENCES public.users(user_id) ON DELETE RESTRICT;


--
-- Name: workspace_collaboration_entries workspace_collaboration_entries_activity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_entries
    ADD CONSTRAINT workspace_collaboration_entries_activity_id_foreign FOREIGN KEY (activity_id) REFERENCES public.activities(activity_id) ON DELETE CASCADE;


--
-- Name: workspace_collaboration_entries workspace_collaboration_entries_author_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_entries
    ADD CONSTRAINT workspace_collaboration_entries_author_user_id_foreign FOREIGN KEY (author_user_id) REFERENCES public.users(user_id) ON DELETE RESTRICT;


--
-- Name: workspace_collaboration_entries workspace_collaboration_entries_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_entries
    ADD CONSTRAINT workspace_collaboration_entries_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.workspace_tasks(task_id) ON DELETE CASCADE;


--
-- Name: workspace_collaboration_members workspace_collaboration_members_activity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_members
    ADD CONSTRAINT workspace_collaboration_members_activity_id_foreign FOREIGN KEY (activity_id) REFERENCES public.activities(activity_id) ON DELETE CASCADE;


--
-- Name: workspace_collaboration_members workspace_collaboration_members_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_members
    ADD CONSTRAINT workspace_collaboration_members_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.workspace_tasks(task_id) ON DELETE CASCADE;


--
-- Name: workspace_collaboration_members workspace_collaboration_members_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_collaboration_members
    ADD CONSTRAINT workspace_collaboration_members_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE RESTRICT;


--
-- Name: workspace_note_mentions workspace_note_mentions_note_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_note_mentions
    ADD CONSTRAINT workspace_note_mentions_note_id_foreign FOREIGN KEY (note_id) REFERENCES public.workspace_personal_notes(note_id) ON DELETE CASCADE;


--
-- Name: workspace_personal_notes workspace_notes_activity_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_personal_notes
    ADD CONSTRAINT workspace_notes_activity_fk FOREIGN KEY (activity_id) REFERENCES public.activities(activity_id) ON DELETE SET NULL;


--
-- Name: workspace_personal_notes workspace_personal_notes_calendar_event_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_personal_notes
    ADD CONSTRAINT workspace_personal_notes_calendar_event_id_foreign FOREIGN KEY (calendar_event_id) REFERENCES public.workspace_calendar_events(calendar_event_id) ON DELETE SET NULL;


--
-- Name: workspace_personal_notes workspace_personal_notes_owner_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_personal_notes
    ADD CONSTRAINT workspace_personal_notes_owner_user_id_foreign FOREIGN KEY (owner_user_id) REFERENCES public.users(user_id) ON DELETE RESTRICT;


--
-- Name: workspace_personal_notes workspace_personal_notes_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_personal_notes
    ADD CONSTRAINT workspace_personal_notes_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.workspace_tasks(task_id) ON DELETE SET NULL;


--
-- Name: workspace_task_assignments workspace_task_assignments_assigned_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_assignments
    ADD CONSTRAINT workspace_task_assignments_assigned_user_id_foreign FOREIGN KEY (assigned_user_id) REFERENCES public.users(user_id) ON DELETE RESTRICT;


--
-- Name: workspace_task_assignments workspace_task_assignments_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_assignments
    ADD CONSTRAINT workspace_task_assignments_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.workspace_tasks(task_id) ON DELETE CASCADE;


--
-- Name: workspace_task_reviews workspace_task_reviews_actor_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_reviews
    ADD CONSTRAINT workspace_task_reviews_actor_user_id_foreign FOREIGN KEY (actor_user_id) REFERENCES public.users(user_id) ON DELETE RESTRICT;


--
-- Name: workspace_task_reviews workspace_task_reviews_assignment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_reviews
    ADD CONSTRAINT workspace_task_reviews_assignment_id_foreign FOREIGN KEY (assignment_id) REFERENCES public.workspace_task_assignments(assignment_id) ON DELETE CASCADE;


--
-- Name: workspace_task_reviews workspace_task_reviews_task_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_reviews
    ADD CONSTRAINT workspace_task_reviews_task_id_foreign FOREIGN KEY (task_id) REFERENCES public.workspace_tasks(task_id) ON DELETE CASCADE;


--
-- Name: workspace_task_submission_files workspace_task_submission_files_assignment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_submission_files
    ADD CONSTRAINT workspace_task_submission_files_assignment_id_foreign FOREIGN KEY (assignment_id) REFERENCES public.workspace_task_assignments(assignment_id) ON DELETE CASCADE;


--
-- Name: workspace_task_submission_files workspace_task_submission_files_uploaded_by_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_submission_files
    ADD CONSTRAINT workspace_task_submission_files_uploaded_by_user_id_foreign FOREIGN KEY (uploaded_by_user_id) REFERENCES public.users(user_id) ON DELETE RESTRICT;


--
-- Name: workspace_tasks workspace_tasks_activity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_tasks
    ADD CONSTRAINT workspace_tasks_activity_id_foreign FOREIGN KEY (activity_id) REFERENCES public.activities(activity_id) ON DELETE SET NULL;


--
-- Name: workspace_tasks workspace_tasks_assigned_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_tasks
    ADD CONSTRAINT workspace_tasks_assigned_user_id_foreign FOREIGN KEY (assigned_user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: workspace_tasks workspace_tasks_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_tasks
    ADD CONSTRAINT workspace_tasks_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(customer_id) ON DELETE SET NULL;


--
-- Name: workspace_tasks workspace_tasks_lead_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_tasks
    ADD CONSTRAINT workspace_tasks_lead_id_foreign FOREIGN KEY (lead_id) REFERENCES public.leads(lead_id) ON DELETE SET NULL;


--
-- Name: workspace_tasks workspace_tasks_opportunity_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_tasks
    ADD CONSTRAINT workspace_tasks_opportunity_id_foreign FOREIGN KEY (opportunity_id) REFERENCES public.opportunities(opportunity_id) ON DELETE SET NULL;


--
-- Name: workspace_tasks workspace_tasks_owner_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_tasks
    ADD CONSTRAINT workspace_tasks_owner_user_id_foreign FOREIGN KEY (owner_user_id) REFERENCES public.users(user_id) ON DELETE RESTRICT;


--
-- Name: workspace_notifications wsn_actor_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_notifications
    ADD CONSTRAINT wsn_actor_fk FOREIGN KEY (actor_user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- Name: workspace_notifications wsn_recipient_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_notifications
    ADD CONSTRAINT wsn_recipient_fk FOREIGN KEY (recipient_user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: workspace_notification_preferences wsnp_user_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_notification_preferences
    ADD CONSTRAINT wsnp_user_fk FOREIGN KEY (user_id) REFERENCES public.users(user_id) ON DELETE CASCADE;


--
-- Name: workspace_task_assignments wta_reviewed_by_fk; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.workspace_task_assignments
    ADD CONSTRAINT wta_reviewed_by_fk FOREIGN KEY (reviewed_by_user_id) REFERENCES public.users(user_id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--



-- END ORIGINAL SCHEMA
-- Fresh ledger: all 32 historical migrations embodied in this baseline.
INSERT INTO public.migrations (migration, batch) VALUES
    ('0001_01_01_000000_create_users_table', 1),
    ('0001_01_01_000001_create_cache_table', 1),
    ('0001_01_01_000002_create_jobs_table', 1),
    ('2026_10_15_102740_add_correction_fields_to_stock_movements_table', 1),
    ('2026_10_21_061901_create_announcements_and_media_tables', 1),
    ('2026_10_28_000000_add_thumbnail_appearance_to_announcements_table', 1),
    ('2026_11_01_072347_create_products_product_code_sequence', 1),
    ('2026_11_04_230000_create_delivery_items_if_missing', 1),
    ('2026_11_05_000000_add_warehouse_to_delivery_items', 1),
    ('2026_11_05_000001_create_service_ticket_resolution_logs_table', 1),
    ('2026_11_12_000001_create_service_ticket_work_logs_table', 1),
    ('2026_11_12_190000_create_user_roles_table', 1),
    ('2026_11_12_200000_add_rbac_protection_and_scope', 1),
    ('2026_11_12_210000_seed_crm_module_access_permissions', 1),
    ('2026_11_12_220000_seed_crm_page_permissions', 1),
    ('2026_11_12_230000_seed_crm_workflow_action_permissions', 1),
    ('2026_11_13_000000_add_creator_attribution_to_crm_entities', 1),
    ('2026_11_13_010000_create_customer_code_sequence', 1),
    ('2026_11_13_020000_add_installation_checklist_actor_columns', 1),
    ('2026_11_14_000000_create_workspace_personal_tables', 1),
    ('2026_11_15_180000_create_calendar_day_exclusions', 1),
    ('2026_11_15_200000_add_workspace_assignments_and_schedules', 1),
    ('2026_11_15_230000_add_crm_organization_hierarchy', 1),
    ('2026_11_16_000000_create_crm_teams_and_members', 1),
    ('2026_11_16_160000_create_workspace_collaboration_tables', 1),
    ('2026_11_17_120000_add_workspace_task_reviews', 1),
    ('2026_11_17_210000_link_workspace_personal_notes_to_activities', 1),
    ('2026_11_17_220000_create_workspace_note_mentions_and_revision', 1),
    ('2026_11_17_230000_create_workspace_notifications', 1),
    ('2026_11_17_234000_create_workspace_notification_preferences', 1),
    ('2026_11_18_010000_create_workspace_audit_events', 1),
    ('2026_11_18_190000_add_crm_status_notification_preference', 1);

-- Code-defined permission catalogue ONLY; no roles, users, grants or business data.
INSERT INTO public.permissions (permission_name, module, action, description) VALUES
    ('customer_lead.access', 'customer_lead', 'access', 'CRM module entry; no role grant'),
    ('sales_crm.access', 'sales_crm', 'access', 'CRM module entry; no role grant'),
    ('catalog.access', 'catalog', 'access', 'CRM module entry; no role grant'),
    ('catalog.manage', 'catalog', 'manage', 'CRM module entry; no role grant'),
    ('inventory.access', 'inventory', 'access', 'CRM module entry; no role grant'),
    ('delivery.access', 'delivery', 'access', 'CRM module entry; no role grant'),
    ('installation.access', 'installation', 'access', 'CRM module entry; no role grant'),
    ('service.access', 'service', 'access', 'CRM module entry; no role grant'),
    ('billing.access', 'billing', 'access', 'CRM module entry; no role grant'),
    ('reporting.access', 'reporting', 'access', 'CRM module entry; no role grant'),
    ('announcements.manage', 'announcements', 'manage', 'CRM module entry; no role grant'),
    ('crm_page.customers.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.customers.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.customers.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.customers.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.contacts.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.contacts.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.contacts.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.contacts.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.leads.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.leads.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.leads.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.leads.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.activities.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.activities.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.activities.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.activities.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.opportunities.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.opportunities.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.opportunities.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.opportunities.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.quotations.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.quotations.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.quotations.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.quotations.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.sales_orders.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.sales_orders.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.sales_orders.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.sales_orders.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.contracts.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.contracts.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.contracts.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.contracts.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.products.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.products.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.products.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.products.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.product_categories.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.product_categories.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.product_categories.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.product_categories.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.brands.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.brands.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.brands.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.brands.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.warehouses.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.warehouses.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.warehouses.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.warehouses.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.inventories.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.inventories.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.inventories.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.inventories.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.stock_movements.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.stock_movements.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.stock_movements.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.stock_movements.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.serial_numbers.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.serial_numbers.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.serial_numbers.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.serial_numbers.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.deliveries.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.deliveries.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.deliveries.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.deliveries.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.installations.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.installations.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.installations.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.installations.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.warranties.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.warranties.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.warranties.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.warranties.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.service_tickets.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.service_tickets.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.service_tickets.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.service_tickets.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.maintenance.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.maintenance.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.maintenance.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.maintenance.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.service_history.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.service_history.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.service_history.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.service_history.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.invoices.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.invoices.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.invoices.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.invoices.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.reports.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.reports.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.reports.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.reports.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_page.announcement_management.view', 'crm_page', 'view', 'CRM page permission; no role grant'),
    ('crm_page.announcement_management.create', 'crm_page', 'create', 'CRM page permission; no role grant'),
    ('crm_page.announcement_management.update', 'crm_page', 'update', 'CRM page permission; no role grant'),
    ('crm_page.announcement_management.delete', 'crm_page', 'delete', 'CRM page permission; no role grant'),
    ('crm_workflow.deliveries.status', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.installations.status', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.installations.checklist', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.installations.activate_warranty', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.service_tickets.status', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.service_tickets.parts_create', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.service_tickets.parts_update', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.service_tickets.parts_delete', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.maintenance.status', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant'),
    ('crm_workflow.invoices.status', 'crm_workflow', 'execute', 'CRM workflow permission; no role grant');
COMMIT;
