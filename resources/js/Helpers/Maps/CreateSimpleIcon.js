const icons = import.meta.glob('/resources/js/assets/markers/*-map_icon.svg', { eager: true, query: 'raw', import: 'default' })

const simpleIconCache = new Map()

export function CreateSimpleIcon({ type = 'tree', fill = null, width = 24, height = 24 } = {}) {
  const key = `/resources/js/assets/markers/${type}-map_icon.svg`
  const svgText = icons[key]
  if (!svgText) throw new Error(`Icon not found for type: ${type}`)

  const cacheKey = key + (fill || '') + width + height
  if (simpleIconCache.has(cacheKey)) {
    return simpleIconCache.get(cacheKey).cloneNode(true)
  }

  const parser = new DOMParser()
  const doc = parser.parseFromString(svgText, 'image/svg+xml')
  const svg = doc.documentElement

  if (fill) {
    svg.querySelectorAll('[fill]').forEach(el => {
      el.setAttribute('fill', fill)
    })
  }

  svg.style.display = 'block'
  svg.setAttribute('width', width)
  svg.setAttribute('height', height)

  simpleIconCache.set(cacheKey, svg)

  return svg.cloneNode(true)
}



export function getColorByGreenState(state) {
  switch (state) {
    case 'good': return 'green'
    case 'normal': return '#fcd45b'
    case 'bad': return 'red'
    case 'planned': return '#66a9ff'
    case 'removed': return '#9d9fa3'
    default: return 'gray'
  }
}
