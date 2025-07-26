<template>
  <div :class="cn('chart-container', className)" v-bind="$attrs">
    <slot />
  </div>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils'

interface ChartContainerProps {
  className?: string
}

defineProps<ChartContainerProps>()
</script>
