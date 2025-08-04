export const typeUkr = {
    tree: 'Дерево',
    bush: 'Кущ',
    hedge: 'Живопліт',
    flower: 'Квіти',
    infrastructure: 'Інфраструктура',
}
export function getMarkerTitle(marker) {
  return (
    marker.green?.species?.name_ukr ||
    marker.infrastructure?.name ||
    marker.infrastructure?.infrastructure_type?.name ||
    typeUkr[marker.type]
  )
}
