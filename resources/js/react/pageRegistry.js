const pageLoaders = {
    landing: () => import('./pages/LandingPage'),
    publicCatalog: () => import('./pages/PublicCatalogPage'),
    login: () => import('./pages/LoginPage'),
    adminDashboard: () => import('./pages/AdminDashboardPage'),
    adminUsers: () => import('./pages/AdminUsersPage'),
    adminCompanies: () => import('./pages/AdminCompaniesPage'),
    adminProducts: () => import('./pages/AdminProductsPage'),
    adminProductForm: () => import('./pages/AdminProductFormPage'),
    adminCategories: () => import('./pages/AdminCategoriesPage'),
    adminLots: () => import('./pages/AdminLotsPage'),
    adminTransfers: () => import('./pages/AdminTransfersPage'),
    adminSales: () => import('./pages/AdminSalesPage'),
    adminQuotations: () => import('./pages/AdminQuotationsPage'),
    adminLogs: () => import('./pages/AdminLogsPage'),
    adminBackups: () => import('./pages/AdminBackupsPage'),
    adminAgentOverview: () => import('./pages/AdminAgentOverviewPage'),
    adminAgentReplenishment: () => import('./pages/AdminAgentReplenishmentPage'),
    adminResource: () => import('./pages/AdminResourcePage'),
};

const pagePromises = new Map();

export function loadPageModule(pageKey) {
    const loader = pageLoaders[pageKey];

    if (!loader) {
        return Promise.reject(new Error(`Unknown React page: ${pageKey}`));
    }

    if (!pagePromises.has(pageKey)) {
        pagePromises.set(pageKey, loader());
    }

    return pagePromises.get(pageKey);
}

export function preloadPage(pageKey) {
    if (!pageKey || !pageLoaders[pageKey]) {
        return;
    }

    loadPageModule(pageKey).catch(() => {});
}
