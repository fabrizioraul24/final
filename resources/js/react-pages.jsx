import React from 'react';
import { createRoot } from 'react-dom/client';
import { loadPageModule } from './react/pageRegistry';
import '../css/admin/admin.css';

const rootElement = document.getElementById('react-root');
const propsElement = document.getElementById('react-page-props');

const ADMIN_PATHS = ['/dashboard/admin', '/dashboard/usuarios', '/dashboard/clientes', '/dashboard/productos', '/dashboard/lotes', '/dashboard/categorias', '/dashboard/traspasos', '/dashboard/ventas', '/dashboard/cotizaciones', '/dashboard/logs', '/dashboard/backups', '/dashboard/agente', '/admin/agente-reposicion'];
const NON_SPA_PATH_PARTS = ['/reporte/pdf', '/download', '/descargar', '/product-lookup', '/live-stats', '/estado', '/data', '/detalle'];

function isAdminSpaUrl(url) {
    if (url.origin !== window.location.origin) {
        return false;
    }

    if (url.pathname.startsWith('/dashboard/vendedor') || url.pathname.startsWith('/dashboard/almacen') || url.pathname.startsWith('/dashboard/comprador') || url.pathname.startsWith('/dashboard/pago')) {
        return false;
    }

    if (NON_SPA_PATH_PARTS.some((part) => url.pathname.includes(part))) {
        return false;
    }

    return ADMIN_PATHS.some((path) => url.pathname === path || url.pathname.startsWith(`${path}/`));
}

function shouldHandleAnchor(anchor) {
    if (!anchor?.href || anchor.target || anchor.hasAttribute('download')) {
        return false;
    }

    return isAdminSpaUrl(new URL(anchor.href, window.location.origin));
}

function readPageFromDocument(doc) {
    const nextRoot = doc.getElementById('react-root');
    const nextProps = doc.getElementById('react-page-props');

    if (!nextRoot?.dataset.page || !nextProps) {
        return null;
    }

    return {
        pageKey: nextRoot.dataset.page,
        props: JSON.parse(nextProps.textContent || '{}'),
        title: doc.title,
    };
}

function AdminSpaApp({ initialPageKey, initialProps }) {
    const [pageState, setPageState] = React.useState({
        pageKey: initialPageKey,
        props: initialProps,
        PageComponent: null,
    });
    const [isNavigating, setIsNavigating] = React.useState(false);

    React.useEffect(() => {
        let cancelled = false;

        document.documentElement.classList.add('react-page-hydrating');

        loadPageModule(pageState.pageKey).then(({ default: PageComponent }) => {
            if (cancelled) {
                return;
            }

            setPageState((current) => ({ ...current, PageComponent }));
            requestAnimationFrame(() => {
                document.documentElement.classList.remove('react-page-hydrating');
                document.documentElement.classList.add('react-page-ready');
            });
        }).catch((error) => {
            document.documentElement.classList.remove('react-page-hydrating');
            throw error;
        });

        return () => {
            cancelled = true;
        };
    }, [pageState.pageKey]);

    const navigateTo = React.useCallback(async (href, { replace = false } = {}) => {
        const url = new URL(href, window.location.origin);

        if (!isAdminSpaUrl(url)) {
            window.location.href = url.href;
            return;
        }

        setIsNavigating(true);
        document.documentElement.classList.add('admin-spa-loading');

        try {
            const response = await fetch(url.href, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                throw new Error(`Navigation failed with status ${response.status}`);
            }

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const nextPage = readPageFromDocument(doc);

            if (!nextPage) {
                window.location.href = url.href;
                return;
            }

            document.title = nextPage.title;
            setPageState({
                pageKey: nextPage.pageKey,
                props: nextPage.props,
                PageComponent: null,
            });

            if (replace) {
                window.history.replaceState({ adminSpa: true }, '', url.href);
            } else {
                window.history.pushState({ adminSpa: true }, '', url.href);
            }

            requestAnimationFrame(() => {
                document.querySelector('.content-scroll')?.scrollTo({ top: 0 });
            });
        } catch (error) {
            console.error(error);
            window.location.href = url.href;
        } finally {
            setIsNavigating(false);
            document.documentElement.classList.remove('admin-spa-loading');
        }
    }, []);

    React.useEffect(() => {
        const onClick = (event) => {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const anchor = event.target instanceof Element ? event.target.closest('a') : null;

            if (!shouldHandleAnchor(anchor)) {
                return;
            }

            event.preventDefault();
            navigateTo(anchor.href);
        };

        const onSubmit = (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement) || String(form.method || 'get').toLowerCase() !== 'get') {
                return;
            }

            const url = new URL(form.action || window.location.href, window.location.origin);

            if (!isAdminSpaUrl(url)) {
                return;
            }

            event.preventDefault();
            const params = new URLSearchParams(new FormData(form));
            url.search = params.toString();
            navigateTo(url.href);
        };

        const onPopState = () => navigateTo(window.location.href, { replace: true });

        document.addEventListener('click', onClick);
        document.addEventListener('submit', onSubmit);
        window.addEventListener('popstate', onPopState);
        window.history.replaceState({ adminSpa: true }, '', window.location.href);

        return () => {
            document.removeEventListener('click', onClick);
            document.removeEventListener('submit', onSubmit);
            window.removeEventListener('popstate', onPopState);
        };
    }, [navigateTo]);

    const PageComponent = pageState.PageComponent;

    return (
        <>
            {PageComponent && <PageComponent {...pageState.props} />}
            {isNavigating && <span className="vendor-spa-progress" aria-hidden="true" />}
        </>
    );
}

if (rootElement) {
    const pageKey = rootElement.dataset.page;

    if (pageKey) {
        const props = propsElement?.textContent ? JSON.parse(propsElement.textContent) : {};
        const root = createRoot(rootElement);

        root.render(
            <React.StrictMode>
                <AdminSpaApp initialPageKey={pageKey} initialProps={props} />
            </React.StrictMode>,
        );
    }
}
