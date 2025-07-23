import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Business Models
export interface Client {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    address?: string;
    company?: string;
    created_at: string;
    updated_at: string;
}

export interface Product {
    id: number;
    name: string;
    description?: string;
    sku: string;
    price: string;
    cost_price?: string;
    stock_quantity: number;
    min_stock_level?: number;
    category?: string;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface QuotationItem {
    id: number;
    quotation_id: number;
    product_id: number;
    quantity: number;
    unit_price: string;
    product: Product;
}

export interface Quotation {
    id: number;
    quote_number: string;
    client_id: number;
    issue_date: string;
    valid_until: string;
    status: 'draft' | 'sent' | 'accepted' | 'rejected' | 'expired';
    subtotal: string;
    tax_rate?: string;
    tax_amount?: string;
    discount_amount?: string;
    total_amount: string;
    notes?: string;
    created_at: string;
    updated_at: string;
    client: Client;
    items: QuotationItem[];
}

export interface InvoiceItem {
    id: number;
    invoice_id: number;
    product_id: number;
    quantity: number;
    unit_price: string;
    product: Product;
}

export interface Invoice {
    id: number;
    invoice_number: string;
    client_id: number;
    quotation_id?: number;
    issue_date: string;
    due_date: string;
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    subtotal: string;
    tax_rate?: string;
    tax_amount?: string;
    discount_amount?: string;
    total_amount: string;
    notes?: string;
    created_at: string;
    updated_at: string;
    client: Client;
    items: InvoiceItem[];
}

export interface Vendor {
    id: number;
    name: string;
    company?: string;
    email?: string;
    phone?: string;
    address?: string;
    contact_person?: string;
    tax_id?: string;
    is_active: boolean;
    notes?: string;
    created_at: string;
    updated_at: string;
}

export interface Expense {
    id: number;
    vendor_id?: number;
    category: string;
    amount: string;
    description: string;
    expense_date: string;
    receipt_path?: string;
    is_billable: boolean;
    is_reimbursable: boolean;
    project?: string;
    notes?: string;
    created_at: string;
    updated_at: string;
    vendor?: Vendor;
}
