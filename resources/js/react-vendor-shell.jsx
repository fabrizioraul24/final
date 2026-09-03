import React, { useState } from 'react';
import { createRoot } from 'react-dom/client';
import { createPortal } from 'react-dom';
import Sidebar from './react/components/admin/Sidebar';
import Topbar from './react/components/admin/Topbar';

const VENDOR_PATH_PREFIX = '/dashboard/vendedor';

function isVendorUrl(url) {
    return url.origin === window.location.origin && url.pathname.startsWith(VENDOR_PATH_PREFIX);
}

function shouldHandleAnchor(anchor) {
    if (!anchor?.href || anchor.target || anchor.hasAttribute('download')) {
        return false;
    }

    const url = new URL(anchor.href, window.location.origin);

    return isVendorUrl(url);
}

function executeScript(script) {
    return new Promise((resolve, reject) => {
        if (script.type === 'application/json' || script.id === 'vendor-shell-props') {
            resolve();
            return;
        }

        if (script.src) {
            const src = script.getAttribute('src') || '';

            if (src.includes('/build/assets/') || src.includes('dashboard-live-search.js')) {
                resolve();
                return;
            }

            if (document.querySelector(`script[src="${script.src}"]`)) {
                resolve();
                return;
            }
        }

        const nextScript = document.createElement('script');

        Array.from(script.attributes).forEach((attribute) => {
            nextScript.setAttribute(attribute.name, attribute.value);
        });

        nextScript.dataset.vendorSpaScript = 'true';

        if (script.src) {
            nextScript.addEventListener('load', resolve, { once: true });
            nextScript.addEventListener('error', reject, { once: true });
            nextScript.src = script.src;
        } else {
            nextScript.textContent = script.textContent;
            resolve();
        }

        document.body.appendChild(nextScript);
    });
}

async function executePageScripts(doc) {
    document.querySelectorAll('script[data-vendor-spa-script="true"]').forEach((script) => script.remove());

    for (const script of Array.from(doc.body.querySelectorAll('script'))) {
        await executeScript(script);
    }

    window.setupDashboardLiveSearch?.();
}

function VendorShellChrome({ sidebar, topbar, csrfToken, logoutAction }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);
    const [layout, setLayout] = useState({ sidebar, topbar });
    const [isNavigating, setIsNavigating] = useState(false);

    const navigateTo = async (href, { replace = false } = {}) => {
        const url = new URL(href, window.location.origin);

        if (!isVendorUrl(url)) {
            window.location.href = url.href;
            return;
        }

        setIsNavigating(true);
        document.documentElement.classList.add('vendor-spa-loading');

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
            const nextContent = doc.querySelector('.content-scroll');
            const currentContent = document.querySelector('.content-scroll');
            const nextProps = doc.getElementById('vendor-shell-props');

            if (!nextContent || !currentContent || !nextProps) {
                window.location.href = url.href;
                return;
            }

            currentContent.innerHTML = nextContent.innerHTML;
            document.title = doc.title;
            setLayout(JSON.parse(nextProps.textContent || '{}'));

            if (replace) {
                window.history.replaceState({ vendorSpa: true }, '', url.href);
            } else {
                window.history.pushState({ vendorSpa: true }, '', url.href);
            }

            currentContent.scrollTop = 0;
            await executePageScripts(doc);
        } catch (error) {
            console.error(error);
            window.location.href = url.href;
        } finally {
            setIsNavigating(false);
            document.documentElement.classList.remove('vendor-spa-loading');
        }
    };

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

            if (!isVendorUrl(url)) {
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
        window.history.replaceState({ vendorSpa: true }, '', window.location.href);
        window.setupDashboardLiveSearch?.();

        return () => {
            document.removeEventListener('click', onClick);
            document.removeEventListener('submit', onSubmit);
            window.removeEventListener('popstate', onPopState);
        };
    }, []);

    return (
        <>
            {createPortal(
                <Sidebar
                    logoUrl={layout.sidebar.logoUrl}
                    title={layout.sidebar.title}
                    subtitle={layout.sidebar.subtitle}
                    items={layout.sidebar.items}
                    isOpen={sidebarOpen}
                    onClose={() => setSidebarOpen(false)}
                    onNavigate={(event, href) => {
                        event.preventDefault();
                        navigateTo(href);
                    }}
                />,
                document.getElementById('vendor-sidebar-root')
            )}
            {createPortal(
                <Topbar
                    pageTitle={layout.topbar.pageTitle}
                    user={layout.topbar.user}
                    csrfToken={csrfToken}
                    logoutAction={logoutAction}
                    onSidebarToggle={() => setSidebarOpen((value) => !value)}
                />,
                document.getElementById('vendor-topbar-root')
            )}
            {isNavigating && <span className="vendor-spa-progress" aria-hidden="true" />}
        </>
    );
}

const propsElement = document.getElementById('vendor-shell-props');
const rootElement = document.getElementById('vendor-react-shell');

if (propsElement && rootElement) {
    const props = JSON.parse(propsElement.textContent || '{}');
    createRoot(rootElement).render(<VendorShellChrome {...props} />);
}
