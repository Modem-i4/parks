import { GetFilterTargetNode } from '../Maps/GetFilterTargetNode.js'

export function GetValueByPath(source, path) {
  if (typeof path !== 'string' || path.length === 0) return undefined

  return path
    .split('.')
    .filter(Boolean)
    .reduce((value, key) => value?.[key], source)
}

export function MatchesFilterCondition(condition, filters) {
  if (!condition) return true

  const actual = GetValueByPath(filters, condition.path)
  const expected = condition.value
  const isEmpty = actual == null
    || actual === ''
    || (Array.isArray(actual) && actual.length === 0)

  if (isEmpty && typeof condition.whenEmpty === 'boolean') {
    return condition.whenEmpty
  }

  const includes = (Array.isArray(actual) || typeof actual === 'string')
    && (Array.isArray(expected)
      ? expected.some(value => actual.includes(value))
      : actual.includes(expected))

  switch (condition.operator) {
    case 'includes':
      return includes
    case '!includes':
      return !includes
    case '=':
      return actual === expected
    case '!=':
      return actual !== expected
    default:
      return false
  }
}

export function GetFilterNodeKey(node) {
  switch (node.type) {
    case 'plots':
      return 'plots'
    case 'taxonomy':
      return 'taxonomy'
    default:
      return node.slug
  }
}

export function GetFilterNodeValue(filters, path, node) {
  const target = GetFilterTargetNode(filters, path)
  if (!target || typeof target !== 'object') return undefined

  return target[GetFilterNodeKey(node)]
}

export function RemoveFilterNodeValue(filters, path, node) {
  const target = GetFilterTargetNode(filters, path)
  if (!target || typeof target !== 'object') return

  delete target[GetFilterNodeKey(node)]
}
