const simpleIconCache  = new Map()

export async function CreateSimpleIcon({ iconPath, fill = null, width = 24, height = 24 } = {}) {
  if (!iconPath) throw new Error('CreateSimpleIcon: iconPath is required')

  const cacheKey = iconPath + (fill || '') + width + height
  if (simpleIconCache.has(cacheKey)) {
    return simpleIconCache.get(cacheKey).cloneNode(true)
  }

  const response = await fetch(iconPath)
  if (!response.ok) throw new Error(`Cannot load SVG from ${iconPath}`)

  const svgText = await response.text()
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
