import { jsPDF } from 'jspdf'
import html2canvas from 'html2canvas'
import type { Quotation, Invoice, Product } from '@/types'

interface PdfOptions {
  filename?: string
  format?: 'a4' | 'letter'
  orientation?: 'portrait' | 'landscape'
  quality?: number
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
      filename = `invoice-${invoice.invoice_number}.pdf`,
      format = 'a4',
      orientation = 'portrait'
    } = options

    try {
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
      
      // Add invoice details
      pdf.setFontSize(16)
      pdf.setFont('helvetica', 'bold')
      pdf.text('INVOICE', 20, 55)
      
      pdf.setFontSize(10)
      pdf.setFont('helvetica', 'normal')
      
      const leftCol = 20
      const rightCol = 120
      let yPos = 70
      
      pdf.text(`Invoice Number: ${invoice.invoice_number}`, leftCol, yPos)
      pdf.text(`Date: ${new Date(invoice.created_at).toLocaleDateString()}`, rightCol, yPos)
      
      yPos += 8
      pdf.text(`Status: ${invoice.status.toUpperCase()}`, leftCol, yPos)
      pdf.text(`Due Date: ${new Date(invoice.due_date).toLocaleDateString()}`, rightCol, yPos)
      
      // Client information
      yPos += 15
      pdf.setFont('helvetica', 'bold')
      pdf.text('Bill To:', leftCol, yPos)
      
      yPos += 8
      pdf.setFont('helvetica', 'normal')
      pdf.text(invoice.client.name, leftCol, yPos)
      if (invoice.client.email) {
        yPos += 6
        pdf.text(invoice.client.email, leftCol, yPos)
      }
      if (invoice.client.phone) {
        yPos += 6
        pdf.text(invoice.client.phone, leftCol, yPos)
      }
      
      // Items table
      yPos += 20
      
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
      invoice.items.forEach((item: any) => {
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
      const subtotal = invoice.items.reduce((sum: number, item: any) => 
        sum + (item.quantity * parseFloat(item.unit_price)), 0)
      
      pdf.text('Subtotal:', leftCol + 120, yPos)
      pdf.text(`$${subtotal.toFixed(2)}`, leftCol + 150, yPos)
      
      if (invoice.tax_amount && parseFloat(invoice.tax_amount) > 0) {
        yPos += 6
        pdf.text('Tax:', leftCol + 120, yPos)
        pdf.text(`$${parseFloat(invoice.tax_amount).toFixed(2)}`, leftCol + 150, yPos)
      }
      
      if (invoice.discount_amount && parseFloat(invoice.discount_amount) > 0) {
        yPos += 6
        pdf.text('Discount:', leftCol + 120, yPos)
        pdf.text(`-$${parseFloat(invoice.discount_amount).toFixed(2)}`, leftCol + 150, yPos)
      }
      
      yPos += 8
      pdf.setFont('helvetica', 'bold')
      pdf.text('Total:', leftCol + 120, yPos)
      pdf.text(`$${parseFloat(invoice.total_amount).toFixed(2)}`, leftCol + 150, yPos)
      
      // Payment terms
      if (invoice.notes) {
        yPos += 20
        pdf.setFont('helvetica', 'bold')
        pdf.text('Payment Terms:', leftCol, yPos)
        yPos += 8
        pdf.setFont('helvetica', 'normal')
        const splitNotes = pdf.splitTextToSize(invoice.notes, 170)
        pdf.text(splitNotes, leftCol, yPos)
      }
      
      // Footer
      const pageHeight = pdf.internal.pageSize.height
      pdf.setFontSize(8)
      pdf.text('Thank you for your business!', leftCol, pageHeight - 20)
      
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
