<template>
  <div class="space-y-4">
    <!-- Selection Header -->
    <div v-if="selectedItems.length > 0" class="bg-blue-50 border border-blue-200 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <span class="text-sm font-medium text-blue-900">
            {{ selectedItems.length }} {{ type }}{{ selectedItems.length > 1 ? 's' : '' }} selected
          </span>
          <Button
            variant="outline"
            size="sm"
            @click="clearSelection"
          >
            Clear Selection
          </Button>
        </div>
        
        <div class="flex items-center space-x-2">
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="sm">
                <Settings class="w-4 h-4 mr-2" />
                Bulk Actions
                <ChevronDown class="w-4 h-4 ml-2" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48">
              <template v-if="type === 'quotation'">
                <DropdownMenuItem @click="bulkUpdateStatus('sent')">
                  <Send class="w-4 h-4 mr-2" />
                  Mark as Sent
                </DropdownMenuItem>
                <DropdownMenuItem @click="bulkUpdateStatus('accepted')">
                  <CheckCircle class="w-4 h-4 mr-2" />
                  Mark as Accepted
                </DropdownMenuItem>
                <DropdownMenuItem @click="bulkUpdateStatus('rejected')">
                  <X class="w-4 h-4 mr-2" />
                  Mark as Rejected
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="bulkConvertToInvoices">
                  <FileText class="w-4 h-4 mr-2" />
                  Convert to Invoices
                </DropdownMenuItem>
              </template>
              
              <template v-if="type === 'invoice'">
                <DropdownMenuItem @click="bulkUpdateStatus('sent')">
                  <Send class="w-4 h-4 mr-2" />
                  Mark as Sent
                </DropdownMenuItem>
                <DropdownMenuItem @click="bulkUpdateStatus('paid')">
                  <CheckCircle class="w-4 h-4 mr-2" />
                  Mark as Paid
                </DropdownMenuItem>
                <DropdownMenuItem @click="bulkUpdateStatus('overdue')">
                  <AlertCircle class="w-4 h-4 mr-2" />
                  Mark as Overdue
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem @click="bulkSendReminders">
                  <Mail class="w-4 h-4 mr-2" />
                  Send Reminders
                </DropdownMenuItem>
              </template>
              
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="bulkDownloadPdfs">
                <Download class="w-4 h-4 mr-2" />
                Download PDFs
              </DropdownMenuItem>
              <DropdownMenuItem @click="bulkExport">
                <FileSpreadsheet class="w-4 h-4 mr-2" />
                Export to CSV
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem @click="bulkDelete" class="text-red-600">
                <Trash2 class="w-4 h-4 mr-2" />
                Delete Selected
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </div>

    <!-- Bulk Action Progress -->
    <div v-if="bulkProgress.show" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="animate-spin">
            <Loader2 class="w-4 h-4" />
          </div>
          <span class="text-sm font-medium text-yellow-900">
            {{ bulkProgress.message }}
          </span>
        </div>
        <div class="text-sm text-yellow-700">
          {{ bulkProgress.current }}/{{ bulkProgress.total }}
        </div>
      </div>
      <div class="mt-2 bg-yellow-200 rounded-full h-2">
        <div
          class="bg-yellow-500 h-2 rounded-full transition-all duration-300"
          :style="{ width: `${(bulkProgress.current / bulkProgress.total) * 100}%` }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { useToast } from '@/composables/useToast'
import {
  Settings,
  ChevronDown,
  Send,
  CheckCircle,
  X,
  FileText,
  AlertCircle,
  Mail,
  Download,
  FileSpreadsheet,
  Trash2,
  Loader2,
} from 'lucide-vue-next'

interface Props {
  selectedItems: number[]
  type: 'quotation' | 'invoice' | 'product' | 'vendor' | 'expense'
}

