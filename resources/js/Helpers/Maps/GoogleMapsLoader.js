
import { Loader } from '@googlemaps/js-api-loader'

const loader = new Loader({
  apiKey: import.meta.env.VITE_GOOGLE_MAPS_API_KEY,
  language: "uk",
  region: "UA",
  version: 'weekly',
  libraries: ['marker'],
})

export default loader;