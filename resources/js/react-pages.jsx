import React from 'react';
import { createRoot } from 'react-dom/client';
import { loadPageModule } from './react/pageRegistry';

const rootElement = document.getElementById('react-root');
const propsElement = document.getElementById('react-page-props');

if (rootElement) {
    const pageKey = rootElement.dataset.page;

    if (pageKey) {
        const props = propsElement?.textContent ? JSON.parse(propsElement.textContent) : {};
        const root = createRoot(rootElement);

        loadPageModule(pageKey).then(({ default: PageComponent }) => {
            root.render(
                <React.StrictMode>
                    <PageComponent {...props} />
                </React.StrictMode>,
            );
        });
    }
}
