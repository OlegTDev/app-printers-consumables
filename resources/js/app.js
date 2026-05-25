import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import Aura from '@primeuix/themes/aura';
import { dom, library } from '@fortawesome/fontawesome-svg-core';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { fas } from '@fortawesome/free-solid-svg-icons';
import { fab } from '@fortawesome/free-brands-svg-icons';
import { far } from '@fortawesome/free-regular-svg-icons';
import CircleIcon from 'vue-material-design-icons/Circle';
import PrimeVue from 'primevue/config';
import DialogService from 'primevue/dialogservice';
import DynamicDialog from 'primevue/dynamicdialog';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import ConfirmDialog from 'primevue/confirmdialog';
import 'primeicons/primeicons.css';
import Tooltip from 'primevue/tooltip';
import moment from 'moment/moment';
import 'moment/locale/ru';
import { config } from '@/config/config';
import { urls } from '@/config/urls';
import { Auth } from '@/auth';
import { LogActions } from './logActions';
import { ru } from 'primelocale/js/ru.js';
import { ZiggyVue } from 'ziggy-js';



const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

/* add icons to the library */
library.add(fas, far, fab);
dom.watch();

createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  progress: {
    color: '#4B5563',
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .component('font-awesome-icon', FontAwesomeIcon)
      .component('circle-icon', CircleIcon)
      .component('DynamicDialog', DynamicDialog)
      .component('ConfirmDialog', ConfirmDialog)
      .use(plugin)
      .use(ZiggyVue)
      .use(PrimeVue, {
        theme: {
          preset: Aura,
          options: {
            darkModeSelector: '.dark',
          }
        },
        ripple: true,
        locale: ru,
      })
      .use(ToastService)
      .use(DialogService)
      .use(ConfirmationService)
      .directive('tooltip', Tooltip)
      .provide('moment', moment)
      .provide('config', config)
      .provide('urls', urls)
      .provide('auth', new Auth())
      .provide('LogActions', new LogActions(props.initialPage.props?.auth?.user))
      .mount(el);

    return app;
  },
});
