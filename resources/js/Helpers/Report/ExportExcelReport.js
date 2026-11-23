import QRCode from 'qrcode'
import { typeUkr } from '../Maps/GetMarkerTitle.js'
import { getTodayDate } from '../LocalDate.js'
import {
  formatDate,
  formatPlantingDate,
  greenStateLabels,
  greenStateColors,
  greenStateTextColors,
  groupMarkersByPark,
  heatColor,
  markerTagKeys,
  markerTypeLabels,
  markerTypeOrder,
  measureRanges,
  normalizeFilterSummaryItem,
  numericTreeValue,
  plotGroupRows,
  plotName,
  reportMarkers,
  reportSettings,
  speciesName,
  stateCount,
  subplotName,
  typeCount,
  uniqueTags,
  worksText,
} from './ReportData.js'
import { reportMapGroups } from './ReportMaps.js'

const mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
const fontSizes = { compact: 9, normal: 11, large: 13 }
const groupColors = {
  'plot-group-a': '#f4f9ff',
  'plot-group-b': '#fff8e8',
  'subplot-group-a': '#f5fff8',
  'subplot-group-b': '#fff5fb',
}

function argb(color) {
  if (color.startsWith('rgb(')) {
    return 'FF' + color.match(/\d+/g).map(value => Number(value).toString(16).padStart(2, '0')).join('').toUpperCase()
  }
  const hex = color.replace('#', '')
  return 'FF' + (hex.length === 3 ? [...hex].map(char => char + char).join('') : hex).toUpperCase()
}

function fillCell(cell, color) {
  if (color) cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: argb(color) } }
}

function styleMapLink(cell) {
  fillCell(cell, '#dbeafe')
  cell.font = { ...cell.font, bold: true, underline: true, color: { argb: argb('#1d4ed8') } }
}

function fitColumnsToContent(sheet, fixedKeys = []) {
  const context = typeof document === 'undefined'
    ? null
    : document.createElement('canvas').getContext?.('2d')

  for (const column of sheet.columns) {
    if (fixedKeys.includes(column.key)) continue

    let width = 0
    column.eachCell(cell => {
      if (cell.isMerged || cell.value === null) return
      const { name = 'Arial', size = 11, bold = false } = cell.font || {}
      if (context) context.font = `${bold ? 'bold ' : ''}${size * 4 / 3}px ${name}`
      for (const line of cell.text.split(/\r?\n/)) {
        const pixels = context
          ? context.measureText(line).width
          : [...line].length * 7 * size / 11 * (bold ? 1.1 : 1)
        width = Math.max(width, pixels)
      }
    })
    // Excel column widths use the default font's digit width (about 7 px).
    const padding = sheet.autoFilter ? 20 : 10
    column.width = Math.min(255, Math.max(4, Math.ceil((width + padding) / 7)))
  }
}

function styleHeader(row) {
  row.height = 34
  row.eachCell(cell => {
    cell.font = { ...cell.font, bold: true, color: { argb: 'FFFFFFFF' } }
    cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true }
    fillCell(cell, '#166534')
  })
}

function addSheet(workbook, name, columns, settings) {
  const sheet = workbook.addWorksheet(name, {
    properties: { defaultRowHeight: fontSizes[settings.textSize] + 8 },
    pageSetup: { orientation: 'landscape', paperSize: 9, fitToPage: true, fitToWidth: 1, fitToHeight: 0 },
  })
  sheet.columns = columns.map(column => ({
    ...column,
    style: {
      font: { name: 'Arial', size: fontSizes[settings.textSize] },
      alignment: { vertical: 'top', wrapText: true },
    },
  }))
  return sheet
}

function addSummary(workbook, groups, markers, options, settings) {
  const states = Object.keys(greenStateLabels)
  const sheet = addSheet(workbook, 'Підсумки', Array.from({ length: 2 + states.length + markerTypeOrder.length }, () => ({})), settings)
  sheet.addRow([options.title || 'Звіт по насадженнях'])
  sheet.mergeCells(1, 1, 1, sheet.columns.length)
  sheet.getCell('A1').font = { name: 'Arial', size: fontSizes[settings.textSize] + 5, bold: true }
  sheet.getRow(1).height = 28
  sheet.addRow(['Сформовано', options.createdDate || getTodayDate()])
  sheet.addRow(['Маркерів у звіті', markers.length])
  if (options.mapUrl) {
    const link = sheet.addRow(['Мапа вибірки', { text: 'Відкрити мапу', hyperlink: options.mapUrl }]).getCell(2)
    styleMapLink(link)
  }
  sheet.addRow([])
  const header = sheet.addRow(['Парк', 'Всього', ...states.map(state => greenStateLabels[state]), ...markerTypeOrder.map(type => markerTypeLabels[type])])
  styleHeader(header)
  const counts = (name, items) => [name, items.length, ...states.map(state => stateCount(items, state)), ...markerTypeOrder.map(type => typeCount(items, type))]
  groups.forEach(group => sheet.addRow(counts(group.park.name, group.markers)))
  const total = sheet.addRow(counts('Разом', markers))
  total.eachCell(cell => { cell.font = { ...cell.font, bold: true } })
  addFilters(sheet, options, settings)
  fitColumnsToContent(sheet)
  return sheet
}

