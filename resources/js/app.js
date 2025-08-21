import '../css/app.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { createPinia } from 'pinia';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Layout from '@/Layouts/Layout.vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia();

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: async (name) => {
      const pages = import.meta.glob('./Pages/**/*.vue')
      const page = await resolvePageComponent(`./Pages/${name}.vue`, pages)
      if (!page.default.layout) {
        page.default.layout = Layout
      }
      return page
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

router.on('navigate', () => {
  document.body.scrollTo({ top: 0, behavior: 'smooth' })
})