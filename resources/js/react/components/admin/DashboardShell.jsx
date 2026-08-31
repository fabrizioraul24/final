import React, { useState } from 'react';
import Sidebar from './Sidebar';
import Topbar from './Topbar';

function DashboardShell({
    sidebar,
    topbar,
    csrfToken,
    logoutAction,
    children,
}) {
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="dashboard-shell">
            <Sidebar
                logoUrl={sidebar.logoUrl}
                items={sidebar.items}
                csrfToken={csrfToken}
                logoutAction={logoutAction}
                isOpen={sidebarOpen}
                onClose={() => setSidebarOpen(false)}
            />
            <main className="main-area">
                <Topbar
                    pageTitle={topbar.pageTitle}
                    user={topbar.user}
                    csrfToken={csrfToken}
                    logoutAction={logoutAction}
                    onSidebarToggle={() => setSidebarOpen((value) => !value)}
                />
                <section className="content-scroll">
                    {children}
                </section>
            </main>

        </div>
    );
}

export default React.memo(DashboardShell);
