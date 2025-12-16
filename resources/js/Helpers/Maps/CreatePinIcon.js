import loader from '@/Helpers/Maps/GoogleMapsLoader'

const pinCache = new Map()

function getCacheKey({ glyph, background, borderColor, scale, glyphScale, width, height }) {
  return [glyph, background, borderColor, scale, glyphScale, width, height].join('|')
}

const PIN_SVG = `
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 41 58">
  <path id="p" d="M20.624 1C25.2445 1 30.0994 3.31854 33.8174 6.96387C37.5323 10.6062 39.9999 15.4627 40 20.4014C40 28.9308 37.3023 33.9511 33.7109 38.7441C30.1175 43.5399 25.431 48.3046 21.8887 55.9814C21.6847 56.4235 21.1892 56.7204 20.5713 56.7266C19.954 56.7326 19.4575 56.4467 19.248 56.0107C15.5517 48.3076 10.8309 43.4601 7.24707 38.5752C3.66106 33.6873 1.00006 28.5434 1 19.8721C1 14.9469 3.51718 10.2281 7.28809 6.7168C11.0616 3.20308 15.9838 1.00007 20.624 1Z"/>
</svg>
`.trim()

export async function CreatePinIcon({
  glyph = '/img/icons/markers/examp-icon.svg',
  background = '#4285F4',
  borderColor = '#ffffff',
  scale = 1.5,
  glyphScale = 1.15,
  width = 24,
  height = 24,
} = {}) {
  const cacheKey = getCacheKey({ glyph, background, borderColor, scale, glyphScale, width, height })
  const cached = pinCache.get(cacheKey)
  if (cached) return cached.cloneNode(true)

  await loader.importLibrary('marker')

  const vbW = 41
  const vbH = 58
  const pinH = Math.round(height * (vbH / 24))
  const pinW = Math.round(pinH * (vbW / vbH))
  const pad = Math.max(2, Math.round(Math.min(width, height) * 0.1))
  const top = Math.round(pinH * 0.16)

  const root = document.createElement('div')
  root.style.cssText = `position:relative;display:inline-block;width:${pinW}px;height:${pinH}px;transform:scale(${scale});transform-origin:center;pointer-events:none;`

  const svgHost = document.createElement('div')
  svgHost.innerHTML = PIN_SVG
  const svg = svgHost.firstElementChild
  svg.setAttribute('width', '100%')
  svg.setAttribute('height', '100%')
  svg.style.cssText = 'display:block;pointer-events:none;'

  const p = svg.querySelector('#p')
  p.setAttribute('fill', background)
  p.setAttribute('stroke', borderColor)
  p.setAttribute('stroke-width', '2')

  root.appendChild(svg)

  const wrapper = document.createElement('div')
  wrapper.style.cssText = `width:${width}px;height:${height}px;border-radius:50%;background:#fff;border:1px solid ${borderColor};display:flex;align-items:center;justify-content:center;box-sizing:border-box;padding:${pad}px;overflow:hidden;transform:scale(${glyphScale});transform-origin:center;pointer-events:none;`

  const img = document.createElement('img')
  img.src = glyph
  img.alt = ''
  img.decoding = 'async'
  img.draggable = false
  img.style.cssText = 'width:100%;height:100%;object-fit:contain;pointer-events:none;user-select:none;'
  wrapper.appendChild(img)

  const box = document.createElement('div')
  box.style.cssText = `position:absolute;left:50%;top:${top}px;transform:translateX(-50%);pointer-events:none;`
  box.appendChild(wrapper)
  root.appendChild(box)

  pinCache.set(cacheKey, root)
  return root.cloneNode(true)
}
