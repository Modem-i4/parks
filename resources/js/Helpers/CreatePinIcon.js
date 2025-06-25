import loader from '@/Helpers/GoogleMapsLoader'

export async function CreatePinIcon({
  glyph = '/storage/img/icons/examp-icon.svg',
  background = '#4285F4',
  borderColor = '#ffffff',
  scale = 1.5,
  width = 24,
  height = 24,
} = {}) {
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

  return pin
}