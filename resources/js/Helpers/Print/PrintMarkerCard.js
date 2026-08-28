import axios from 'axios'
import { getCoordsFromMarker } from '@/Helpers/Maps/MapHelper'
import { getMarkerTitle, typeUkr } from '@/Helpers/Maps/GetMarkerTitle'

const greenStateLabels = {
  planned: 'Лунка',
  good: 'Добрий',
  normal: 'Задовільний',
  bad: 'Незадовільний',
  removed: 'Видалено',
}

const greenStateClasses = {
  planned: 'state-planned',
  good: 'state-good',
  normal: 'state-normal',
  bad: 'state-bad',
  removed: 'state-removed',
}

function escapeHtml(value) {
  return String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;')
}

function present(value) {
  return value !== null && value !== undefined && value !== ''
}

function formatDate(value) {
  if (!value) return ''
  return String(value).split('T')[0]
}

function formatNumber(value, suffix = '') {
  if (!present(value)) return ''
  return `${value}${suffix}`
}

function formatAge(value) {
  if (!present(value)) return ''
  const totalMonths = Math.max(0, Math.floor(Number(value)))
  return `${Math.floor(totalMonths / 12)} р. ${totalMonths % 12} міс.`
}

function row(label, value) {
  if (!present(value)) return ''

  return `
    <div class="row">
      <dt>${escapeHtml(label)}</dt>
      <dd>${escapeHtml(value)}</dd>
    </div>
  `
}

function rowHtml(label, valueHtml) {
  if (!present(valueHtml)) return ''

  return `
    <div class="row">
      <dt>${escapeHtml(label)}</dt>
      <dd>${valueHtml}</dd>
    </div>
  `
}

function section(title, rows) {
  const content = rows.filter(Boolean).join('')
  if (!content) return ''

  return `
    <section>
      <h2>${escapeHtml(title)}</h2>
      <dl>${content}</dl>
    </section>
  `
}

function stateBadge(state) {
  const label = greenStateLabels[state] || state
  if (!present(label)) return ''

  return `<span class="state-badge ${greenStateClasses[state] || 'state-default'}">${escapeHtml(label)}</span>`
}

function multilineSection(title, value) {
  if (!present(value)) return ''

  return `
    <section>
      <h2>${escapeHtml(title)}</h2>
      <p class="multiline">${escapeHtml(value)}</p>
    </section>
  `
}

function taxonomySection(green) {
  const species = green?.species
  const genus = species?.genus
  const family = genus?.family
  const ukrRows = [
    row('Рід', genus?.name_ukr),
    row('Вид', species?.name_ukr),
    row('Родина', family?.name_ukr),
  ].filter(Boolean).join('')
  const latRows = [
    row('Рід латиною', genus?.name_lat),
    row('Вид латиною', species?.name_lat),
    row('Родина латиною', family?.name_lat),
  ].filter(Boolean).join('')

  if (!ukrRows && !latRows) return ''

  return `
    <section>
      <h2>Класифікація</h2>
      <div class="classification-grid">
        <dl>${ukrRows}</dl>
        <dl>${latRows}</dl>
      </div>
    </section>
  `
}

function typeSpecificRows(marker) {
  const green = marker.green
  const tree = green?.tree
  const bush = green?.bush
  const hedge = green?.hedge

  if (marker.type === 'tree') {
    return [
      row('Висота', formatNumber(tree?.height_m, ' м')),
      row('Діаметр стовбура', formatNumber(tree?.trunk_diameter_cm, ' см')),
      row('Окружність стовбура', formatNumber(tree?.trunk_circumference_cm, ' см')),
      row('Нахил', formatNumber(tree?.tilt_degree, '°')),
      row('Стан крони', formatNumber(tree?.crown_condition_percent, '%')),
      row('Площа', formatNumber(tree?.area, ' м²')),
    ]
  }

  if (marker.type === 'bush') {
    return [
      row('Кількість', bush?.quantity),
      row('Площа', formatNumber(bush?.area, ' м²')),
    ]
  }

  if (marker.type === 'hedge') {
    return [
      row('Довжина', formatNumber(hedge?.length_m, ' м')),
      row('Тип ряду', hedge?.hedge_row?.name),
      row('Форма', hedge?.hedge_shape?.name),
      row('Площа', formatNumber(hedge?.area, ' м²')),
    ]
  }

  return []
}

function tagsSection(tags = []) {
  if (!tags.length) return ''

  return `
    <section>
      <h2>Теги</h2>
      <div class="tags">
        ${tags.map(tag => `<span>${escapeHtml(tag.name)}</span>`).join('')}
      </div>
    </section>
  `
}

