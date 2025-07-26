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
      <!-- Area -->
      <g class="recharts-layer recharts-area">
        <defs>
          <linearGradient :id="`area-gradient-${uniqueId}`" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" :stop-color="getColor(0)" stop-opacity="0.3"/>
            <stop offset="100%" :stop-color="getColor(0)" stop-opacity="0.1"/>
          </linearGradient>
        </defs>
        
        <path
          :d="areaPath"
          :fill="`url(#area-gradient-${uniqueId})`"
          class="recharts-area-area"
        />
        
        <path
          :d="linePath"
          :stroke="getColor(0)"
          stroke-width="2"
          fill="none"
          class="recharts-area-curve"
        />
        
        <!-- Data points -->
        <g
          v-for="(point, index) in dataPoints"
          :key="index"
          class="recharts-layer recharts-area-dots"
        >
          <circle
            :cx="point.x"
            :cy="point.y"
            r="3"
            :fill="getColor(0)"
            stroke="hsl(var(--background))"
            stroke-width="2"
            class="recharts-dot"
          />
        </g>
      </g>
      
      <!-- X Axis -->
      <g class="recharts-layer recharts-cartesian-axis recharts-xAxis">
        <line
          x1="10"
          y1="85"
          x2="90"
          y2="85"
          stroke="currentColor"
          stroke-width="0.5"
          opacity="0.2"
        />
        <g v-for="(item, index) in chartData" :key="index" class="recharts-layer recharts-cartesian-axis-tick">
          <text
            :x="getXPosition(index)"
            y="90"
            text-anchor="middle"
            fill="currentColor"
            class="recharts-text"
            font-size="10"
            opacity="0.6"
          >
            {{ item[categoryKey] }}
          </text>
        </g>
      </g>
      
      <!-- Y Axis -->
      <g class="recharts-layer recharts-cartesian-axis recharts-yAxis">
        <line
          x1="10"
          y1="15"
          x2="10"
          y2="85"
          stroke="currentColor"
          stroke-width="0.5"
          opacity="0.2"
        />
        <g v-for="tick in yAxisTicks" :key="tick" class="recharts-layer recharts-cartesian-axis-tick">
          <text
            x="8"
            :y="getYPosition(tick)"
            text-anchor="end"
            fill="currentColor"
            class="recharts-text"
            font-size="10"
            opacity="0.6"
          >
            {{ tick }}
          </text>
        </g>
      </g>
    </svg>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface AreaChartProps {
  data: any[]
  config: Record<string, any>
  width?: number
  height?: number
  className?: string
}

const props = withDefaults(defineProps<AreaChartProps>(), {
  width: 400,
  height: 300
})

const uniqueId = Math.random().toString(36).substr(2, 9)

const chartData = computed(() => props.data)
const categoryKey = computed(() => props.config.categories?.[0] || Object.keys(props.data[0])[0])
const valueKey = computed(() => props.config.series?.[0]?.dataKey || Object.keys(props.data[0])[1])

const maxValue = computed(() => {
  return Math.max(...chartData.value.map(item => item[valueKey.value]))
})

const minValue = computed(() => {
  return Math.min(...chartData.value.map(item => item[valueKey.value]))
})

const yAxisTicks = computed(() => {
  const max = maxValue.value
  const min = minValue.value
  const step = Math.ceil((max - min) / 4)
  return [min, min + step, min + step * 2, min + step * 3, max]
})

const dataPoints = computed(() => {
  return chartData.value.map((item, index) => ({
    x: getXPosition(index),
    y: getYPosition(item[valueKey.value])
  }))
})

const linePath = computed(() => {
  const points = dataPoints.value
  if (points.length === 0) return ''
  
  let path = `M ${points[0].x} ${points[0].y}`
  
  for (let i = 1; i < points.length; i++) {
    const prev = points[i - 1]
    const current = points[i]
    const cpx1 = prev.x + (current.x - prev.x) / 3
    const cpy1 = prev.y
    const cpx2 = current.x - (current.x - prev.x) / 3
    const cpy2 = current.y
    
    path += ` C ${cpx1} ${cpy1}, ${cpx2} ${cpy2}, ${current.x} ${current.y}`
  }
  
  return path
})

const areaPath = computed(() => {
  const points = dataPoints.value
  if (points.length === 0) return ''
  
  let path = linePath.value
  path += ` L ${points[points.length - 1].x} 85`
  path += ` L ${points[0].x} 85`
  path += ' Z'
  
  return path
})

const containerStyle = computed(() => ({
  width: `${props.width}px`,
  height: `${props.height}px`
}))

const getXPosition = (index: number) => {
  const width = 80 // Available width between margins
  return 10 + (index / (chartData.value.length - 1)) * width
}

const getYPosition = (value: number) => {
  const height = 70 // Available height between margins
  const range = maxValue.value - minValue.value || 1
  return 85 - ((value - minValue.value) / range) * height
}

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

.recharts-area-curve {
  transition: stroke-width 0.2s ease;
}

.recharts-area-curve:hover {
  stroke-width: 3;
}

.recharts-dot {
  transition: r 0.2s ease;
}

.recharts-dot:hover {
  r: 4;
}

.recharts-text {
  font-family: inherit;
}
</style>
