import pinTemplateRaw from '@/assets/parks/park-pin.svg?raw'

const pinCache = new Map()

function getCacheKey(opts) {
    const {
        glyph, color, height,
        boxTopPct, boxLeftPct, boxWidthPct, boxHeightPct,
        imageScale,
        label, labelColor, labelFontSize, labelFontWeight, labelMaxWidthPct, labelMarginTop
    } = opts
    return [
        glyph, color, height,
        boxTopPct, boxLeftPct, boxWidthPct, boxHeightPct,
        imageScale,
        label, labelColor, labelFontSize, labelFontWeight, labelMaxWidthPct, labelMarginTop
    ].join('|')
}

export async function CreateCustomPinIcon({
    glyph,
    color = '#007c57',
    height = 100,
    boxTopPct = 9,
    boxLeftPct = 22,
    boxWidthPct = 58,
    boxHeightPct = 58,
    imageScale = 1.25,
    label = '',
    labelColor = '#fff',
    labelFontSize = 16,
    labelFontWeight = 600,
    labelMaxWidthPct = 100,
    labelMarginTop = 4,
} = {}) {
    const aspectW = 113
    const aspectH = 148
    const width = Math.round((aspectW / aspectH) * height)

    const cacheKey = getCacheKey({
        glyph, color, height,
        boxTopPct, boxLeftPct, boxWidthPct, boxHeightPct,
        imageScale,
        label, labelColor, labelFontSize, labelFontWeight, labelMaxWidthPct, labelMarginTop
    })
    if (pinCache.has(cacheKey)) {
        return pinCache.get(cacheKey).cloneNode(true)
    }

    const wrapper = document.createElement('div')
    wrapper.style.display = 'inline-block'
    wrapper.style.position = 'relative'
    wrapper.style.width = `${width}px`
    wrapper.style.height = `${height}px`
    wrapper.style.pointerEvents = 'none'

    const svgHost = document.createElement('div')
    svgHost.innerHTML = pinTemplateRaw
    const svg = svgHost.firstElementChild
    svg.setAttribute('width', '100%')
    svg.setAttribute('height', '100%')
    svg.style.display = 'block'
    svg.style.pointerEvents = 'none'
    svg.style.color = color
    wrapper.appendChild(svg)

    const box = document.createElement('div')
    Object.assign(box.style, {
        position: 'absolute',
        top: `${boxTopPct}%`,
        left: `${boxLeftPct}%`,
        width: `${boxWidthPct}%`,
        height: `${boxHeightPct}%`,
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        pointerEvents: 'none',
    })
    wrapper.appendChild(box)

    const img = document.createElement('img')
    img.src = glyph
    img.alt = ''
    Object.assign(img.style, {
        maxWidth: `${Math.round(100 * imageScale)}%`,
        maxHeight: `${Math.round(100 * imageScale)}%`,
        width: 'auto',
        height: 'auto',
        display: 'block',
        objectFit: 'contain',
        pointerEvents: 'none',
        userSelect: 'none',
    })
    const whiteList = [
        'img/icons/hotkevych_park.png',
        'img/icons/liberators_park.png',
        'img/icons/shevchenko_park.png',
    ]
    const shouldWhiten = typeof glyph === 'string' && whiteList.some(p => glyph.includes(p))
    if (shouldWhiten) {
        img.style.filter = 'brightness(0) invert(1)'
    }

    box.appendChild(img)

    if (label && label.trim().length) {
        const labelEl = document.createElement('div')
        labelEl.textContent = label
        Object.assign(labelEl.style, {
            position: 'absolute',
            top: '100%',
            left: '50%',
            transform: 'translateX(-50%)',
            marginTop: `${labelMarginTop}px`,
            color: labelColor,
            fontSize: `${labelFontSize}px`,
            fontWeight: String(labelFontWeight),
            lineHeight: '1.2',
            whiteSpace: 'nowrap',
            textAlign: 'center',
            pointerEvents: 'none',
            userSelect: 'none',
        })
        labelEl.style.textShadow = [
                '0 1px 2px rgba(0,0,0,.9)',
                '0 0 2px rgba(0,0,0,.9)',
                '1px 0 2px rgba(0,0,0,.9)',
                '-1px 0 2px rgba(0,0,0,.9)',
                '0 -1px 2px rgba(0,0,0,.9)'
            ].join(', ')
        wrapper.appendChild(labelEl)
    }

    pinCache.set(cacheKey, wrapper)
    return wrapper.cloneNode(true)
}
