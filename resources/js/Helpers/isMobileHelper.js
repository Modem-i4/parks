import { ref } from 'vue'

const isMobile = ref(window.innerWidth < 768)

function update() {
  isMobile.value = window.innerWidth < 768
}

window.addEventListener('resize', update)

export { isMobile }