function worksSection(works = []) {
  if (!works.length) return ''

  return `
    <section>
      <h2>Роботи</h2>
      <table>
        <thead>
          <tr>
            <th>Рекомендація</th>
            <th>Доручено</th>
            <th>Виконано</th>
            <th>Примітки</th>
          </tr>
        </thead>
        <tbody>
          ${works.map(work => `
            <tr>
              <td>${escapeHtml(work.recommendation?.name || '—')}</td>
              <td>${escapeHtml(formatDate(work.recommendation_date) || '—')}</td>
              <td>${escapeHtml(formatDate(work.execution_date) || '—')}</td>
              <td>${escapeHtml(work.notes || '')}</td>
            </tr>
          `).join('')}
        </tbody>
      </table>
    </section>
  `
}

function photosSection(media = []) {
  if (!media.length) return ''

  return `
    <section>
      <h2>Фото</h2>
      <div class="photo-grid">
        ${media.map(item => `
          <figure>
            <img src="${escapeHtml(item.file_path)}" alt="">
            ${item.description ? `<figcaption>${escapeHtml(item.description)}</figcaption>` : ''}
          </figure>
        `).join('')}
      </div>
    </section>
  `
}

function staticMapUrl(marker, options = {}) {
  const key = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
  if (!key) return null

  const coords = getCoordsFromMarker(marker)
  if (!Number.isFinite(coords.lat) || !Number.isFinite(coords.lng)) return null

  const center = `${coords.lat},${coords.lng}`
  const params = new URLSearchParams({
    center,
    zoom: String(options.zoom ?? 19),
    size: '640x280',
    scale: '2',
    maptype: options.maptype ?? 'hybrid',
    key,
  })
  params.append('markers', center)

  return `https://maps.googleapis.com/maps/api/staticmap?${params.toString()}`
}

function inventoryUrl(inventoryNumber) {
  if (!inventoryNumber) return ''

  return `${window.location.origin}/m/${encodeURIComponent(inventoryNumber)}`
}

function mapSection(marker) {
  const roadmapUrl = staticMapUrl(marker, { maptype: 'roadmap', zoom: 16 })
  const satelliteUrl = staticMapUrl(marker, { maptype: 'hybrid', zoom: 19 })
  if (!roadmapUrl && !satelliteUrl) return ''

  return `
    <section>
      <h2>Розміщення на мапі</h2>
      <div class="map-grid">
        ${roadmapUrl ? `
          <figure>
            <img class="map-shot" src="${escapeHtml(roadmapUrl)}" alt="">
          </figure>
        ` : ''}
        ${satelliteUrl ? `
          <figure>
            <img class="map-shot" src="${escapeHtml(satelliteUrl)}" alt="">
          </figure>
        ` : ''}
      </div>
    </section>
  `
}

async function markerMedia(marker) {
  if (!marker?.id || marker.isDraft) return []

  try {
    const { data } = await axios.get(`/api/markers/${marker.id}/media`)
    return data.media || []
  } catch (error) {
    console.error('Не вдалося завантажити фото для друку:', error)
    return []
  }
}

