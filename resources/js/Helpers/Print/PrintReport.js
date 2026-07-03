import { getMarkerTitle } from '@/Helpers/Maps/GetMarkerTitle'

const greenStateLabels = {
  good: 'Добрий',
  normal: 'Задовільний',
  bad: 'Незадовільний',
  planned: 'Лунка',
  removed: 'Видалено',
}

const greenStateSummaryLabels = {
  good: 'Хороший',
  normal: 'Задовільний',
  bad: 'Незадовільний',
  planned: 'Лунка',
  removed: 'Видалено',
}

const greenStateColors = {
  good: '#22c55e',
  normal: '#fcd45b',
  bad: '#ef4444',
  planned: '#66a9ff',
  removed: '#111827',
}

const greenStateTextColors = {
  good: '#fff',
  normal: '#111827',
  bad: '#fff',
  planned: '#fff',
  removed: '#fff',
}

const greenStateSymbols = {
  good: '✓',
  normal: '~',
  bad: '×',
  planned: '○',
  removed: '•',
}

const markerStateShapeClasses = {
  tree: 'state-shape-circle',
  bush: 'state-shape-triangle',
  hedge: 'state-shape-square',
  flower: 'state-shape-diamond',
}

const markerTypeLabels = {
  tree: 'Дерева',
  bush: 'Кущі',
  hedge: 'Живоплоти',
  flower: 'Клумби',
  infrastructure: 'Інфраструктура',
}

const markerTypeOrder = ['tree', 'bush', 'hedge', 'flower', 'infrastructure']

const collator = new Intl.Collator('uk-UA', { sensitivity: 'base' })

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')
}

function markerInventory(marker) {
  return marker.green?.inventory_number || '—'
}

function markerStateLabel(marker) {
  const state = marker.green?.green_state
  return greenStateLabels[state] || state || '—'
}

function markerStateCell(marker) {
  if (marker.type === 'infrastructure') {
    return `
      <td class="state-cell" title="Інфраструктура">
        <span class="state-mark state-shape-circle" style="background:#2563eb;color:#fff">i</span>
      </td>
    `
  }

  const state = marker.green?.green_state
  const color = greenStateColors[state] || '#d1d5db'
  const symbol = greenStateSymbols[state] || ''
  const textColor = greenStateTextColors[state] || '#4b5563'
  const shapeClass = markerStateShapeClasses[marker.type] || 'state-shape-circle'

  return `
    <td class="state-cell" title="${escapeHtml(markerStateLabel(marker))}">
      <span class="state-mark ${shapeClass}" style="background:${escapeHtml(color)};color:${escapeHtml(textColor)}">
        <span class="state-symbol">${escapeHtml(symbol)}</span>
      </span>
    </td>
  `
}

function formatNumber(value) {
  if (value === null || value === undefined || value === '') return '—'
  const number = Number(value)
  if (!Number.isFinite(number)) return String(value)

  return new Intl.NumberFormat('uk-UA', { maximumFractionDigits: 2 }).format(number)
}

function formatDate(value) {
  if (!value) return ''
  return String(value).split('T')[0]
}

function plotName(marker) {
  return marker.green?.subplot?.plot?.name || marker.green?.plot?.name || '—'
}

function subplotName(marker) {
  return marker.green?.subplot?.name || '—'
}

function speciesName(marker) {
  return marker.green?.species?.name_ukr || getMarkerTitle(marker) || '—'
}

function treeValue(marker, field) {
  return marker.green?.tree?.[field] ?? null
}

function numericTreeValue(marker, field) {
  const value = Number(treeValue(marker, field))
  return Number.isFinite(value) && value > 0 ? value : null
}

