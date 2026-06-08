import pinTemplateRaw from '@/assets/parks/park-pin.svg?raw'

const pinCache = new Map()

function getCacheKey(opts) {
    const {
        glyph, color, height,
        boxTopPct, boxLeftPct, boxWidthPct, boxHeightPct,
        imageScale,
        label, labelColor, labelFontSize, labelFontWeight, labelWidth, labelMarginTop,
        count, countColor
    } = opts
    return [
        glyph, color, height,
        boxTopPct, boxLeftPct, boxWidthPct, boxHeightPct,
        imageScale,
        label, labelColor, labelFontSize, labelFontWeight, labelWidth, labelMarginTop,
        count, countColor
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
    labelWidth = 170,
    labelMarginTop = 4,
    count = null,
    countColor = '#7ab7a5',
} = {}) {
    const aspectW = 113
    const aspectH = 148
    const width = Math.round((aspectW / aspectH) * height)

    const cacheKey = getCacheKey({
        glyph, color, height,
        boxTopPct, boxLeftPct, boxWidthPct, boxHeightPct,
        imageScale,
        label, labelColor, labelFontSize, labelFontWeight, labelWidth, labelMarginTop,
        count, countColor
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

    const hasLabel = label && label.trim().length
    const hasCount = count !== null && count !== undefined

    if (hasLabel || hasCount) {
        const labelWrap = document.createElement('div')
        Object.assign(labelWrap.style, {
            position: 'absolute',
            top: '100%',
            left: '50%',
            transform: 'translateX(-50%)',
            marginTop: `${labelMarginTop}px`,
            display: 'flex',
            flexDirection: 'column',
            alignItems: 'center',
            gap: '4px',
            width: `${labelWidth}px`,
            pointerEvents: 'auto',
            cursor: 'pointer',
            userSelect: 'none',
            zIndex: '1',
        })

        const labelEl = document.createElement('div')
        labelEl.textContent = label
        Object.assign(labelEl.style, {
            color: labelColor,
            fontSize: `${labelFontSize}px`,
            fontWeight: String(labelFontWeight),
            lineHeight: '1.2',
            textAlign: 'center',
            width: '100%',
            whiteSpace: 'normal',
            overflowWrap: 'break-word',
            wordBreak: 'normal',
        })
        labelEl.style.textShadow = [
            '0 1px 2px rgba(0,0,0,.9)',
            '0 0 2px rgba(0,0,0,.9)',
            '1px 0 2px rgba(0,0,0,.9)',
            '-1px 0 2px rgba(0,0,0,.9)',
            '0 -1px 2px rgba(0,0,0,.9)'
        ].join(', ')
        if (hasLabel) labelWrap.appendChild(labelEl)

        if (hasCount) {
            const countEl = document.createElement('div')
            countEl.textContent = `Записів: ${count}`
            Object.assign(countEl.style, {
                background: countColor,
                color: '#fff',
                borderRadius: '6px',
                padding: '2px 8px',
                fontSize: '13px',
                fontWeight: '600',
                lineHeight: '1.25',
                whiteSpace: 'nowrap',
                boxShadow: '0 1px 2px rgba(0,0,0,.25)',
            })
            labelWrap.appendChild(countEl)
        }

        wrapper.appendChild(labelWrap)
    }

    pinCache.set(cacheKey, wrapper)
    return wrapper.cloneNode(true)
}
