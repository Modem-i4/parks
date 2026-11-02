import LZString from 'lz-string'

// Version the payload so printed links remain readable after future changes.
export function encodeReportFilters(snapshot) {
  return LZString.compressToEncodedURIComponent(JSON.stringify([1, snapshot.filters, snapshot.mode]))
}

export function decodeReportFilters(encoded) {
  try {
    if (!encoded || encoded.length > 12000) return null
    const [version, filters, mode] = JSON.parse(LZString.decompressFromEncodedURIComponent(encoded))
    if (version !== 1 || !filters || typeof filters !== 'object' || Array.isArray(filters)) return null
    if (!['green', 'infrastructure', 'works'].includes(mode)) return null
    // Never permit prototype keys in a payload subsequently used by filter controls.
    if (/"(?:__proto__|constructor|prototype)"\s*:/.test(JSON.stringify(filters))) return null
    return { filters, mode }
  } catch {
    return null
  }
}

export function reportFiltersAsOf(filters, date) {
  const snapshot = JSON.parse(JSON.stringify(filters))
  if (snapshot.green) {
    const general = snapshot.green.general ??= {}
    for (const [key, flag] of [
      ['planting_date_range', 'planting_as_of'],
      ['green_state_changed_at_range', 'removal_as_of'],
    ]) {
      const range = general[key] || [null, null]
      if (!range[0] && !range[1]) general[flag] = true
      general[key] = [range[0] || null, range[1] && range[1] < date ? range[1] : date]
    }
  }
  return snapshot
}

export function reportMapUrl(origin, parkId, snapshot) {
  const url = new URL(parkId ? `/parks/${encodeURIComponent(parkId)}` : '/parks', origin)
  url.searchParams.set('rf', encodeReportFilters(snapshot))
  return url.href
}

export function clearReportFilterUrl(pageUrl, origin, router, parkId = null) {
  const url = new URL(pageUrl, origin)
  if (!url.searchParams.has('rf')) return
  url.searchParams.delete('rf')
  // Keep the single-park map route when the cleaned URL is refreshed.
  if (parkId) url.pathname = `/parks/${encodeURIComponent(parkId)}/m`
  router.replace({
    url: `${url.pathname}${url.search}${url.hash}`,
    preserveState: true,
    preserveScroll: true,
  })
}
