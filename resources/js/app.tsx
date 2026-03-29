import './bootstrap';
import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ReactNode, ComponentType } from 'react';
import { createRoot } from 'react-dom/client';
import AppLayout from './layouts/AppLayout';

type InertiaPageModule = {
  default: ComponentType & {
    layout?: (page: ReactNode) => ReactNode;
  };
};

createInertiaApp({
  title: (title) => {
    if (!title) return 'Loizou Prints';

    if (title === 'Loizou Prints - Bookstore & Design Store') {
      return title;
    }

    return `${title} - Loizou Prints`;
  },

  resolve: async (name) => {
    const page = (await resolvePageComponent(
      `./pages/${name}.tsx`,
      import.meta.glob('./pages/**/*.tsx')
    )) as InertiaPageModule;

    page.default.layout =
      page.default.layout ||
      ((pageEl: ReactNode) => <AppLayout>{pageEl}</AppLayout>);

    return page;
  },

  setup({ el, App, props }) {
    createRoot(el).render(<App {...props} />);
  },
});