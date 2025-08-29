export function pretty(val) {
  if (val === null || val === undefined) return '—'
  if (typeof val === 'object') {
    try { return JSON.stringify(val, null, 2) } catch {}
  }
  if (typeof val === 'string') {
    const s = val.trim()
    if ((s.startsWith('{') && s.endsWith('}')) || (s.startsWith('[') && s.endsWith(']'))) {
      try { return JSON.stringify(JSON.parse(s), null, 2) } catch {}
    }
    return s
  }
  return String(val)
}