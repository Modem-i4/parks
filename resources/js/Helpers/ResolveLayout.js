import GuestLayout from '@/Layouts/GuestLayout.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const currentLayout = window.location.pathname.startsWith('/admin')
  ? AdminLayout
  : GuestLayout;

export default currentLayout;
