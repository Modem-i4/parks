import { getMarkerTitle } from '../Maps/GetMarkerTitle.js'

export const greenStateLabels = {
  good: 'Добрий',
  normal: 'Задовільний',
  bad: 'Незадовільний',
  planned: 'Лунка',
  removed: 'Видалено',
}

export const greenStateSummaryLabels = {
  good: 'Хороший',
  normal: 'Задовільний',
  bad: 'Незадовільний',
  planned: 'Лунка',
  removed: 'Видалено',
}

export const greenStateColors = {
  good: '#22c55e',
  normal: '#fcd45b',
  bad: '#f87171',
  planned: '#66a9ff',
  removed: '#111827',
}

export const greenStateTextColors = {
  good: '#fff',
  normal: '#111827',
  bad: '#fff',
  planned: '#fff',
  removed: '#fff',
}

export const markerTypeLabels = {
  tree: 'Дерева',
  bush: 'Кущі',
  hedge: 'Живоплоти',
  flower: 'Клумби',
  infrastructure: 'Інфраструктура',
}

export const markerTypeOrder = ['tree', 'bush', 'hedge', 'flower', 'infrastructure']

const collator = new Intl.Collator('uk-UA', { sensitivity: 'base' })

export function formatDate(value) {
  if (!value) return ''
  return String(value).split('T')[0]
}

export function formatPlantingDate(value) {
  const date = formatDate(value)
  return /^\d{4}-01-01$/.test(date) ? date.slice(0, 4) : date
}

export function plotName(marker) {
  return marker.green?.subplot?.plot?.name || marker.green?.plot?.name || '—'
}

export function subplotName(marker) {
  return marker.green?.subplot?.name || '—'
}

export function speciesName(marker) {
  return marker.green?.species?.name_ukr || getMarkerTitle(marker) || '—'
}

function treeValue(marker, field) {
  return marker.green?.tree?.[field] ?? null
}

export function numericTreeValue(marker, field) {
  const value = Number(treeValue(marker, field))
  return Number.isFinite(value) && value > 0 ? value : null
}

export function measureRanges(markers) {
  return {
    trunk_diameter_cm: valueRange(markers.map(marker => numericTreeValue(marker, 'trunk_diameter_cm'))),
    height_m: valueRange(markers.map(marker => numericTreeValue(marker, 'height_m'))),
  }
}

function valueRange(values) {
  const finiteValues = values.filter(value => Number.isFinite(value))
  if (!finiteValues.length) return null

  return {
    min: Math.min(...finiteValues),
    max: Math.max(...finiteValues),
  }
}

export function heatColor(value, range) {
  if (!Number.isFinite(value) || !range) return ''

  const ratio = range.max === range.min
    ? 0.5
    : Math.max(0, Math.min(1, (value - range.min) / (range.max - range.min)))

  if (ratio <= 0.5) {
    const localRatio = ratio / 0.5
    return mixColor([187, 247, 208], [254, 240, 138], localRatio)
  }

  return mixColor([254, 240, 138], [254, 202, 202], (ratio - 0.5) / 0.5)
}

function mixColor(from, to, ratio) {
  const [r, g, b] = from.map((value, index) => Math.round(value + (to[index] - value) * ratio))
  return `rgb(${r}, ${g}, ${b})`
}

export function reportSettings(options = {}) {
  return {
    textSize: ['compact', 'normal', 'large'].includes(options.textSize) ? options.textSize : 'normal',
    includeMap: options.includeMap === true,
    includeInfrastructure: options.includeInfrastructure === true,
    includeFilterSummary: options.includeFilterSummary === true,
    colorGroups: options.colorGroups !== false,
    colorTreeSizes: options.colorTreeSizes !== false,
  }
}

export function reportMarkers(markers, settings) {
  return settings.includeInfrastructure
    ? markers
    : markers.filter(marker => marker.type !== 'infrastructure')
}

export function uniqueTags(markers) {
  const tagsByKey = new Map()

  for (const marker of markers) {
    for (const tag of marker.tags || []) {
      const name = tag?.name?.trim()
      if (!name) continue
      tagsByKey.set(tag.id ?? name, { id: tag.id ?? name, name })
    }
  }

  return Array.from(tagsByKey.values())
    .sort((a, b) => collator.compare(a.name, b.name))
}

export function markerTagKeys(marker) {
  return new Set((marker.tags || [])
    .filter(tag => tag?.name)
    .map(tag => tag.id ?? tag.name))
}

function workTitle(work) {
  const recommendation = work.recommendation?.name || '—'
  const date = formatDate(work.execution_date || work.recommendation_date)
  const notes = work.notes ? ` (${work.notes})` : ''

  return `${recommendation}${date ? `, ${date}` : ''}${notes}`
}

export function worksText(marker, completed) {
  const works = (marker.green?.works || [])
    .filter(work => completed ? work.execution_date : !work.execution_date)
    .map(workTitle)

  return works.length ? works.join('; ') : '—'
}

export function plotGroupRows(markers) {
  let previousPlot = null
  let previousSubplot = null
  let plotGroupIndex = -1
  let subplotGroupIndex = -1

  return markers.map(marker => {
    const plot = plotName(marker)
    const subplot = subplotName(marker)

    if (plot !== previousPlot) {
      plotGroupIndex += 1
      previousPlot = plot
    }

    if (subplot !== previousSubplot) {
      subplotGroupIndex += 1
      previousSubplot = subplot
    }

    return {
      marker,
      plotGroupClass: plotGroupIndex % 2 === 0 ? 'plot-group-a' : 'plot-group-b',
      subplotGroupClass: subplotGroupIndex % 2 === 0 ? 'subplot-group-a' : 'subplot-group-b',
    }
  })
}

function parkFromMarker(marker, fallbackPark) {
  const park = marker.park || fallbackPark
  const id = marker.park_id || park?.id || 'unknown'
  const icon = park?.icon?.file_path || (typeof park?.icon === 'string' ? park.icon : null)

  return {
    id,
    name: park?.name || marker.park_name || 'Без парку',
    icon,
  }
}

export function groupMarkersByPark(markers, fallbackPark) {
  const groupsByPark = new Map()

  for (const marker of markers) {
    const park = parkFromMarker(marker, fallbackPark)
    const key = park.id ?? park.name

    if (!groupsByPark.has(key)) {
      groupsByPark.set(key, { park, markers: [] })
    }

    groupsByPark.get(key).markers.push(marker)
  }

  return Array.from(groupsByPark.values())
    .sort((a, b) => collator.compare(a.park.name, b.park.name))
}

export function stateCount(markers, state) {
  return markers.filter(marker => marker.green?.green_state === state).length
}

export function typeCount(markers, type) {
  return markers.filter(marker => marker.type === type).length
}

export function normalizeFilterSummaryItem(item) {
  if (typeof item === 'string') {
    const [labelPath, ...valueParts] = item.split(':')
    const path = labelPath.split('/').map(part => part.trim()).filter(Boolean)
    const label = path.pop() || 'Фільтр'

    return {
      path,
      label,
      value: valueParts.join(':').trim(),
    }
  }

  return {
    path: Array.isArray(item?.path) ? item.path.filter(Boolean) : [],
    label: item?.label || item?.name || 'Фільтр',
    value: item?.value || '',
  }
}
