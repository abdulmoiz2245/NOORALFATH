import { router } from '@inertiajs/vue3'
import type { Quotation, Invoice } from '@/types'

interface EmailOptions {
  to: string
  subject?: string
  message?: string
  attachPdf?: boolean
}

interface EmailTemplate {
  subject: string
  message: string
}

export function useEmailService() {
  const sendQuotationEmail = async (quotation: Quotation, options: EmailOptions) => {
    const defaultOptions = {
      subject: `Quotation ${quotation.quote_number} from Noor Al-Fath`,
      message: getQuotationEmailTemplate(quotation),
      attachPdf: true,
      ...options
    }

    try {
      const response = await router.post(`/quotations/${quotation.id}/email`, {
        to: defaultOptions.to,
        subject: defaultOptions.subject,
        message: defaultOptions.message,
        attach_pdf: defaultOptions.attachPdf
      }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          // Handle success in calling component
        },
        onError: (errors) => {
          console.error('Email sending failed:', errors)
        }
      })
      
      return { success: true }
    } catch (error) {
      console.error('Email service error:', error)
      return { 
        success: false, 
        error: error instanceof Error ? error.message : 'Unknown error' 
      }
    }
  }

  const sendInvoiceEmail = async (invoice: Invoice, options: EmailOptions) => {
    const defaultOptions = {
      subject: `Invoice ${invoice.invoice_number} from Noor Al-Fath`,
      message: getInvoiceEmailTemplate(invoice),
      attachPdf: true,
      ...options
    }

    try {
      const response = await router.post(`/invoices/${invoice.id}/email`, {
        to: defaultOptions.to,
        subject: defaultOptions.subject,
        message: defaultOptions.message,
        attach_pdf: defaultOptions.attachPdf
      }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
          // Handle success in calling component
        },
        onError: (errors) => {
          console.error('Email sending failed:', errors)
        }
      })
      
      return { success: true }
    } catch (error) {
      console.error('Email service error:', error)
      return { 
        success: false, 
        error: error instanceof Error ? error.message : 'Unknown error' 
      }
    }
  }

  const getQuotationEmailTemplate = (quotation: Quotation): string => {
    return `Dear ${quotation.client.name},

I hope this email finds you well.

Please find attached quotation ${quotation.quote_number} dated ${new Date(quotation.issue_date).toLocaleDateString()}.

Quotation Details:
- Quote Number: ${quotation.quote_number}
- Issue Date: ${new Date(quotation.issue_date).toLocaleDateString()}
- Valid Until: ${new Date(quotation.valid_until).toLocaleDateString()}
- Total Amount: $${parseFloat(quotation.total_amount).toFixed(2)}

This quotation is valid until ${new Date(quotation.valid_until).toLocaleDateString()}. Please review the attached document and let us know if you have any questions or would like to proceed with the order.

We look forward to hearing from you soon.

Best regards,
Noor Al-Fath Team

---
This is an automated email. Please do not reply directly to this email address.`
  }

  const getInvoiceEmailTemplate = (invoice: Invoice): string => {
    return `Dear ${invoice.client.name},

I hope this email finds you well.

Please find attached invoice ${invoice.invoice_number} dated ${new Date(invoice.issue_date).toLocaleDateString()}.

Invoice Details:
- Invoice Number: ${invoice.invoice_number}
- Issue Date: ${new Date(invoice.issue_date).toLocaleDateString()}
- Due Date: ${new Date(invoice.due_date).toLocaleDateString()}
- Total Amount: $${parseFloat(invoice.total_amount).toFixed(2)}

Payment is due by ${new Date(invoice.due_date).toLocaleDateString()}. Please process payment at your earliest convenience to avoid any late fees.

If you have any questions about this invoice or need to discuss payment terms, please don't hesitate to contact us.

Thank you for your business.

Best regards,
Noor Al-Fath Team

---
This is an automated email. Please do not reply directly to this email address.`
  }

  const getEmailTemplates = (): Record<string, EmailTemplate> => {
    return {
      quotation_sent: {
        subject: 'New Quotation - {{quote_number}}',
        message: `Dear {{client_name}},

Please find attached quotation {{quote_number}} for your review.

This quotation is valid until {{valid_until}}. Please let us know if you have any questions.

Best regards,
Noor Al-Fath Team`
      },
      quotation_reminder: {
        subject: 'Quotation Reminder - {{quote_number}}',
        message: `Dear {{client_name}},

This is a friendly reminder about quotation {{quote_number}} that expires on {{valid_until}}.

Please review and let us know if you would like to proceed.

Best regards,
Noor Al-Fath Team`
      },
      invoice_sent: {
        subject: 'Invoice - {{invoice_number}}',
        message: `Dear {{client_name}},

Please find attached invoice {{invoice_number}}.

Payment is due by {{due_date}}. Thank you for your business.

Best regards,
Noor Al-Fath Team`
      },
      invoice_reminder: {
        subject: 'Payment Reminder - {{invoice_number}}',
        message: `Dear {{client_name}},

This is a reminder that payment for invoice {{invoice_number}} is due on {{due_date}}.

Please process payment at your earliest convenience.

Best regards,
Noor Al-Fath Team`
      },
      invoice_overdue: {
        subject: 'Overdue Notice - {{invoice_number}}',
        message: `Dear {{client_name}},

Invoice {{invoice_number}} is now overdue. The payment was due on {{due_date}}.

Please contact us immediately to resolve this matter.

Best regards,
Noor Al-Fath Team`
      }
    }
  }

  const applyTemplate = (template: string, variables: Record<string, string>): string => {
    let result = template
    for (const [key, value] of Object.entries(variables)) {
      result = result.replace(new RegExp(`{{${key}}}`, 'g'), value)
    }
    return result
  }

  return {
    sendQuotationEmail,
    sendInvoiceEmail,
    getQuotationEmailTemplate,
    getInvoiceEmailTemplate,
    getEmailTemplates,
    applyTemplate
  }
}
