<template>
  <div
    v-if="config"
    :style="containerStyle"
    class="recharts-wrapper"
    dir="ltr"
  >
    <svg
      class="recharts-surface"
      :width="width"
      :height="height"
      viewBox="0 0 100 100"
      :style="containerStyle"
    >
      <g class="recharts-layer recharts-pie">
        <g
          v-for="(slice, index) in pieSlices"
          :key="index"
          class="recharts-layer recharts-pie-sector"
        >
          <path
            :d="slice.path"
            :fill="getColor(index)"
            class="recharts-sector"
            stroke="hsl(var(--background))"
            stroke-width="2"
          />
        </g>
        
        <!-- Center hole for donut -->
        <circle
          cx="50"
          cy="50"
          :r="innerRadius"
          fill="hsl(var(--background))"
        />
      </g>
      
      <!-- Labels -->
      <g class="recharts-layer recharts-pie-labels">
        <text
          v-for="(slice, index) in pieSlices"
          :key="index"
          :x="slice.labelX"
          :y="slice.labelY"
          text-anchor="middle"
          fill="currentColor"
          class="recharts-text"
          font-size="8"
          opacity="0.8"
        >
          {{ slice.percentage }}%
        </text>
      </g>
    </svg>
    
    <!-- Legend -->
    <div class="mt-4 flex flex-wrap justify-center gap-2">
      <div
        v-for="(item, index) in chartData"
        :key="index"
        class="flex items-center gap-1 text-sm"
      >
        <div
          class="h-3 w-3 rounded-full"
          :style="{ backgroundColor: getColor(index) }"
        ></div>
        <span class="text-muted-foreground">{{ item[categoryKey] }}</span>
        <span class="font-medium">{{ item[valueKey] }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface DonutChartProps {
  data: any[]
  config: Record<string, any>
  width?: number
  height?: number
  className?: string
}

const props = withDefaults(defineProps<DonutChartProps>(), {
  width: 300,
  height: 300
})

const chartData = computed(() => props.data)
const categoryKey = computed(() => props.config.categories?.[0] || Object.keys(props.data[0])[0])
const valueKey = computed(() => props.config.series?.[0]?.dataKey || Object.keys(props.data[0])[1])

const totalValue = computed(() => {
  return chartData.value.reduce((sum, item) => sum + item[valueKey.value], 0)
})

const outerRadius = 35
const innerRadius = 20

const pieSlices = computed(() => {
  let currentAngle = -90 // Start from top
  
  return chartData.value.map((item, index) => {
    const value = item[valueKey.value]
    const percentage = Math.round((value / totalValue.value) * 100)
    const angle = (value / totalValue.value) * 360
    
    const startAngle = currentAngle
    const endAngle = currentAngle + angle
    
    const startAngleRad = (startAngle * Math.PI) / 180
    const endAngleRad = (endAngle * Math.PI) / 180
    
    const x1 = 50 + outerRadius * Math.cos(startAngleRad)
    const y1 = 50 + outerRadius * Math.sin(startAngleRad)
    const x2 = 50 + outerRadius * Math.cos(endAngleRad)
    const y2 = 50 + outerRadius * Math.sin(endAngleRad)
    
    const x3 = 50 + innerRadius * Math.cos(endAngleRad)
    const y3 = 50 + innerRadius * Math.sin(endAngleRad)
    const x4 = 50 + innerRadius * Math.cos(startAngleRad)
    const y4 = 50 + innerRadius * Math.sin(startAngleRad)
    
    const largeArcFlag = angle > 180 ? 1 : 0
    
    const path = [
      `M ${x1} ${y1}`,
      `A ${outerRadius} ${outerRadius} 0 ${largeArcFlag} 1 ${x2} ${y2}`,
      `L ${x3} ${y3}`,
      `A ${innerRadius} ${innerRadius} 0 ${largeArcFlag} 0 ${x4} ${y4}`,
      'Z'
    ].join(' ')
    
    // Label position (middle of the slice)
    const labelAngle = startAngle + angle / 2
    const labelAngleRad = (labelAngle * Math.PI) / 180
    const labelRadius = (outerRadius + innerRadius) / 2
    const labelX = 50 + labelRadius * Math.cos(labelAngleRad)
    const labelY = 50 + labelRadius * Math.sin(labelAngleRad)
    
    currentAngle += angle
    
    return {
      path,
      percentage,
      labelX,
      labelY: labelY + 2 // Adjust for text baseline
    }
  })
})

const containerStyle = computed(() => ({
  width: `${props.width}px`,
  height: `${props.height}px`
}))

const getColor = (index: number) => {
  const colors = [
    'hsl(var(--chart-1))',
    'hsl(var(--chart-2))',
    'hsl(var(--chart-3))',
    'hsl(var(--chart-4))',
    'hsl(var(--chart-5))'
  ]
  return colors[index % colors.length]
}
</script>

<style scoped>
.recharts-wrapper {
  user-select: none;
  outline: none;
}

.recharts-surface {
  overflow: visible;
}

.recharts-sector {
  transition: opacity 0.2s ease;
}

.recharts-sector:hover {
  opacity: 0.8;
}

.recharts-text {
  font-family: inherit;
}
</style>