function addMarkers(workbook, groups, markers, settings) {
  const tags = uniqueTags(markers)
  const hasRemovedMarkers = markers.some(marker => marker.green?.green_state === 'removed')
  const columns = [
    { key: 'index', header: '№ п/п' },
    { key: 'park', header: 'Парк' },
    { key: 'type', header: 'Тип' },
    { key: 'inventory', header: '№ інв.' },
    { key: 'oldInventory', header: '№ старий' },
    { key: 'inventoryTag', header: 'Бірка' },
    { key: 'plot', header: 'Виділ' },
    { key: 'subplot', header: 'Ділянка' },
    { key: 'state', header: 'Стан' },
    { key: 'planted', header: 'Дата висадки' },
    ...(hasRemovedMarkers ? [{ key: 'removed', header: 'Дата видалення' }] : []),
    { key: 'species', header: 'Вид, укр' },
    { key: 'trunk_diameter_cm', header: 'Діаметр на 1,3 м, в см' },
    { key: 'height_m', header: 'Висота, м' },
    ...tags.map((tag, index) => ({ key: `tag_${index}`, header: tag.name })),
    { key: 'works', header: 'Роботи', width: 32 },
    { key: 'recommendations', header: 'Рекомендації', width: 32 },
  ]
  const sheet = addSheet(workbook, 'Маркери', columns, settings)
  const ranges = measureRanges(markers)
  sheet.views = [{ state: 'frozen', xSplit: 2, ySplit: 1 }]
  sheet.pageSetup.printTitlesRow = '1:1'
  styleHeader(sheet.getRow(1))

  for (const group of groups) {
    for (const { marker, plotGroupClass, subplotGroupClass } of plotGroupRows(group.markers)) {
      const green = marker.green
      const markerTags = markerTagKeys(marker)
      const row = sheet.addRow({
        index: sheet.rowCount,
        park: group.park.name,
        type: typeUkr[marker.type] || marker.type,
        // Identifiers stay text, including leading zeroes and values starting with '='.
        inventory: String(green?.inventory_number ?? '').trim(),
        oldInventory: String(green?.inventory_number_old ?? '').trim(),
        inventoryTag: String(green?.tree?.inventory_tag ?? '').trim(),
        plot: plotName(marker),
        subplot: subplotName(marker),
        state: marker.type === 'infrastructure' ? 'Інфраструктура' : (greenStateLabels[green?.green_state] || green?.green_state || '—'),
        planted: formatPlantingDate(green?.planting_date),
        ...(hasRemovedMarkers ? { removed: green?.green_state === 'removed' ? formatDate(green.green_state_changed_at) : '' } : {}),
        species: speciesName(marker),
        trunk_diameter_cm: numericTreeValue(marker, 'trunk_diameter_cm'),
        height_m: numericTreeValue(marker, 'height_m'),
        ...Object.fromEntries(tags.map((tag, index) => [`tag_${index}`, markerTags.has(tag.id) ? '✓' : '×'])),
        works: worksText(marker, true),
        recommendations: worksText(marker, false),
      })

      if (settings.colorGroups) {
        fillCell(row.getCell('plot'), groupColors[plotGroupClass])
        fillCell(row.getCell('subplot'), groupColors[subplotGroupClass])
      }
      const stateColor = marker.type === 'infrastructure' ? '#2563eb' : greenStateColors[green?.green_state]
      if (stateColor) {
        fillCell(row.getCell('state'), stateColor)
        row.getCell('state').font = {
          ...row.getCell('state').font,
          color: { argb: argb(greenStateTextColors[green?.green_state] || '#fff') },
        }
      }
      for (const field of ['trunk_diameter_cm', 'height_m']) {
        const cell = row.getCell(field)
        cell.numFmt = '0.##'
        if (settings.colorTreeSizes) fillCell(cell, heatColor(cell.value, ranges[field]))
      }
    }
  }
  sheet.autoFilter = { from: { row: 1, column: 1 }, to: { row: sheet.rowCount, column: columns.length } }
  fitColumnsToContent(sheet, ['works', 'recommendations'])
}

function addFilters(sheet, options, settings) {
  const summary = Array.isArray(options.filterSummary) ? options.filterSummary.filter(Boolean) : []
  if (!settings.includeFilterSummary || !summary.length) return
  sheet.addRow([])
  const title = sheet.addRow(['Фільтри вибірки'])
  sheet.mergeCells(title.number, 1, title.number, 3)
  title.getCell(1).font = { ...title.getCell(1).font, bold: true }
  styleHeader(sheet.addRow(['Група', 'Фільтр', 'Значення']))
  summary.forEach(item => {
    const { path, label, value } = normalizeFilterSummaryItem(item)
    sheet.addRow([path.join(' / '), label, value])
  })
}