interface Emits {
  (e: 'clear-selection'): void
  (e: 'refresh'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const form = useForm({})
const { success, error } = useToast()

const bulkProgress = ref({
  show: false,
  message: '',
  current: 0,
  total: 0,
})

const clearSelection = () => {
  emit('clear-selection')
}

const showProgress = (message: string, total: number) => {
  bulkProgress.value = {
    show: true,
    message,
    current: 0,
    total,
  }
}

const updateProgress = (current: number) => {
  bulkProgress.value.current = current
}

const hideProgress = () => {
  bulkProgress.value.show = false
}

const bulkUpdateStatus = (status: string) => {
  if (!confirm(`Are you sure you want to update ${props.selectedItems.length} ${props.type}(s) to ${status}?`)) {
    return
  }

  showProgress(`Updating ${props.type}s to ${status}...`, props.selectedItems.length)

  form.post(`/${props.type}s/bulk-update-status`, {
    data: {
      ids: props.selectedItems,
      status,
    },
    onSuccess: () => {
      hideProgress()
      success('Success!', `${props.selectedItems.length} ${props.type}(s) updated successfully`)
      emit('refresh')
      emit('clear-selection')
    },
    onError: () => {
      hideProgress()
      error('Error!', `Failed to update ${props.type}s`)
    },
  })
}

const bulkConvertToInvoices = () => {
  if (!confirm(`Convert ${props.selectedItems.length} quotation(s) to invoices?`)) {
    return
  }

  showProgress('Converting quotations to invoices...', props.selectedItems.length)

  form.post('/quotations/bulk-convert-to-invoices', {
    data: {
      ids: props.selectedItems,
    },
    onSuccess: () => {
      hideProgress()
      success('Success!', `${props.selectedItems.length} quotation(s) converted to invoices`)
      emit('refresh')
      emit('clear-selection')
    },
    onError: () => {
      hideProgress()
      error('Error!', 'Failed to convert quotations')
    },
  })
}

const bulkSendReminders = () => {
  if (!confirm(`Send payment reminders for ${props.selectedItems.length} invoice(s)?`)) {
    return
  }

  showProgress('Sending payment reminders...', props.selectedItems.length)

  form.post('/invoices/bulk-send-reminders', {
    data: {
      ids: props.selectedItems,
    },
    onSuccess: () => {
      hideProgress()
      success('Success!', `Reminders sent for ${props.selectedItems.length} invoice(s)`)
      emit('refresh')
      emit('clear-selection')
    },
    onError: () => {
      hideProgress()
      error('Error!', 'Failed to send reminders')
    },
  })
}

const bulkDownloadPdfs = async () => {
  showProgress('Generating PDFs...', props.selectedItems.length)

  try {
    // This would typically be a backend endpoint that generates a ZIP file
    const response = await fetch(`/${props.type}s/bulk-download-pdfs`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        ids: props.selectedItems,
      }),
    })

    if (response.ok) {
      const blob = await response.blob()
      const url = window.URL.createObjectURL(blob)
      const a = document.createElement('a')
      a.href = url
      a.download = `${props.type}s_${new Date().toISOString().split('T')[0]}.zip`
      document.body.appendChild(a)
      a.click()
      window.URL.revokeObjectURL(url)
      document.body.removeChild(a)

      hideProgress()
      success('Success!', 'PDFs downloaded successfully')
      emit('clear-selection')
    } else {
      throw new Error('Download failed')
    }
  } catch (err) {
    hideProgress()
    error('Error!', 'Failed to download PDFs')
  }
}

const bulkExport = () => {
  showProgress('Exporting data...', props.selectedItems.length)

  form.post(`/${props.type}s/bulk-export`, {
    data: {
      ids: props.selectedItems,
    },
    onSuccess: () => {
      hideProgress()
      success('Success!', 'Data exported successfully')
      emit('clear-selection')
    },
    onError: () => {
      hideProgress()
      error('Error!', 'Failed to export data')
    },
  })
}

const bulkDelete = () => {
  if (!confirm(`Are you sure you want to delete ${props.selectedItems.length} ${props.type}(s)? This action cannot be undone.`)) {
    return
  }

  showProgress(`Deleting ${props.type}s...`, props.selectedItems.length)

  form.delete(`/${props.type}s/bulk-delete`, {
    data: {
      ids: props.selectedItems,
    },
    onSuccess: () => {
      hideProgress()
      success('Success!', `${props.selectedItems.length} ${props.type}(s) deleted successfully`)
      emit('refresh')
      emit('clear-selection')
    },
    onError: () => {
      hideProgress()
      error('Error!', `Failed to delete ${props.type}s`)
    },
  })
}
</script>
