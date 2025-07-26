import { jsPDF } from 'jspdf'
import html2canvas from 'html2canvas'
import type { Quotation, Invoice, Product } from '@/types'

interface PdfOptions {
  filename?: string
  format?: 'a4' | 'letter'
  orientation?: 'portrait' | 'landscape'
  quality?: number
  payment?: {
    id: number
    amount: number
    payment_method: string
    payment_date: string
    status: string
    notes?: string
    receipt_path?: string
  }
}

export function usePdfGenerator() {
  const generateQuotationPdf = async (quotation: Quotation, options: PdfOptions = {}) => {
    const {
      filename = `quotation-${quotation.quote_number}.pdf`,
      format = 'a4',
      orientation = 'portrait',
      quality = 0.98
    } = options

    try {
      // Create PDF document
      const pdf = new jsPDF({
        orientation,
        unit: 'mm',
        format
      })

      // Add company header
      pdf.setFontSize(20)
      pdf.setFont('helvetica', 'bold')
      pdf.text('Noor Al-Fath', 20, 25)
      
      pdf.setFontSize(12)
      pdf.setFont('helvetica', 'normal')
      pdf.text('Business Management System', 20, 35)
      
      // Add quotation details
      pdf.setFontSize(16)
      pdf.setFont('helvetica', 'bold')
      pdf.text('QUOTATION', 20, 55)
      
      pdf.setFontSize(10)
      pdf.setFont('helvetica', 'normal')
      
      // Quote details in two columns
      const leftCol = 20
      const rightCol = 120
      let yPos = 70
      
      pdf.text(`Quote Number: ${quotation.quote_number}`, leftCol, yPos)
      pdf.text(`Date: ${new Date(quotation.created_at).toLocaleDateString()}`, rightCol, yPos)
      
      yPos += 8
      pdf.text(`Status: ${quotation.status.toUpperCase()}`, leftCol, yPos)
      pdf.text(`Valid Until: ${new Date(quotation.valid_until).toLocaleDateString()}`, rightCol, yPos)
      
      // Client information
      yPos += 15
      pdf.setFont('helvetica', 'bold')
      pdf.text('Bill To:', leftCol, yPos)
      
      yPos += 8
      pdf.setFont('helvetica', 'normal')
      pdf.text(quotation.client.name, leftCol, yPos)
      if (quotation.client.email) {
        yPos += 6
        pdf.text(quotation.client.email, leftCol, yPos)
      }
      if (quotation.client.phone) {
        yPos += 6
        pdf.text(quotation.client.phone, leftCol, yPos)
      }
      
      // Items table
      yPos += 20
      const tableStartY = yPos
      
      // Table headers
      pdf.setFont('helvetica', 'bold')
      pdf.setFillColor(240, 240, 240)
      pdf.rect(leftCol, yPos - 5, 170, 10, 'F')
      
      pdf.text('Item', leftCol + 2, yPos)
      pdf.text('Qty', leftCol + 80, yPos)
      pdf.text('Rate', leftCol + 100, yPos)
      pdf.text('Amount', leftCol + 140, yPos)
      
      yPos += 12
      pdf.setFont('helvetica', 'normal')
      
      // Table rows
      quotation.items.forEach((item: any) => {
        if (yPos > 250) {
          pdf.addPage()
          yPos = 30
        }
        
        pdf.text(item.product.name, leftCol + 2, yPos)
        pdf.text(item.quantity.toString(), leftCol + 80, yPos)
        pdf.text(`$${parseFloat(item.unit_price).toFixed(2)}`, leftCol + 100, yPos)
        pdf.text(`$${(item.quantity * parseFloat(item.unit_price)).toFixed(2)}`, leftCol + 140, yPos)
        
        yPos += 8
      })
      
      // Totals
      yPos += 10
      pdf.line(leftCol + 100, yPos, leftCol + 170, yPos)
      
      yPos += 8
      const subtotal = quotation.items.reduce((sum: number, item: any) => 
        sum + (item.quantity * parseFloat(item.unit_price)), 0)
      
      pdf.text('Subtotal:', leftCol + 120, yPos)
      pdf.text(`$${subtotal.toFixed(2)}`, leftCol + 150, yPos)
      
      if (quotation.tax_amount && parseFloat(quotation.tax_amount) > 0) {
        yPos += 6
        pdf.text('Tax:', leftCol + 120, yPos)
        pdf.text(`$${parseFloat(quotation.tax_amount).toFixed(2)}`, leftCol + 150, yPos)
      }
      
      if (quotation.discount_amount && parseFloat(quotation.discount_amount) > 0) {
        yPos += 6
        pdf.text('Discount:', leftCol + 120, yPos)
        pdf.text(`-$${parseFloat(quotation.discount_amount).toFixed(2)}`, leftCol + 150, yPos)
      }
      
      yPos += 8
      pdf.setFont('helvetica', 'bold')
      pdf.text('Total:', leftCol + 120, yPos)
      pdf.text(`$${parseFloat(quotation.total_amount).toFixed(2)}`, leftCol + 150, yPos)
      
      // Notes
      if (quotation.notes) {
        yPos += 20
        pdf.setFont('helvetica', 'bold')
        pdf.text('Notes:', leftCol, yPos)
        yPos += 8
        pdf.setFont('helvetica', 'normal')
        const splitNotes = pdf.splitTextToSize(quotation.notes, 170)
        pdf.text(splitNotes, leftCol, yPos)
      }
      
      // Footer
      const pageHeight = pdf.internal.pageSize.height
      pdf.setFontSize(8)
      pdf.text('Thank you for your business!', leftCol, pageHeight - 20)
      
      // Save the PDF
      pdf.save(filename)
      
      return { success: true, filename }
    } catch (error) {
      console.error('PDF generation failed:', error)
      return { success: false, error: error instanceof Error ? error.message : 'Unknown error' }
    }
  }

  const generateInvoicePdf = async (invoice: Invoice, options: PdfOptions = {}) => {
    const {
      filename = options.payment 
        ? `invoice-${invoice.invoice_number}-payment-${options.payment.id}.pdf`
        : `invoice-${invoice.invoice_number}.pdf`,
      format = 'a4',
      orientation = 'portrait',
      payment = options.payment
    } = options

    try {
      const pdf = new jsPDF({
        orientation,
        unit: 'mm',
        format
      })

      const pageWidth = pdf.internal.pageSize.getWidth()
      const margin = 15
      
      // Company Logo (placeholder - you can add actual logo later)
      pdf.setFillColor(0, 100, 200)
      pdf.rect(margin, 10, 30, 20, 'F')
      pdf.setTextColor(255, 255, 255)
      pdf.setFontSize(12)
      pdf.setFont('helvetica', 'bold')
      pdf.text('LOGO', margin + 8, 23)
      
      // Company Info (Right side)
      pdf.setTextColor(0, 0, 0)
      pdf.setFontSize(14)
      pdf.setFont('helvetica', 'bold')
      pdf.text('NOOR ALFATH TECHNICAL SERVICES L.L.C.', pageWidth - margin, 15, { align: 'right' })
      
      pdf.setFontSize(9)
      pdf.setFont('helvetica', 'normal')
      pdf.text('Mob: +971 529746117, +971554311302', pageWidth - margin, 22, { align: 'right' })
      pdf.text('Email: technicalservices@nooralfath.com', pageWidth - margin, 27, { align: 'right' })
      pdf.text('Al Quoz 1 street 27B office 34', pageWidth - margin, 32, { align: 'right' })
      pdf.text('Dubai, United Arab Emirates', pageWidth - margin, 37, { align: 'right' })
      pdf.text('TRN: 104124300500003', pageWidth - margin, 42, { align: 'right' })
      
      // Invoice Title
      let yPos = 55
      pdf.setFontSize(18)
      pdf.setFont('helvetica', 'bold')
      const title = payment ? 'PAYMENT RECEIPT' : 'INVOICE'
      pdf.text(title, pageWidth / 2, yPos, { align: 'center' })
      
      yPos += 15
      
      // Client Info (Left side)
      pdf.setFontSize(12)
      pdf.setFont('helvetica', 'bold')
      pdf.text('Bill To:', margin, yPos)
      
      yPos += 8
      pdf.setFont('helvetica', 'normal')
      pdf.text(invoice.client.name, margin, yPos)
      
      if (invoice.client.company) {
        yPos += 6
        pdf.text(invoice.client.company, margin, yPos)
      }
      
      if (invoice.client.address) {
        yPos += 6
        pdf.text(invoice.client.address, margin, yPos)
      }
      
      if (invoice.client.email) {
        yPos += 6
        pdf.text(`Email: ${invoice.client.email}`, margin, yPos)
      }
      
      if (invoice.client.phone) {
        yPos += 6
        pdf.text(`Tel: ${invoice.client.phone}`, margin, yPos)
      }
      
      // Invoice Details (Right side)
      const rightX = pageWidth - 60
      let rightY = 70
      
      pdf.setFont('helvetica', 'bold')
      pdf.text('Invoice Details:', rightX, rightY)
      
      rightY += 8
      pdf.setFont('helvetica', 'normal')
      pdf.text(`Invoice #: ${invoice.invoice_number}`, rightX, rightY)
      
      rightY += 6
      pdf.text(`Issue Date: ${new Date(invoice.issue_date).toLocaleDateString()}`, rightX, rightY)
      
      rightY += 6
      pdf.text(`Due Date: ${new Date(invoice.due_date).toLocaleDateString()}`, rightX, rightY)
      
      rightY += 6
      pdf.text(`Status: ${invoice.status.toUpperCase()}`, rightX, rightY)
      
      if (payment) {
        rightY += 6
        pdf.text(`Payment Date: ${new Date(payment.payment_date).toLocaleDateString()}`, rightX, rightY)
        
        rightY += 6
        pdf.text(`Payment Method: ${payment.payment_method.replace(/_/g, ' ').toUpperCase()}`, rightX, rightY)
      }
      
      // Items Table
      yPos = Math.max(yPos + 20, rightY + 15)
      
      // Table Headers
      pdf.setFont('helvetica', 'bold')
      pdf.setFillColor(240, 240, 240)
      pdf.rect(margin, yPos, pageWidth - 2 * margin, 8, 'F')
      
      const colWidths = [80, 25, 30, 20, 30]
      const colX = [margin, margin + colWidths[0], margin + colWidths[0] + colWidths[1], 
                   margin + colWidths[0] + colWidths[1] + colWidths[2],
                   margin + colWidths[0] + colWidths[1] + colWidths[2] + colWidths[3]]
      
      yPos += 6
      pdf.text('Description', colX[0] + 2, yPos)
      pdf.text('Qty', colX[1] + 2, yPos)
      pdf.text('Unit Price', colX[2] + 2, yPos)
      pdf.text('VAT %', colX[3] + 2, yPos)
      pdf.text('Total', colX[4] + 2, yPos)
      
      // Table Rows
      yPos += 8
      pdf.setFont('helvetica', 'normal')
      
      let subtotal = 0
      let totalTax = 0
      
      invoice.items.forEach((item: any) => {
        if (yPos > 250) {
          pdf.addPage()
          yPos = 20
        }
        
        // Use product.name for description, handle both old and new structure
        const description = (item as any).description || item.product?.name || 'Product'
        const unitPrice = typeof item.unit_price === 'string' ? parseFloat(item.unit_price) : item.unit_price
        const vatRate = (item as any).vat_rate || 0
        const totalPrice = parseFloat((item as any).total_price_w_tax) || (item.quantity * unitPrice)
        
        pdf.text(description, colX[0] + 2, yPos)
        pdf.text(item.quantity.toString(), colX[1] + 2, yPos)
        pdf.text(`$${unitPrice.toFixed(2)}`, colX[2] + 2, yPos)
        pdf.text(`${vatRate}%`, colX[3] + 2, yPos)
        pdf.text(`$${totalPrice.toFixed(2)}`, colX[4] + 2, yPos)
        
        const basePrice = parseFloat((item as any).total_price) || (item.quantity * unitPrice)
        subtotal += basePrice
        totalTax += totalPrice - basePrice
        
        yPos += 8
      })
      
      // Totals
      yPos += 10
      const totalsX = pageWidth - 70
      
      pdf.setFont('helvetica', 'normal')
      pdf.text(`Subtotal: $${subtotal.toFixed(2)}`, totalsX, yPos)
      
      yPos += 6
      pdf.text(`Tax: $${totalTax.toFixed(2)}`, totalsX, yPos)
      
      yPos += 6
      pdf.setFont('helvetica', 'bold')
      pdf.text(`Total: $${parseFloat(invoice.total_amount).toFixed(2)}`, totalsX, yPos)
      
      // Payment Information (if payment provided)
      if (payment) {
        yPos += 15
        pdf.setFont('helvetica', 'bold')
        pdf.text('Payment Information:', margin, yPos)
        
        yPos += 8
        pdf.setFont('helvetica', 'normal')
        pdf.text(`Payment Amount: $${parseFloat(payment.amount.toString()).toFixed(2)}`, margin, yPos)
        
        yPos += 6
        pdf.text(`Payment Method: ${payment.payment_method.replace(/_/g, ' ')}`, margin, yPos)
        
        yPos += 6
        pdf.text(`Payment Date: ${new Date(payment.payment_date).toLocaleDateString()}`, margin, yPos)
        
        yPos += 6
        pdf.text(`Status: ${payment.status.toUpperCase()}`, margin, yPos)
        
        if (payment.notes) {
          yPos += 6
          pdf.text(`Notes: ${payment.notes}`, margin, yPos)
        }
      }
      
      // Bank Details
      yPos += 20
      pdf.setFont('helvetica', 'bold')
      pdf.text('BANK DETAILS:', margin, yPos)
      
      yPos += 8
      pdf.setFont('helvetica', 'normal')
      pdf.text('Account Title: NOOR ALFATH TECHNICAL SERVICES L.L.C.', margin, yPos)
      
      yPos += 6
      pdf.text('Account #: 019101343075', margin, yPos)
      
      yPos += 6
      pdf.text('IBAN #: AE160330000019101343075', margin, yPos)
      
      yPos += 6
      pdf.text('SWIFT CODE: BOMLAEAD', margin, yPos)
      
      // Signature Section
      yPos += 20
      pdf.text('Authorized Signature & Stamp:', margin, yPos)
      
      // Add a box for signature
      pdf.rect(margin, yPos + 5, 60, 20)
      
      pdf.save(filename)
      return { success: true, filename }
      
    } catch (error) {
      console.error('PDF generation failed:', error)
      return { success: false, error: error instanceof Error ? error.message : 'Unknown error' }
    }
  }

  const generateFromElement = async (elementId: string, filename: string, options: PdfOptions = {}) => {
    const {
      format = 'a4',
      orientation = 'portrait',
      quality = 0.98
    } = options

    try {
      const element = document.getElementById(elementId)
      if (!element) {
        throw new Error(`Element with ID '${elementId}' not found`)
      }

      const canvas = await html2canvas(element, {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff'
      })

      const imgData = canvas.toDataURL('image/png', quality)
      const pdf = new jsPDF({
        orientation,
        unit: 'mm',
        format
      })

      const pdfWidth = pdf.internal.pageSize.getWidth()
      const pdfHeight = pdf.internal.pageSize.getHeight()
      const imgWidth = canvas.width
      const imgHeight = canvas.height
      const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight)
      const imgX = (pdfWidth - imgWidth * ratio) / 2
      const imgY = 0

      pdf.addImage(imgData, 'PNG', imgX, imgY, imgWidth * ratio, imgHeight * ratio)
      pdf.save(filename)

      return { success: true, filename }
    } catch (error) {
      console.error('PDF generation from element failed:', error)
      return { success: false, error: error instanceof Error ? error.message : 'Unknown error' }
    }
  }

  return {
    generateQuotationPdf,
    generateInvoicePdf,
    generateFromElement
  }
}
