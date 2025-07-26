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
      <g class="recharts-layer recharts-bar-graphical-item">
        <g
          v-for="(item, index) in chartData"
          :key="index"
          class="recharts-layer recharts-bar-rectangle"
        >
          <rect
            :width="barWidth"
            :height="getBarHeight(item[valueKey])"
            :x="getBarX(index)"
            :y="getBarY(item[valueKey])"
            :fill="getColor(index)"
            class="recharts-rectangle"
            stroke="none"
            rx="2"
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
            :x="getBarX(index) + barWidth / 2"
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

interface BarChartProps {
  data: any[]
  config: Record<string, any>
  width?: number
  height?: number
  className?: string
}

const props = withDefaults(defineProps<BarChartProps>(), {
  width: 400,
  height: 300
})

const chartData = computed(() => props.data)
const categoryKey = computed(() => props.config.categories?.[0] || Object.keys(props.data[0])[0])
const valueKey = computed(() => props.config.series?.[0]?.dataKey || Object.keys(props.data[0])[1])

const maxValue = computed(() => {
  return Math.max(...chartData.value.map(item => item[valueKey.value]))
})

const yAxisTicks = computed(() => {
  const max = maxValue.value
  const step = Math.ceil(max / 4)
  return [0, step, step * 2, step * 3, max]
})

const barWidth = computed(() => 70 / chartData.value.length)

const containerStyle = computed(() => ({
  width: `${props.width}px`,
  height: `${props.height}px`
}))

const getBarHeight = (value: number) => {
  return (value / maxValue.value) * 65
}

const getBarX = (index: number) => {
  return 15 + (index * (70 / chartData.value.length))
}

const getBarY = (value: number) => {
  return 85 - getBarHeight(value)
}

const getYPosition = (tick: number) => {
  return 85 - (tick / maxValue.value) * 65
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

.recharts-rectangle {
  transition: opacity 0.2s ease;
}

.recharts-rectangle:hover {
  opacity: 0.8;
}

.recharts-text {
  font-family: inherit;
}
</style>
