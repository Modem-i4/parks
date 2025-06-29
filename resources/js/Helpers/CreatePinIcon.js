import loader from '@/Helpers/GoogleMapsLoader'

const pinCache = new Map()

function getCacheKey({ glyph, background, borderColor, scale, width, height }) {
  return [glyph, background, borderColor, scale, width, height].join('|')
}

export async function CreatePinIcon({
  glyph = '/storage/img/icons/examp-icon.svg',
  background = '#4285F4',
  borderColor = '#ffffff',
  scale = 1.5,
  width = 24,
  height = 24,
} = {}) {
  const cacheKey = getCacheKey({ glyph, background, borderColor, scale, width, height })

  if (pinCache.has(cacheKey)) {
    return pinCache.get(cacheKey).element.cloneNode(true)
  }

  const { PinElement } = await loader.importLibrary('marker')

  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg')
  svg.setAttribute('width', width)
  svg.setAttribute('height', height)
  svg.setAttribute('viewBox', `0 0 ${width} ${height}`)

  const use = document.createElementNS('http://www.w3.org/2000/svg', 'use')
  use.setAttribute('href', glyph)
  svg.appendChild(use)

  const pin = new PinElement({
    glyph: svg,
    glyphColor: '#000000',
    background,
    borderColor,
    scale
  })

  pinCache.set(cacheKey, pin)

  return pin.element.cloneNode(true)
}