function measureRanges(markers) {
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

function heatColor(value, range) {
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

function measureCell(marker, field, ranges) {
  const value = numericTreeValue(marker, field)
  const color = heatColor(value, ranges[field])
  const style = color ? ` style="background:${escapeHtml(color)}"` : ''
  const emptyClass = value === null ? ' number-empty' : ''

  return `<td class="number-col${emptyClass}"${style}>${escapeHtml(formatNumber(value))}</td>`
}

function isCoordinatePair(coords) {
  return Array.isArray(coords)
    && coords.length === 2
    && Number.isFinite(Number(coords[0]))
    && Number.isFinite(Number(coords[1]))
}

function coordsFromPair(coords) {
  const lng = Number(coords[0])
  const lat = Number(coords[1])

  if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return null

  return { lat, lng }
}

function coordinatePath(coords) {
  if (!Array.isArray(coords)) return null
  if (coords.length && coords.every(isCoordinatePair)) return coords.map(coordsFromPair).filter(Boolean)

  for (const item of coords) {
    const path = coordinatePath(item)
    if (path?.length) return path
  }

  return null
}

function coordsFromGeoJson(marker) {
  const center = marker?.geo_json?.properties?.center
  if (isCoordinatePair(center)) return coordsFromPair(center)

  const geometryCoords = marker?.geo_json?.geometry?.coordinates
  if (isCoordinatePair(geometryCoords)) return coordsFromPair(geometryCoords)

  const path = coordinatePath(geometryCoords)
  return path?.length ? mapCenter(path) : null
}

function markerCoords(marker) {
  return coordsFromGeoJson(marker)
    || (isCoordinatePair(marker?.coordinates) ? coordsFromPair(marker.coordinates) : null)
    || (() => {
      const path = coordinatePath(marker?.coordinates)
      return path?.length ? mapCenter(path) : null
    })()
}

function mapCenter(coords) {
  if (!coords.length) return null

  const sum = coords.reduce((acc, item) => ({
    lat: acc.lat + item.lat,
    lng: acc.lng + item.lng,
  }), { lat: 0, lng: 0 })

  return {
    lat: sum.lat / coords.length,
    lng: sum.lng / coords.length,
  }
}

function coordinatePoints(coords) {
  if (!Array.isArray(coords)) return []
  if (isCoordinatePair(coords)) {
    const point = coordsFromPair(coords)
    return point ? [point] : []
  }

  return coords.flatMap(coordinatePoints)
}

function mapBounds(coords) {
  if (!coords.length) return null

  return coords.reduce((bounds, item) => ({
    minLat: Math.min(bounds.minLat, item.lat),
    maxLat: Math.max(bounds.maxLat, item.lat),
    minLng: Math.min(bounds.minLng, item.lng),
    maxLng: Math.max(bounds.maxLng, item.lng),
  }), {
    minLat: coords[0].lat,
    maxLat: coords[0].lat,
    minLng: coords[0].lng,
    maxLng: coords[0].lng,
  })
}

function boundsCenter(bounds) {
  return {
    lat: (bounds.minLat + bounds.maxLat) / 2,
    lng: (bounds.minLng + bounds.maxLng) / 2,
  }
}

function parkMapViewport(park) {
  const bounds = mapBounds(coordinatePoints(park?.geo_json?.geometry?.coordinates))
  if (!bounds) return null

  return {
    center: boundsCenter(bounds),
    visible: [
      { lat: bounds.minLat, lng: bounds.minLng },
      { lat: bounds.maxLat, lng: bounds.maxLng },
    ],
  }
}

function clusterCenter(coords) {
  const center = mapCenter(coords)

  return {
    lat: center.lat,
    lng: center.lng,
    count: coords.length,
  }
}

function spatialClusters(coords, clusterSize) {
  const rowCount = Math.max(1, Math.round(Math.sqrt(coords.length / clusterSize)))
  const rowSize = Math.ceil(coords.length / rowCount)
  const sortedByLat = [...coords].sort((a, b) => a.lat - b.lat || a.lng - b.lng)
  const clusters = []

  for (let rowIndex = 0; rowIndex < rowCount; rowIndex += 1) {
    const row = sortedByLat
      .slice(rowIndex * rowSize, (rowIndex + 1) * rowSize)
      .sort((a, b) => a.lng - b.lng || a.lat - b.lat)

    for (let index = 0; index < row.length; index += clusterSize) {
      clusters.push(clusterCenter(row.slice(index, index + clusterSize)))
    }
  }

  return clusters
}

function mapMarkerCoords(coords, maxMarkers) {
  if (coords.length <= maxMarkers) {
    return {
      coords,
      clustered: false,
      largestCluster: 1,
    }
  }

  let clusterSize = Math.ceil(coords.length / maxMarkers)
  let clusters = []

  for (let attempt = 0; attempt < 8; attempt += 1) {
    clusters = spatialClusters(coords, clusterSize)
    if (clusters.length <= maxMarkers) break
    clusterSize += 1
  }

  return {
    coords: clusters.slice(0, maxMarkers),
    clustered: true,
    largestCluster: Math.max(...clusters.map(cluster => cluster.count)),
  }
}

function staticMap(markers, options = {}) {
  const key = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
  if (!key) return null

  const coords = markers.map(markerCoords).filter(Boolean)
  if (!coords.length) return null
  const maxMarkers = options.maxMarkers ?? 350
  const preparedMarkers = mapMarkerCoords(coords, maxMarkers)

  const params = new URLSearchParams({
    size: options.size ?? '640x400',
    scale: '2',
    maptype: options.maptype ?? 'roadmap',
    key,
  })
  const viewport = options.park ? parkMapViewport(options.park) : null
  const center = viewport?.center || mapCenter(coords)
  if (!center) return null

  params.set('center', `${center.lat.toFixed(6)},${center.lng.toFixed(6)}`)

  if (viewport?.visible) {
    params.append('visible', viewport.visible
      .map(item => `${item.lat.toFixed(6)},${item.lng.toFixed(6)}`)
      .join('|'))
  } else {
    params.set('zoom', String(options.zoom ?? 20))
  }

  params.append('markers', [
    'size:tiny',
    'color:0x007c57',
    ...preparedMarkers.coords
    .map(item => `${item.lat.toFixed(6)},${item.lng.toFixed(6)}`)
  ].join('|'))

  return {
    url: `https://maps.googleapis.com/maps/api/staticmap?${params.toString()}`,
    markerCount: preparedMarkers.coords.length,
    originalCount: coords.length,
    clustered: preparedMarkers.clustered,
    largestCluster: preparedMarkers.largestCluster,
  }
}

function uniqueTags(markers) {
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

function markerTagKeys(marker) {
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

function worksText(marker, completed) {
  const works = (marker.green?.works || [])
    .filter(work => completed ? work.execution_date : !work.execution_date)
    .map(workTitle)

  return works.length ? works.join('; ') : '—'
}

function plotGroupRows(markers) {
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

function groupMarkersByPark(markers, fallbackPark) {
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

function stateCount(markers, state) {
  return markers.filter(marker => marker.green?.green_state === state).length
}

function typeCount(markers, type) {
  return markers.filter(marker => marker.type === type).length
}

function statePill(state, count) {
  return `
    <span class="summary-pill state-summary state-${escapeHtml(state)}">
      <span class="summary-symbol">${escapeHtml(greenStateSymbols[state])}</span>
      <span>${escapeHtml(greenStateSummaryLabels[state] || greenStateLabels[state])}: ${count}</span>
    </span>
  `
}

function optionalStatePill(markers, state) {
  const count = stateCount(markers, state)
  return count > 0 ? statePill(state, count) : ''
}

function typePill(type, count) {
  return `
    <span class="summary-pill type-summary type-${escapeHtml(type)}">
      <span>${escapeHtml(markerTypeLabels[type] || type)}: ${count}</span>
    </span>
  `
}

function typePills(markers) {
  return markerTypeOrder
    .map(type => {
      const count = typeCount(markers, type)
      return count > 0 ? typePill(type, count) : ''
    })
    .join('')
}

function parkHeader(group) {
  const { park, markers } = group

  return `
    <div class="park-heading">
      <div class="park-title-group">
        ${park.icon ? `<img class="park-icon" src="${escapeHtml(park.icon)}" alt="">` : '<span class="park-icon park-icon-placeholder"></span>'}
        <h2>${escapeHtml(park.name)}</h2>
      </div>
      <div class="park-heading-separator"></div>
      <div class="park-summary-groups">
        <div class="summary-pills state-pills">
          <span class="summary-pill total-pill">Всього: ${markers.length}</span>
          ${statePill('good', stateCount(markers, 'good'))}
          ${statePill('normal', stateCount(markers, 'normal'))}
          ${statePill('bad', stateCount(markers, 'bad'))}
          ${optionalStatePill(markers, 'planned')}
          ${optionalStatePill(markers, 'removed')}
        </div>
        <div class="summary-pills type-pills">
          ${typePills(markers)}
        </div>
      </div>
    </div>
  `
}

function markersTable(markers, tags, ranges) {
  return `
    <section class="table-section">
      <table class="report-table">
        <thead>
          <tr>
            <th class="num-col">№ п/п</th>
            <th class="inv-col">№ інв.</th>
            <th class="side-col">Виділ</th>
            <th class="side-col">Ділянка</th>
            <th class="state-col">Стан</th>
            <th class="species-col">Вид, укр</th>
            <th class="measure-col">Діаметр на 1, 3 м, в см</th>
            <th class="measure-col">Висота, м</th>
            ${tags.map(tag => `
              <th class="tag-head" title="${escapeHtml(tag.name)}">
                <span>${escapeHtml(tag.name)}</span>
              </th>
            `).join('')}
            <th class="work-col">Роботи</th>
            <th class="work-col">Рекомендації</th>
          </tr>
        </thead>
        <tbody>
          ${plotGroupRows(markers).map(({ marker, plotGroupClass, subplotGroupClass }, index) => {
            const markerTags = markerTagKeys(marker)

            return `
              <tr>
                <td class="num-col">${index + 1}</td>
                <td class="inv-cell">${escapeHtml(markerInventory(marker))}</td>
                <td class="side-cell plot-group-cell ${plotGroupClass}"><span>${escapeHtml(plotName(marker))}</span></td>
                <td class="side-cell subplot-group-cell ${subplotGroupClass}"><span>${escapeHtml(subplotName(marker))}</span></td>
                ${markerStateCell(marker)}
                <td>${escapeHtml(speciesName(marker))}</td>
                ${measureCell(marker, 'trunk_diameter_cm', ranges)}
                ${measureCell(marker, 'height_m', ranges)}
                ${tags.map(tag => {
                  const hasTag = markerTags.has(tag.id)
                  return `<td class="tag-cell ${hasTag ? 'tag-yes' : 'tag-no'}">${hasTag ? '✓' : '×'}</td>`
                }).join('')}
                <td class="work-text">${escapeHtml(worksText(marker, true))}</td>
                <td class="work-text">${escapeHtml(worksText(marker, false))}</td>
              </tr>
            `
          }).join('')}
        </tbody>
      </table>
    </section>
  `
}

function parkSections(markers, options = {}) {
  const tags = uniqueTags(markers)
  const ranges = measureRanges(markers)
  const groups = groupMarkersByPark(markers, options.fallbackPark)

  return groups.map(group => `
    <section class="park-section">
      ${parkHeader(group)}
      ${markersTable(group.markers, tags, ranges)}
    </section>
  `).join('')
}

function reportMapSection(markers, options = {}) {
  const parksById = new Map((options.parks || []).map(park => [String(park.id), park]))
  const groups = groupMarkersByPark(markers, options.fallbackPark)
    .map(group => {
      const park = parksById.get(String(group.park.id)) || group.park
      const map = staticMap(group.markers, {
        maptype: 'roadmap',
        maxMarkers: 350,
        park,
        size: '640x400',
        zoom: 16,
      })

      return {
        ...group,
        map,
      }
    })
    .filter(group => group.map?.url)

  if (!groups.length) return ''

  return `
    <section class="report-map-section ${groups.length === 1 ? 'single-map-section' : 'multi-map-section'}">
      <h2>Розміщення на мапі</h2>
      <div class="report-map-grid">
        ${groups.map(group => `
          <figure>
            ${groups.length > 1 ? `<figcaption>${escapeHtml(group.park.name)}</figcaption>` : ''}
            <img class="report-map-shot" src="${escapeHtml(group.map.url)}" alt="">
            ${group.map.clustered ? `
              <div class="map-note">
                На мапі: ${group.map.markerCount} кластеризованих позначок для ${group.map.originalCount} маркерів. Найбільший кластер: ${group.map.largestCluster}.
              </div>
            ` : ''}
          </figure>
        `).join('')}
      </div>
    </section>
  `
}

function buildHtml(markers, options = {}) {
  const title = options.title || 'Звіт по маркерах'

  return `
    <!doctype html>
    <html lang="uk">
      <head>
        <meta charset="utf-8">
        <base href="${escapeHtml(window.location.origin)}/">
        <title>${escapeHtml(title)}</title>
        <style>
          @page {
            size: A4 landscape;
            margin: 8mm;
            @bottom-right {
              content: "Стор. " counter(page);
              color: #6b7280;
              font-size: 8px;
            }
          }
          * { box-sizing: border-box; }
          body {
            margin: 0;
            background: #fff;
            color: #1f2937;
            font-family: Arial, sans-serif;
            font-size: 10px;
            line-height: 1.15;
          }
          .page { width: 100%; margin: 0 auto; }
          header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            border-bottom: 2px solid #007c57;
            padding-bottom: 5px;
            margin-bottom: 6px;
          }
          h1 { margin: 0 0 2px; color: #007c57; font-size: 16px; line-height: 1.05; }
          .subtitle { margin: 0; color: #4b5563; }
          .logos {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 4px;
          }
          .logo { max-width: 112px; max-height: 38px; object-fit: contain; }
          .logo-interreg { max-width: 164px; }
          .meta { text-align: right; color: #4b5563; font-size: 9px; }
          .park-section {
            margin: 0 0 12px;
            break-inside: auto;
            page-break-inside: auto;
          }
          .park-heading {
            display: grid;
            grid-template-columns: max-content 1px minmax(0, 1fr);
            align-items: center;
            gap: 8px;
            margin: 0 0 4px;
            break-after: avoid;
            page-break-after: avoid;
          }
          .park-title-group {
            display: flex;
            align-items: center;
            gap: 7px;
            min-width: 0;
          }
          .park-heading h2 {
            margin: 0;
            color: #111827;
            font-size: 16px;
            line-height: 1.1;
            white-space: nowrap;
          }
          .park-heading-separator {
            align-self: stretch;
            min-height: 26px;
            background: #d1d5db;
          }
          .park-summary-groups {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
          }
          .park-icon {
            width: 20px;
            height: 20px;
            object-fit: contain;
            flex: 0 0 auto;
          }
          .park-icon-placeholder {
            border-radius: 999px;
            background: #e5e7eb;
          }
          .summary-pills {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 4px;
            margin-left: 0;
          }
          .summary-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            min-height: 16px;
            border-radius: 999px;
            padding: 2px 6px;
            border: 1px solid transparent;
            font-size: 9px;
            font-weight: 700;
            line-height: 1;
            white-space: nowrap;
          }
          .total-pill {
            background: #eef2ff;
            border-color: #c7d2fe;
            color: #3730a3;
          }
          .summary-symbol {
            font-size: 10px;
            line-height: 1;
          }
          .state-planned {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1d4ed8;
          }
          .state-good {
            background: #dcfce7;
            border-color: #86efac;
            color: #047857;
          }
          .state-normal {
            background: #fef9c3;
            border-color: #fde68a;
            color: #a16207;
          }
          .state-bad {
            background: #fee2e2;
            border-color: #fecaca;
            color: #b91c1c;
          }
          .state-removed {
            background: #f3f4f6;
            border-color: #9ca3af;
            color: #111827;
          }
          .type-summary {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #334155;
          }
          .type-tree {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
          }
          .type-bush {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #15803d;
          }
          .type-hedge {
            background: #f7fee7;
            border-color: #d9f99d;
            color: #4d7c0f;
          }
          .type-flower {
            background: #fdf2f8;
            border-color: #fbcfe8;
            color: #be185d;
          }
          .type-infrastructure {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
          }
          .table-section {
            width: 100%;
            overflow: visible;
            break-inside: auto;
            page-break-inside: auto;
          }
          .report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            break-inside: auto;
            page-break-inside: auto;
          }
          th,
          td {
            border: 1px solid #d1d5db;
            padding: 2px 3px;
            text-align: left;
            vertical-align: middle;
            word-break: break-word;
          }
          th {
            background: #f3f4f6;
            color: #374151;
            font-weight: 700;
            text-align: center;
          }
          thead { display: table-header-group; }
          tbody tr { break-inside: avoid; }
          .plot-group-a { background: #f4f9ff; }
          .plot-group-b { background: #fff8e8; }
          .subplot-group-a { background: #f5fff8; }
          .subplot-group-b { background: #fff5fb; }
          .num-col { width: 23px; text-align: center; }
          .inv-col { width: 38px; }
          .inv-cell {
            width: 38px;
            font-size: 8.5px;
            line-height: 1;
            white-space: nowrap;
            word-break: normal;
            text-align: center;
          }
          .side-col {
            width: 18px;
            height: 58px;
            padding: 1px 0;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
          }
          .side-cell {
            padding: 1px 2px;
            text-align: center;
            font-size: 9px;
            line-height: 1.05;
            word-break: break-word;
          }
          .side-cell span {
            display: block;
          }
          .species-col { width: 92px; }
          .measure-col { width: 42px; }
          .state-col { width: 18px; }
          th.state-col {
            height: 58px;
            padding: 1px 0;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
          }
          .work-col { width: 108px; }
          .number-col {
            text-align: center;
            white-space: nowrap;
            font-weight: 700;
          }
          .number-empty {
            font-weight: 400;
            background: #fff;
          }
          .state-cell {
            padding: 1px;
            text-align: center;
          }
          .state-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 16px;
            height: 16px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
          }
          .state-shape-circle { border-radius: 999px; }
          .state-shape-triangle {
            clip-path: polygon(50% 0, 100% 100%, 0 100%);
            align-items: flex-end;
            padding-bottom: 2px;
          }
          .state-shape-triangle .state-symbol {
            transform: translateY(1px);
          }
          .state-shape-square { border-radius: 2px; }
          .state-shape-diamond {
            width: 15px;
            height: 15px;
            transform: rotate(45deg);
            border-radius: 1px;
          }
          .state-shape-diamond .state-symbol {
            display: inline-block;
            transform: rotate(-45deg);
          }
          .tag-head {
            width: 18px;
            height: 94px;
            padding: 1px 0;
            vertical-align: bottom;
          }
          .tag-head span {
            display: inline-block;
            max-height: 88px;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 8px;
            line-height: 1;
          }
          .tag-cell {
            width: 18px;
            padding: 1px 0;
            text-align: center;
            font-size: 10px;
            font-weight: 700;
            line-height: 1;
          }
          .tag-yes {
            background: #dcfce7;
            color: #047857;
          }
          .tag-no {
            background: #fee2e2;
            color: #b91c1c;
          }
          .work-text {
            font-size: 8px;
            line-height: 1.15;
          }
          .report-map-section {
            break-before: page;
            page-break-before: always;
            margin: 0;
          }
          .report-map-section h2 {
            margin: 0 0 8px;
            color: #111827;
            font-size: 16px;
            line-height: 1.1;
          }
          .report-map-grid {
            display: grid;
            gap: 10px;
            align-items: start;
            width: 100%;
          }
          .single-map-section .report-map-grid {
            grid-template-columns: 1fr;
          }
          .multi-map-section .report-map-grid {
            grid-template-columns: 1fr;
            gap: 0;
          }
          .report-map-grid figure {
            margin: 0;
            break-inside: avoid;
            page-break-inside: avoid;
          }
          .multi-map-section figure {
            break-before: page;
            page-break-before: always;
          }
          .multi-map-section figure:first-child {
            break-before: auto;
            page-break-before: auto;
          }
          .report-map-grid figcaption {
            margin: 0 0 4px;
            color: #374151;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
          }
          .report-map-shot {
            display: block;
            width: 100%;
            height: 176mm;
            object-fit: fill;
            border: 1px solid #d1d5db;
          }
          .map-note {
            margin-top: 4px;
            color: #4b5563;
            font-size: 9px;
            line-height: 1.2;
          }
          @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
          }
        </style>
      </head>
      <body>
        <main class="page">
          <header>
            <div>
              <h1>${escapeHtml(title)}</h1>
              <p class="subtitle">Маркерів у звіті: ${markers.length}</p>
            </div>
            <div>
              <div class="logos">
                <img class="logo logo-interreg" src="/img/icons/logo-interreg.png" alt="Interreg NEXT Poland-Ukraine">
                <img class="logo" src="/img/icons/logo-parks-matter.png" alt="ParksMatter">
              </div>
              <div class="meta">Сформовано: ${escapeHtml(new Date().toLocaleDateString('uk-UA'))}</div>
            </div>
          </header>
          ${parkSections(markers, options)}
          ${reportMapSection(markers, options)}
        </main>
        <script>
          Promise.all(Array.from(document.images).map((image) => {
            if (image.complete) return Promise.resolve()
            return new Promise((resolve) => {
              image.onload = resolve
              image.onerror = resolve
            })
          })).then(() => {
            setTimeout(() => {
              window.focus()
              window.print()
            }, 250)
          })
        </script>
      </body>
    </html>
  `
}

function openReportWindow(printUrl) {
  const printWindow = window.open('', '_blank')
  if (!printWindow) return false

  printWindow.document.title = 'Друк звіту'
  printWindow.document.body.style.fontFamily = 'Arial, sans-serif'
  printWindow.document.body.style.padding = '24px'
  printWindow.document.body.textContent = 'Готуємо звіт для друку...'

  printWindow.location.replace(printUrl)
  return true
}

function preparedReport(printUrl) {
  let revoked = false
  let revokeTimer = setTimeout(revoke, 10 * 60_000)

  function revoke() {
    if (revoked) return
    clearTimeout(revokeTimer)
    URL.revokeObjectURL(printUrl)
    revoked = true
  }

  function scheduleRevokeAfterOpen() {
    clearTimeout(revokeTimer)
    revokeTimer = setTimeout(revoke, 60_000)
  }

  return {
    open() {
      if (revoked) return false

      const opened = openReportWindow(printUrl)
      if (opened) scheduleRevokeAfterOpen()

      return opened
    },
    revoke,
  }
}

export async function printReport(markers, options = {}) {
  if (!markers?.length) return null

  const printBlob = new Blob([buildHtml(markers, options)], { type: 'text/html' })
  const printUrl = URL.createObjectURL(printBlob)
  const report = preparedReport(printUrl)

  return {
    ...report,
    opened: report.open(),
  }
}
