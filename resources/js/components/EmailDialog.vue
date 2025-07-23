<template>
  <Dialog v-model:open="open">
    <DialogContent class="sm:max-w-lg">
      <DialogHeader>
        <DialogTitle>
          Send {{ type === 'quotation' ? 'Quotation' : 'Invoice' }} via Email
        </DialogTitle>
        <DialogDescription>
          Send this {{ type }} to a client or team member via email with optional PDF attachment.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="sendEmail" class="space-y-4">
        <div class="space-y-2">
          <Label for="to">Email Address</Label>
          <Input
            id="to"
            v-model="form.to"
            type="email"
            placeholder="Enter email address"
            required
          />
          <InputError :message="form.errors.to" />
        </div>

        <div class="space-y-2">
          <Label for="subject">Subject</Label>
          <Input
            id="subject"
            v-model="form.subject"
            placeholder="Email subject"
            required
          />
          <InputError :message="form.errors.subject" />
        </div>

        <div class="space-y-2">
          <Label for="message">Message</Label>
          <Textarea
            id="message"
            v-model="form.message"
            placeholder="Enter your message"
            rows="6"
            required
          />
          <InputError :message="form.errors.message" />
        </div>

        <div class="flex items-center space-x-2">
          <Checkbox
            id="attach_pdf"
            v-model:checked="form.attach_pdf"
          />
          <Label for="attach_pdf" class="text-sm font-medium">
            Attach PDF {{ type }}
          </Label>
        </div>

        <InputError :message="form.errors.email" />

        <DialogFooter>
          <Button
            type="button"
            variant="outline"
            @click="$emit('close')"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
          >
            <Mail class="mr-2 h-4 w-4" />
            {{ form.processing ? 'Sending...' : 'Send Email' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Checkbox } from '@/components/ui/checkbox'
import InputError from '@/components/InputError.vue'
import { Mail } from 'lucide-vue-next'
import type { Quotation, Invoice } from '@/types'

interface Props {
  open: boolean
  type: 'quotation' | 'invoice'
  data: Quotation | Invoice
}

interface Emits {
  (e: 'close'): void
  (e: 'update:open', value: boolean): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Form data
const form = useForm({
  to: '',
  subject: '',
  message: '',
  attach_pdf: true,
})

// Computed properties
const documentNumber = computed(() => {
  if (props.type === 'quotation') {
    return (props.data as Quotation).quote_number
  }
  return (props.data as Invoice).invoice_number
})

const defaultSubject = computed(() => {
  const docType = props.type === 'quotation' ? 'Quotation' : 'Invoice'
  return `${docType} ${documentNumber.value} from Noor Al-Fath`
})

const defaultMessage = computed(() => {
  if (props.type === 'quotation') {
    const quotation = props.data as Quotation
    return `Dear ${quotation.client.name},

I hope this email finds you well.

Please find attached quotation ${quotation.quote_number} for your review.

Quotation Details:
- Quote Number: ${quotation.quote_number}
- Issue Date: ${new Date(quotation.issue_date).toLocaleDateString()}
- Valid Until: ${new Date(quotation.valid_until).toLocaleDateString()}
- Total Amount: $${parseFloat(quotation.total_amount).toFixed(2)}

This quotation is valid until ${new Date(quotation.valid_until).toLocaleDateString()}. Please review the attached document and let us know if you have any questions or would like to proceed with the order.

We look forward to hearing from you soon.

Best regards,
Noor Al-Fath Team`
  } else {
    const invoice = props.data as Invoice
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
Noor Al-Fath Team`
  }
})

// Watch for dialog open/close
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    // Reset and populate form when dialog opens
    form.reset()
    form.to = props.data.client.email || ''
    form.subject = defaultSubject.value
    form.message = defaultMessage.value
    form.attach_pdf = true
  }
})

// Methods
const sendEmail = () => {
  const route = props.type === 'quotation' 
    ? `quotations/${props.data.id}/email`
    : `invoices/${props.data.id}/email`

  form.post(route, {
    preserveScroll: true,
    onSuccess: () => {
      emit('close')
      form.reset()
    },
  })
}
</script>