function buildHtml(marker, media) {
  const green = marker.green
  const coords = getCoordsFromMarker(marker)
  const coordinates = `${coords.lat.toFixed(5)}, ${coords.lng.toFixed(5)}`
  const coordinatesUrl = `https://www.google.com/maps?q=${encodeURIComponent(`${coords.lat},${coords.lng}`)}`
  const title = getMarkerTitle(marker)
  const typeLabel = typeUkr[marker.type] || marker.type

  return `
    <!doctype html>
    <html lang="uk">
      <head>
        <meta charset="utf-8">
        <base href="${escapeHtml(window.location.origin)}/">
        <title>${escapeHtml(title)} - друк</title>
        <style>
          @page { margin: 14mm; }
          * { box-sizing: border-box; }
          body {
            margin: 0;
            background: #fff;
            color: #1f2937;
            font-family: Arial, sans-serif;
            font-size: 12.5px;
            line-height: 1.28;
          }
          .page { max-width: 780px; margin: 0 auto; }
          header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            border-bottom: 2px solid #007c57;
            padding-bottom: 10px;
            margin-bottom: 14px;
          }
          .logos {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            margin-bottom: 4px;
          }
          .logo { max-width: 160px; max-height: 64px; object-fit: contain; }
          .logo-interreg { max-width: 210px; }
          .meta { text-align: right; color: #4b5563; font-size: 11px; }
          h1 { margin: 0 0 2px; color: #007c57; font-size: 22px; line-height: 1.08; }
          .subtitle { margin: 0; color: #4b5563; font-size: 13px; }
          section {
            break-inside: avoid;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 0 9px;
            margin: 0 0 11px;
          }
          h2 { margin: 0 0 7px; font-size: 15px; color: #111827; }
          dl { display: grid; grid-template-columns: 1fr 1fr; gap: 3px 22px; margin: 0; }
          .row { display: grid; grid-template-columns: 128px 1fr; gap: 8px; align-items: center; min-height: 18px; }
          dt { color: #6b7280; }
          dd { margin: 0; font-weight: 600; min-width: 0; }
          .multiline { white-space: pre-wrap; margin: 0; }
          .tags { display: flex; flex-wrap: wrap; gap: 4px; }
          .tags span {
            border: 1px solid #d1d5db;
            border-radius: 999px;
            padding: 2px 8px;
          }
          .classification-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
          }
          .classification-grid dl { display: grid; grid-template-columns: 1fr; gap: 3px; }
          .state-badge {
            display: inline-flex;
            align-items: center;
            min-height: 17px;
            border-radius: 999px;
            padding: 1px 7px;
            font-weight: 700;
            line-height: 1;
            vertical-align: middle;
          }
          .state-planned { background: #dbeafe; color: #1d4ed8; }
          .state-good { background: #dcfce7; color: #15803d; }
          .state-normal { background: #fef3c7; color: #a16207; }
          .state-bad { background: #fee2e2; color: #b91c1c; }
          .state-removed { background: #e5e7eb; color: #374151; }
          .state-default { background: #f3f4f6; color: #374151; }
          .print-link { color: #007c57; font-weight: 700; text-decoration: none; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #e5e7eb; padding: 5px 6px; text-align: left; vertical-align: top; }
          th { background: #f3f4f6; color: #374151; }
          .photo-grid,
          .map-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px;
          }
          figure { margin: 0; break-inside: avoid; }
          figure img, .map-shot {
            width: 100%;
            max-height: 260px;
            object-fit: cover;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
          }
          figcaption { margin-top: 3px; color: #6b7280; font-size: 10.5px; }
          .map-shot { height: 200px; max-height: none; }
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
              <p class="subtitle">${escapeHtml(typeLabel)}${green?.inventory_number ? ` · № ${escapeHtml(green.inventory_number)}` : ''}</p>
            </div>
            <div>
              <div class="logos">
                <img class="logo logo-interreg" src="/img/icons/logo-interreg.png" alt="Interreg NEXT Poland-Ukraine">
                <img class="logo" src="/img/icons/logo-parks-matter.png" alt="ParksMatter">
              </div>
              <div class="meta">Сформовано: ${escapeHtml(new Date().toLocaleDateString('uk-UA'))}</div>
            </div>
          </header>

          ${section('Основна інформація', [
            row('Тип', typeLabel),
            row('Парк', marker.park?.name),
            rowHtml('Інв. номер', green?.inventory_number
              ? `<a class="print-link" href="${escapeHtml(inventoryUrl(green.inventory_number))}" target="_blank">${escapeHtml(green.inventory_number)}</a>
              ${green.tree?.inventory_tag
                ? ` <i>(${escapeHtml(green.tree.inventory_tag)})</i>`
                : ''}`
              : ''
            ),
            rowHtml('Стан', stateBadge(green?.green_state)),
            rowHtml('Дата посадки', formatDate(green?.planting_date)),
            row('Вік', formatAge(green?.age_months)),
            row('Виділ', green?.subplot?.plot?.name || green?.plot?.name),
            green?.green_state === 'removed'
              ? row('Дата видалення', formatDate(green?.green_state_changed_at))
              : '',
            formatDate(green?.green_state_changed_at) !== formatDate(green?.updated_at || marker.updated_at)
              ? row('Оновлено', formatDate(green?.updated_at || marker.updated_at))
              : '',
            row('Ділянка', green?.subplot?.name),
            rowHtml('Координати', `<a class="print-link" href="${escapeHtml(coordinatesUrl)}" target="_blank">${escapeHtml(coordinates)}</a>`),
          ])}
          ${taxonomySection(green)}
          ${section('Властивості', typeSpecificRows(marker))}
          ${multilineSection('Опис', marker.description)}
          ${multilineSection('Коментар до стану', green?.green_state_note)}
          ${tagsSection(marker.tags || [])}
          ${worksSection(green?.works || [])}
          ${mapSection(marker)}
          ${photosSection(media)}
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

export async function printMarkerCard(marker) {
  if (!marker?.green) return

  const printWindow = window.open('', '_blank')
  if (!printWindow) {
    window.print()
    return
  }

  printWindow.document.title = 'Друк картки маркера'
  printWindow.document.body.style.fontFamily = 'Arial, sans-serif'
  printWindow.document.body.style.padding = '24px'
  printWindow.document.body.textContent = 'Готуємо картку для друку...'

  const media = await markerMedia(marker)
  const printBlob = new Blob([buildHtml(marker, media)], { type: 'text/html' })
  const printUrl = URL.createObjectURL(printBlob)

  printWindow.location.replace(printUrl)
  setTimeout(() => URL.revokeObjectURL(printUrl), 60_000)
}
