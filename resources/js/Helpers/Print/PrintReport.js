import QRCode from 'qrcode'
import {
  greenStateLabels,
  greenStateSummaryLabels,
  greenStateColors,
  greenStateTextColors,
  markerTypeLabels,
  markerTypeOrder,
  formatDate,
  formatPlantingDate,
  plotName,
  subplotName,
  speciesName,
  numericTreeValue,
  measureRanges,
  heatColor,
  reportSettings,
  reportMarkers,
  uniqueTags,
  markerTagKeys,
  worksText,
  plotGroupRows,
  groupMarkersByPark,
  stateCount,
  typeCount,
  normalizeFilterSummaryItem,
} from '../Report/ReportData.js'
import { reportMapGroups } from '../Report/ReportMaps.js'

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

const reportTextSizeClasses = {
  compact: 'text-compact',
  normal: 'text-normal',
  large: 'text-large',
}

const parkPageBreakMarkerCount = 150

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')
}

function markerInventory(marker) {
  const green = marker.green
  const number = String(green?.inventory_number ?? '').trim()
  const oldNumber = String(green?.inventory_number_old ?? '').trim()
  const tag = String(green?.tree?.inventory_tag ?? '').trim()

  let html = escapeHtml(number)
  if (oldNumber) html += `${html ? '/' : ''}<i>${escapeHtml(oldNumber)}</i>`
  if (tag) html += `${html ? ' ' : ''}(${escapeHtml(tag)})`

  return html || '—'
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

function measureCell(marker, field, ranges, settings) {
  const value = numericTreeValue(marker, field)
  const color = settings.colorTreeSizes ? heatColor(value, ranges[field]) : ''
  const style = color ? ` style="background:${escapeHtml(color)}"` : ''
  const emptyClass = value === null ? ' number-empty' : ''

  return `<td class="number-col${emptyClass}"${style}>${escapeHtml(formatNumber(value))}</td>`
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

function markersTable(markers, tags, ranges, settings, hasRemovedMarkers) {
  return `
    <section class="table-section">
      <table class="report-table">
        <thead>
          <tr>
            <th class="num-col">№ п/п</th>
            <th class="inv-col">№ інв.<br>/<i>№ старий</i> (бірка)</th>
            <th class="side-col">Виділ</th>
            <th class="side-col">Ділянка</th>
            <th class="state-col">Стан</th>
            <th class="state-date-col">Дата висадки</th>
            ${hasRemovedMarkers ? '<th class="state-date-col">Дата видалення</th>' : ''}
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
                <td class="inv-cell">${markerInventory(marker)}</td>
                <td class="side-cell plot-group-cell ${settings.colorGroups ? plotGroupClass : ''}"><span>${escapeHtml(plotName(marker))}</span></td>
                <td class="side-cell subplot-group-cell ${settings.colorGroups ? subplotGroupClass : ''}"><span>${escapeHtml(subplotName(marker))}</span></td>
                ${markerStateCell(marker)}
                <td class="state-date-cell">${escapeHtml(formatPlantingDate(marker.green?.planting_date) || '—')}</td>
                ${hasRemovedMarkers ? `<td class="state-date-cell">${escapeHtml(
                  marker.green?.green_state === 'removed'
                    ? formatDate(marker.green?.green_state_changed_at) || '—'
                    : '—'
                )}</td>` : ''}
                <td>${escapeHtml(speciesName(marker))}</td>
                ${measureCell(marker, 'trunk_diameter_cm', ranges, settings)}
                ${measureCell(marker, 'height_m', ranges, settings)}
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
  const settings = reportSettings(options)
  const tags = uniqueTags(markers)
  const hasRemovedMarkers = markers.some(marker => marker.green?.green_state === 'removed')
  const ranges = measureRanges(markers)
  const groups = groupMarkersByPark(markers, options.fallbackPark)
  const separateParks = markers.length > parkPageBreakMarkerCount

  return groups.map((group, index) => `
    <section class="park-section ${separateParks && index > 0 ? 'park-section-new-page' : ''}">
      ${parkHeader(group)}
      ${markersTable(group.markers, tags, ranges, settings, hasRemovedMarkers)}
    </section>
  `).join('')
}

function reportMapSection(markers, options = {}) {
  const groups = reportMapGroups(markers, options)

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

function addFilterSummaryItem(tree, item) {
  const normalized = normalizeFilterSummaryItem(item)
  let node = tree

  for (const part of normalized.path) {
    let child = node.children.find(item => item.label === part)
    if (!child) {
      child = { label: part, children: [], items: [] }
      node.children.push(child)
    }

    node = child
  }

  node.items.push(normalized)
}

function filterSummaryTree(summary) {
  const tree = { label: null, children: [], items: [] }
  summary.forEach(item => addFilterSummaryItem(tree, item))

  return tree
}

function renderFilterSummaryNode(node, level = 0) {
  const children = node.children.map(child => `
    <span class="filter-summary-group filter-summary-level-${Math.min(level, 2)}">
      <span class="filter-summary-group-title">${escapeHtml(child.label)}</span>
      <span class="filter-summary-children">
        ${renderFilterSummaryNode(child, level + 1)}
      </span>
    </span>
  `).join('')

  const items = node.items.map(item => `
    <span class="filter-summary-leaf">
      <span class="filter-summary-leaf-label">${escapeHtml(item.value ? `${item.label}:` : item.label)}</span>
      ${item.value ? `<span class="filter-summary-leaf-value">${escapeHtml(item.value)}</span>` : ''}
    </span>
  `).join('')

  return `${children}${items}`
}

function filterSummarySection(options = {}) {
  const summary = Array.isArray(options.filterSummary) ? options.filterSummary.filter(Boolean) : []
  if (!summary.length) return ''
  const tree = filterSummaryTree(summary)

  return `
    <section class="filter-summary">
      <div class="filter-summary-tree">
        ${renderFilterSummaryNode(tree)}
      </div>
    </section>
  `
}

function buildHtml(markers, options = {}) {
  const settings = reportSettings(options)
  const preparedMarkers = reportMarkers(markers, settings)
  const textSizeClass = reportTextSizeClasses[settings.textSize] || reportTextSizeClasses.normal
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
            --report-font-size: 10px;
            --report-line-height: 1.15;
            --cell-padding-y: 2px;
            --cell-padding-x: 3px;
            --side-head-height: 58px;
            --tag-head-height: 94px;
            --tag-head-max-height: 88px;
            --side-font-size: 9px;
            --tag-head-font-size: 8px;
            --tag-cell-font-size: 10px;
            --work-font-size: 8px;
            --state-mark-size: 16px;
            --state-mark-font-size: 11px;
            --inv-font-size: 8.5px;
            --summary-font-size: 9px;
            --summary-min-height: 16px;
            margin: 0;
            background: #fff;
            color: #1f2937;
            font-family: Arial, sans-serif;
            font-size: var(--report-font-size);
            line-height: var(--report-line-height);
          }
          body.text-compact {
            --report-font-size: 7.6px;
            --report-line-height: 1.02;
            --cell-padding-y: 0.6px;
            --cell-padding-x: 1.5px;
            --side-head-height: 40px;
            --tag-head-height: 66px;
            --tag-head-max-height: 62px;
            --side-font-size: 7px;
            --tag-head-font-size: 6.4px;
            --tag-cell-font-size: 8px;
            --work-font-size: 6.4px;
            --state-mark-size: 11px;
            --state-mark-font-size: 8px;
            --inv-font-size: 6.8px;
            --summary-font-size: 7px;
            --summary-min-height: 12px;
          }
          body.text-large {
            --report-font-size: 12.5px;
            --report-line-height: 1.28;
            --cell-padding-y: 4px;
            --cell-padding-x: 5px;
            --side-head-height: 76px;
            --tag-head-height: 124px;
            --tag-head-max-height: 118px;
            --side-font-size: 11px;
            --tag-head-font-size: 10px;
            --tag-cell-font-size: 12px;
            --work-font-size: 10px;
            --state-mark-size: 20px;
            --state-mark-font-size: 13px;
            --inv-font-size: 10px;
            --summary-font-size: 10px;
            --summary-min-height: 19px;
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
          .report-qr { text-align: center; color: #007c57; font-size: 9px; text-decoration: none; }
          .report-qr img { display: block; width: 36mm; height: 36mm; }
          .meta { text-align: right; color: #4b5563; font-size: 9px; }
          .filter-summary {
            margin: 0 0 8px;
            padding: 0 0 6px;
            border-bottom: 2px solid #007c57;
            break-inside: avoid;
            page-break-inside: avoid;
          }
          .filter-summary-tree,
          .filter-summary-children {
            display: flex;
            flex-wrap: wrap;
            gap: 3px;
          }
          .filter-summary-group {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 3px;
            min-height: 20px;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            background: #f9fafb;
            color: #1f2937;
            padding: 3px 4px 3px 6px;
            font-size: 8px;
            line-height: 1.1;
          }
          .filter-summary-level-1 {
            border-color: #cbd5e1;
            background: #f8fafc;
          }
          .filter-summary-level-2 {
            border-color: #e5e7eb;
            background: #fff;
          }
          .filter-summary-group-title {
            color: #374151;
            font-weight: 700;
            white-space: nowrap;
          }
          .filter-summary-level-1 > .filter-summary-group-title {
            color: #475569;
          }
          .filter-summary-level-2 > .filter-summary-group-title {
            color: #4b5563;
          }
          .filter-summary-leaf {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            min-height: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: #fff;
            color: #1f2937;
            padding: 2px 6px;
            font-size: 8px;
            line-height: 1.1;
          }
          .filter-summary-leaf-label {
            color: #374151;
            font-weight: 700;
            white-space: nowrap;
          }
          .filter-summary-leaf-value {
            color: #111827;
          }
          .park-section {
            margin: 0 0 12px;
            break-inside: auto;
            page-break-inside: auto;
          }
          .park-section-new-page {
            break-before: page;
            page-break-before: always;
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
            min-height: var(--summary-min-height);
            border-radius: 999px;
            padding: 2px 6px;
            border: 1px solid transparent;
            font-size: var(--summary-font-size);
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
            padding: var(--cell-padding-y) var(--cell-padding-x);
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
          .inv-col { width: 90px; }
          .inv-cell {
            font-size: var(--inv-font-size);
            line-height: 1.15;
            white-space: normal;
            word-break: normal;
            overflow-wrap: anywhere;
            text-align: center;
          }
          .side-col {
            width: 18px;
            height: var(--side-head-height);
            padding: 1px 0;
            writing-mode: vertical-rl;
            transform: rotate(180deg);
          }
          .side-cell {
            padding: 1px 2px;
            text-align: center;
            font-size: var(--side-font-size);
            line-height: 1.05;
            word-break: break-word;
          }
          .side-cell span {
            display: block;
          }
          .species-col { width: 92px; }
          .measure-col { width: 42px; }
          .state-col { width: 18px; }
          .state-date-col { width: 32px; }
          th.state-col,
          th.state-date-col {
            height: var(--side-head-height);
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
          .state-date-cell {
            padding: 1px 2px;
            text-align: center;
            white-space: nowrap;
          }
          .state-mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: var(--state-mark-size);
            height: var(--state-mark-size);
            font-size: var(--state-mark-font-size);
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
            height: var(--tag-head-height);
            padding: 1px 0;
            vertical-align: bottom;
          }
          .tag-head span {
            display: inline-block;
            max-height: var(--tag-head-max-height);
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: var(--tag-head-font-size);
            line-height: 1;
          }
          .tag-cell {
            width: 18px;
            padding: 1px 0;
            text-align: center;
            font-size: var(--tag-cell-font-size);
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
            font-size: var(--work-font-size);
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
      <body class="${escapeHtml(textSizeClass)}">
        <main class="page">
          <header>
            <div>
              <h1>${escapeHtml(title)}</h1>
              <p class="subtitle">Маркерів у звіті: ${preparedMarkers.length}</p>
            </div>
            ${options.qrDataUrl ? `<a class="report-qr" href="${escapeHtml(options.mapUrl)}"><img src="${escapeHtml(options.qrDataUrl)}" alt="QR-код мапи з фільтрами звіту"></a>` : ''}
            <div>
              <div class="logos">
                <img class="logo logo-interreg" src="/img/icons/logo-interreg.png" alt="Interreg NEXT Poland-Ukraine">
                <img class="logo" src="/img/icons/logo-parks-matter.png" alt="ParksMatter">
              </div>
              <div class="meta">Сформовано: ${escapeHtml(options.createdDate || new Date().toLocaleDateString('uk-UA'))}</div>
            </div>
          </header>
          ${settings.includeFilterSummary ? filterSummarySection(options) : ''}
          ${parkSections(preparedMarkers, options)}
          ${settings.includeMap ? reportMapSection(preparedMarkers, options) : ''}
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

  if (options.includeQrCode !== false && options.mapUrl) {
    try {
      options = { ...options, qrDataUrl: await QRCode.toDataURL(options.mapUrl, { errorCorrectionLevel: 'M', margin: 4, scale: 6 }) }
    } catch {
      throw new Error('Не вдалося створити QR-код. Спробуйте зменшити кількість обраних маркерів або спростити фільтри.')
    }
  }

  const printBlob = new Blob([buildHtml(markers, options)], { type: 'text/html' })
  const printUrl = URL.createObjectURL(printBlob)
  const report = preparedReport(printUrl)

  return {
    ...report,
    opened: report.open(),
  }
}
