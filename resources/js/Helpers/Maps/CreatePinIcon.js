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
  glyphScale = 1.15,
  width = 24,
  height = 24,
} = {}) {
  const cacheKey = getCacheKey({ glyph, background, borderColor, scale, width, height })

  if (pinCache.has(cacheKey)) {
    return pinCache.get(cacheKey).element.cloneNode(true)
  }

  const { PinElement } = await loader.importLibrary('marker')

  const wrapper = document.createElement('div')
  wrapper.style.width = `${width}px`
  wrapper.style.height = `${height}px`
  wrapper.style.borderRadius = '50%'
  wrapper.style.background = '#ffffff' 
  wrapper.style.border = `1px solid ${borderColor}`
  wrapper.style.display = 'flex'
  wrapper.style.alignItems = 'center'
  wrapper.style.justifyContent = 'center'
  wrapper.style.overflow = 'hidden'
  wrapper.style.transform = `scale(${glyphScale})`
  wrapper.style.transformOrigin = 'center'

  const img = document.createElement('img')
  img.src = glyph
  img.style.width = '95%' 
  img.style.height = '95%'
  img.style.objectFit = 'contain'

  wrapper.appendChild(img)

  const pin = new PinElement({
    glyph: wrapper,
    background,
    borderColor,
    scale,
  })

  pinCache.set(cacheKey, pin)

  return pin.element.cloneNode(true)
}
