import loader from '@/Helpers/Maps/GoogleMapsLoader'

const pinCache = new Map()

function getCacheKey({ glyph, background, borderColor, scale, width, height }) {
  return [glyph, background, borderColor, scale, width, height].join('|')
}

export async function CreatePinIcon({
  glyph = '/img/icons/markers/examp-icon.svg',
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

  const img = document.createElement('img')
  img.src = glyph
  img.width = width
  img.height = height
  img.style.objectFit = 'contain'
  img.style.display = 'block'

  const pin = new PinElement({
    glyph: img,
    background,
    borderColor,
    scale
  })

  pinCache.set(cacheKey, pin)

  return pin.element.cloneNode(true)
}
