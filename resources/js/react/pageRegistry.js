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
    adminTransferShow: () => import('./pages/AdminTransferShowPage'),
    adminSales: () => import('./pages/AdminSalesPage'),
    adminSaleCreate: () => import('./pages/AdminSaleCreatePage'),
    adminSaleShow: () => import('./pages/AdminSaleShowPage'),
    adminQuotations: () => import('./pages/AdminQuotationsPage'),
    adminQuotationShow: () => import('./pages/AdminQuotationShowPage'),
    adminLogs: () => import('./pages/AdminLogsPage'),
    adminBackups: () => import('./pages/AdminBackupsPage'),
    adminAgentOverview: () => import('./pages/AdminAgentOverviewPage'),
    adminAgentReplenishment: () => import('./pages/AdminAgentReplenishmentPage'),
    adminAgentAlertDetail: () => import('./pages/AdminAgentAlertDetailPage'),
    adminAgentInsights: () => import('./pages/AdminAgentInsightsPage'),
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
