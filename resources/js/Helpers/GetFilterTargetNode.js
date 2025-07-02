export function GetFilterTargetNode(filters, path) {
  return path.reduce((target, key) => target?.[key], filters)
}

export function GetOrCreateFilterTargetNode(filters, path) {
  let target = filters
  for (const key of path) {
    if (!target[key]) target[key] = {}
    target = target[key]
  }
  return target
}
