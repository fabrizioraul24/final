(() => {
    const formSelector = '[data-live-search-form]';

    function buildQueryString(form) {
        const params = new URLSearchParams();
        const formData = new FormData(form);

        for (const [key, value] of formData.entries()) {
            const normalized = typeof value === 'string' ? value.trim() : value;
            if (key === 'page' || normalized === '') {
                continue;
            }

            params.append(key, normalized);
        }

        return params;
    }

    function updateReportLink(form, reportLink) {
        if (!reportLink) {
            return;
        }

        const url = new URL(reportLink.dataset.baseHref || reportLink.href, window.location.origin);
        url.search = buildQueryString(form).toString();
        reportLink.href = url.toString();
    }

    function setupForm(form) {
        if (form.dataset.liveSearchReady === 'true') {
            return;
        }

        const input = form.querySelector('[data-live-search-input]');
        if (!input) {
            return;
        }

        form.dataset.liveSearchReady = 'true';

        const reportLink = form.querySelector('[data-live-report-link]')
            || form.closest('.card')?.querySelector('[data-live-report-link]');
        if (reportLink && !reportLink.dataset.baseHref) {
            reportLink.dataset.baseHref = reportLink.getAttribute('href') || '';
        }

        const submitDelay = Number(form.dataset.liveSubmitDelay || '450');

        let submitTimer = null;

        const scheduleSubmit = () => {
            window.clearTimeout(submitTimer);
            submitTimer = window.setTimeout(() => {
                form.requestSubmit();
            }, submitDelay);
        };

        input.addEventListener('input', () => {
            updateReportLink(form, reportLink);
            scheduleSubmit();
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                window.clearTimeout(submitTimer);
            }
        });

        form.querySelectorAll('select, input[type="date"], input[type="checkbox"], input[type="radio"]').forEach((field) => {
            field.addEventListener('change', () => {
                updateReportLink(form, reportLink);
                form.requestSubmit();
            });
        });

        updateReportLink(form, reportLink);
    }

    window.setupDashboardLiveSearch = () => {
        document.querySelectorAll(formSelector).forEach(setupForm);
    };

    document.addEventListener('DOMContentLoaded', window.setupDashboardLiveSearch);
})();