async function addQrCode(workbook, sheet, options) {
  if (options.includeQrCode === false || !options.mapUrl) return
  let base64
  try {
    base64 = await QRCode.toDataURL(options.mapUrl, { errorCorrectionLevel: 'M', margin: 4, scale: 6 })
  } catch {
    throw new Error('Не вдалося створити QR-код. Спробуйте зменшити кількість обраних маркерів або спростити фільтри.')
  }
  const row = sheet.rowCount + 2
  sheet.mergeCells(row, 1, row, 3)
  sheet.getCell(row, 1).value = 'QR-код мапи з фільтрами звіту'
  sheet.addImage(workbook.addImage({ base64, extension: 'png' }), {
    tl: { col: 0, row }, ext: { width: 160, height: 160 },
  })
  sheet.pageSetup.printArea = `A1:${sheet.getColumn(sheet.columnCount).letter}${row + 9}`
}

async function addMaps(workbook, markers, options, settings) {
  if (!settings.includeMap) return
  const sheet = addSheet(workbook, 'Мапи', Array.from({ length: 8 }, () => ({ width: 12 })), settings)
  const groups = reportMapGroups(markers, options)
  if (!groups.length) sheet.getCell('A1').value = 'Зображення мапи недоступне.'
  if (options.mapUrl) {
    sheet.mergeCells('A2:D2')
    const link = sheet.getCell('A2')
    link.value = { text: 'Відкрити мапу вибірки', hyperlink: options.mapUrl }
    styleMapLink(link)
  }
  let row = 4
  for (const group of groups) {
    sheet.mergeCells(row, 1, row, 8)
    sheet.getCell(row, 1).value = group.park.name
    sheet.getCell(row, 1).font = { ...sheet.getCell(row, 1).font, bold: true }
    try {
      const response = await fetch(group.map.url, { signal: AbortSignal.timeout(15_000) })
      if (!response.ok) throw new Error('Map image unavailable')
      const blob = await response.blob()
      if (!blob.type.startsWith('image/')) throw new Error('Invalid map image')
      const base64 = await new Promise((resolve, reject) => {
        const reader = new FileReader()
        reader.onload = () => resolve(reader.result)
        reader.onerror = () => reject(reader.error)
        reader.readAsDataURL(blob)
      })
      sheet.addImage(workbook.addImage({ base64, extension: 'png' }), {
        tl: { col: 0, row }, ext: { width: 640, height: 400 },
      })
      // Reserve enough space even when the compact text size is selected.
      row += 22
      if (group.map.clustered) {
        sheet.mergeCells(row, 1, row, 8)
        sheet.getCell(row, 1).value = `На мапі: ${group.map.markerCount} кластеризованих позначок для ${group.map.originalCount} маркерів. Найбільший кластер: ${group.map.largestCluster}.`
        sheet.getRow(row).height = 36
        row += 2
      }
    } catch {
      sheet.mergeCells(row + 1, 1, row + 2, 8)
      sheet.getCell(row + 1, 1).value = 'Не вдалося завантажити зображення. Скористайтеся посиланням на мапу вибірки.'
      row += 4
    }
    row += 2
  }
}

export async function buildExcelReport(markers, options = {}) {
  const settings = reportSettings(options)
  const preparedMarkers = reportMarkers(markers || [], settings)
  if (!preparedMarkers.length) throw new Error('Немає маркерів для формування звіту.')

  const { default: ExcelJS } = await import('exceljs')
  const workbook = new ExcelJS.Workbook()
  workbook.creator = 'ParksMatter'
  workbook.title = options.title || 'Звіт по насадженнях'
  const groups = groupMarkersByPark(preparedMarkers, options.fallbackPark)
  addMarkers(workbook, groups, preparedMarkers, settings)
  const summary = addSummary(workbook, groups, preparedMarkers, options, settings)
  await addQrCode(workbook, summary, options)
  await addMaps(workbook, preparedMarkers, options, settings)
  return workbook
}

export async function exportExcelReport(markers, options = {}) {
  const workbook = await buildExcelReport(markers, options)
  const buffer = await workbook.xlsx.writeBuffer()
  const url = URL.createObjectURL(new Blob([buffer], { type: mimeType }))
  const link = document.createElement('a')
  link.href = url
  link.download = `parks_report_${options.createdDate || getTodayDate()}.xlsx`
  document.body.appendChild(link)
  try {
    link.click()
  } finally {
    link.remove()
    setTimeout(() => URL.revokeObjectURL(url), 60_000)
  }
}
