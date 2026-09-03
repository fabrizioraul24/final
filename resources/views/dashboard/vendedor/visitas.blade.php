@extends('layouts.sidebar-vendedor')

@section('title', 'Agenda | Vendedor')
@section('page-title', 'Agenda comercial')

@php
    $statusLabels = [
        'pendiente' => 'Pendiente',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
    ];
    $activeMetric = $filters['status'] ?: 'all';
    $companiesPayload = $companies->map(function ($company) {
        return [
            'id' => $company->id,
            'nit' => $company->nit,
            'name' => $company->name,
            'city' => $company->city,
        ];
    })->values();
@endphp

@section('content')
<div class="vendor-agenda-page">
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="card">
            <span class="chip" style="color:#f87171;">Revisa los datos de la agenda antes de guardar.</span>
        </div>
    @endif

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-calendar-event-line"></i></div>
            <div>
                <h1>Agenda del Vendedor</h1>
                <p>Planifica visitas comerciales usando solo los clientes de tu cartera.</p>
            </div>
        </div>
        <div class="fit-users-header-actions">
            <a href="{{ route('dashboard.vendedor.companies') }}" class="fit-outline-button">
                <i class="ri-user-add-line"></i>
                <span>Clientes</span>
            </a>
        </div>
    </section>

    <section class="fit-metric-grid">
        <a class="fit-metric-card indigo {{ $activeMetric === 'all' ? 'active' : '' }}" href="{{ route('dashboard.vendedor.visits') }}">
            <span><small>Total Agenda</small><strong>{{ $stats['total'] }}</strong><em>Historial comercial</em></span>
            <span class="fit-metric-icon"><i class="ri-calendar-2-line"></i></span>
        </a>
        <a class="fit-metric-card green {{ $activeMetric === 'pendiente' ? 'active' : '' }}" href="{{ route('dashboard.vendedor.visits', ['status' => 'pendiente', 'search' => $filters['search'], 'visit_date' => $filters['visit_date']]) }}">
            <span><small>Pendientes</small><strong>{{ $stats['upcoming'] }}</strong><em>Por atender</em></span>
            <span class="fit-metric-icon"><i class="ri-time-line"></i></span>
        </a>
        <a class="fit-metric-card blue {{ $filters['visit_date'] === now()->toDateString() ? 'active' : '' }}" href="{{ route('dashboard.vendedor.visits', ['visit_date' => now()->toDateString(), 'search' => $filters['search'], 'status' => $filters['status']]) }}">
            <span><small>Hoy</small><strong>{{ $stats['today'] }}</strong><em>{{ now()->format('d/m/Y') }}</em></span>
            <span class="fit-metric-icon"><i class="ri-calendar-check-line"></i></span>
        </a>
        <a class="fit-metric-card amber {{ $activeMetric === 'completada' ? 'active' : '' }}" href="{{ route('dashboard.vendedor.visits', ['status' => 'completada', 'search' => $filters['search'], 'visit_date' => $filters['visit_date']]) }}">
            <span><small>Completadas</small><strong>{{ $stats['completed'] }}</strong><em>Visitas cerradas</em></span>
            <span class="fit-metric-icon"><i class="ri-checkbox-circle-line"></i></span>
        </a>
    </section>

    <section class="vendor-agenda-layout">
        <article class="vendor-agenda-card vendor-agenda-create-card">
            <div class="fit-section-head">
                <div>
                    <h2>Nueva visita</h2>
                    <p>Busca el cliente por nombre o NIT y registra la fecha comprometida.</p>
                </div>
                <span class="fit-section-badge green">Agenda</span>
            </div>

            <form method="POST" action="{{ route('dashboard.vendedor.visits.store') }}" class="vendor-agenda-form" id="visitCreateForm">
                @csrf
                <div class="fit-form-field">
                    <label for="company_lookup">Cliente de tu cartera *</label>
                    <div class="vendor-agenda-lookup">
                        <i class="ri-search-line"></i>
                        <input type="text" id="company_lookup" placeholder="Buscar por NIT o nombre" autocomplete="off">
                        <div id="company_suggestions" class="vendor-agenda-suggestions"></div>
                    </div>
                    <input type="hidden" id="company_id" name="company_id" value="{{ old('company_id') }}">
                    @error('company_id')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="fit-form-field">
                    <label for="visit_date">Fecha de visita *</label>
                    <input type="date" id="visit_date" name="visit_date" value="{{ old('visit_date', now()->toDateString()) }}" required>
                    @error('visit_date')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="fit-form-field">
                    <label for="note">Nota</label>
                    <textarea id="note" name="note" rows="4" placeholder="Ej. Confirmar pedido semanal, revisar vitrina o coordinar reposicion.">{{ old('note') }}</textarea>
                    @error('note')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <button type="submit" class="fit-primary-button">
                    <i class="ri-save-line"></i>
                    <span>Guardar visita</span>
                </button>
            </form>
        </article>

        <article class="vendor-agenda-card vendor-agenda-calendar-card">
            <div class="fit-section-head">
                <div>
                    <h2>Resumen rapido</h2>
                    <p>Estado actual de tus visitas comerciales.</p>
                </div>
            </div>
            <div class="vendor-agenda-summary-list">
                <div><span>Hoy</span><strong>{{ $stats['today'] }}</strong></div>
                <div><span>Pendientes futuras</span><strong>{{ $stats['upcoming'] }}</strong></div>
                <div><span>Completadas</span><strong>{{ $stats['completed'] }}</strong></div>
                <div><span>Canceladas</span><strong>{{ $stats['canceled'] }}</strong></div>
            </div>
        </article>
    </section>

    <section class="fit-filter-card">
        <form method="GET" action="{{ route('dashboard.vendedor.visits') }}" class="fit-filter-form vendor-agenda-filter" data-live-search-form>
            <label class="fit-search-control" for="search">
                <i class="ri-search-line"></i>
                <input type="search" id="search" name="search" value="{{ $filters['search'] }}" placeholder="Buscar cliente o NIT..." data-live-search-input>
            </label>
            <label class="fit-select-control" for="status_filter">
                <i class="ri-checkbox-circle-line"></i>
                <select id="status_filter" name="status">
                    <option value="">Todos los estados</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="fit-search-control" for="visit_date_filter">
                <i class="ri-calendar-line"></i>
                <input type="date" id="visit_date_filter" name="visit_date" value="{{ $filters['visit_date'] }}">
            </label>
            <button type="submit" class="fit-primary-button compact"><i class="ri-search-line"></i><span>Filtrar</span></button>
            @if($filters['search'] || $filters['visit_date'] || $filters['status'])
                <a href="{{ route('dashboard.vendedor.visits') }}" class="fit-clear-button">Limpiar filtros</a>
            @endif
        </form>
    </section>

    <section class="fit-section">
        <div class="fit-section-head">
            <div>
                <h2>Visitas programadas</h2>
                <p>Agenda ordenada por fecha. Cada registro pertenece al vendedor autenticado.</p>
            </div>
            <span class="fit-section-badge green">{{ $visits->total() }} registros</span>
        </div>

        <div class="fit-table-card">
            <div class="fit-table-scroll">
                <table class="fit-users-table vendor-agenda-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>NIT</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Nota</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visits as $visit)
                            @php
                                $status = $visit->status ?? 'pendiente';
                                $statusClass = $status === 'completada' ? 'active' : ($status === 'cancelada' ? 'canceled' : 'pending');
                            @endphp
                            <tr>
                                <td>
                                    <div class="fit-user-cell">
                                        <span class="fit-sale-client-icon"><i class="ri-store-2-line"></i></span>
                                        <div>
                                            <strong>{{ $visit->company->name ?? 'Sin cliente' }}</strong>
                                            <small>{{ $visit->company->city ?? 'Ciudad no registrada' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><code class="fit-code">{{ $visit->company->nit ?? 'N/D' }}</code></td>
                                <td><strong class="vendor-agenda-date">{{ optional($visit->visit_date)->format('d/m/Y') }}</strong></td>
                                <td><span class="fit-transfer-status {{ $statusClass }}"><span></span> {{ $statusLabels[$status] ?? ucfirst($status) }}</span></td>
                                <td><span class="fit-muted-text">{{ $visit->note ?: '-' }}</span></td>
                                <td class="text-right">
                                    <div class="fit-row-actions">
                                        <button type="button"
                                            class="fit-action-button warning btn-edit-visit"
                                            title="Editar visita"
                                            data-id="{{ $visit->id }}"
                                            data-company-id="{{ $visit->company_id }}"
                                            data-visit-date="{{ optional($visit->visit_date)->format('Y-m-d') }}"
                                            data-status="{{ $visit->status }}"
                                            data-note="{{ $visit->note }}">
                                            <i class="ri-pencil-line"></i>
                                        </button>
                                        <form method="POST" action="{{ route('dashboard.vendedor.visits.destroy', $visit) }}" onsubmit="return confirm('Eliminar esta visita?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="fit-action-button danger" title="Eliminar visita">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" style="text-align:center;padding:1rem;">Sin visitas agendadas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top:1rem;">
            {{ $visits->appends($filters)->links() }}
        </div>
    </section>

    <div class="modal" id="visitEditModal">
        <div class="modal-content fit-modal-content" style="max-width:760px;">
            <div class="modal-header">
                <h3>Editar visita</h3>
                <button class="close-button" type="button" id="closeVisitEdit">&times;</button>
            </div>
            <form method="POST" id="visitEditForm" data-base-action="{{ route('dashboard.vendedor.visits.update', ['visit' => '__visit__']) }}">
                @csrf
                @method('PUT')
                <div class="fit-form-grid">
                    <div class="fit-form-field">
                        <label for="edit_company_id">Cliente *</label>
                        <select id="edit_company_id" name="company_id" required>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->nit }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fit-form-field">
                        <label for="edit_visit_date">Fecha *</label>
                        <input type="date" id="edit_visit_date" name="visit_date" required>
                    </div>
                    <div class="fit-form-field">
                        <label for="edit_status">Estado *</label>
                        <select id="edit_status" name="status" required>
                            @foreach($statusLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="fit-form-field span-2">
                        <label for="edit_note">Nota</label>
                        <textarea id="edit_note" name="note" rows="4"></textarea>
                    </div>
                </div>
                <div class="fit-modal-footer">
                    <button type="button" class="fit-outline-button" id="cancelVisitEdit">Cancelar</button>
                    <button type="submit" class="fit-primary-button"><i class="ri-save-line"></i><span>Guardar cambios</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const visitEditModal = document.getElementById('visitEditModal');
    const visitEditForm = document.getElementById('visitEditForm');
    const visitUpdateUrl = visitEditForm?.dataset.baseAction || '';

    const input = document.getElementById('company_lookup');
    const hidden = document.getElementById('company_id');
    const box = document.getElementById('company_suggestions');
    const companies = @json($companiesPayload);

    const selectedCompany = companies.find((company) => String(company.id) === String(hidden?.value || ''));
    if (selectedCompany && input) {
        input.value = `${selectedCompany.nit || 'N/D'} - ${selectedCompany.name}`;
    }

    function renderSuggestions(list) {
        if (!box) return;
        if (!list.length) {
            box.classList.remove('active');
            box.innerHTML = '';
            return;
        }

        box.innerHTML = list.map((company) => `
            <button type="button" class="vendor-agenda-suggestion" data-id="${company.id}">
                <strong>${company.nit || 'N/D'} - ${company.name}</strong>
                <span>${company.city || 'Ciudad no registrada'}</span>
            </button>
        `).join('');
        box.classList.add('active');
    }

    input?.addEventListener('input', () => {
        const term = input.value.trim().toLowerCase();
        if (hidden) hidden.value = '';
        if (!term) {
            renderSuggestions([]);
            return;
        }

        renderSuggestions(companies.filter((company) => {
            return String(company.nit || '').toLowerCase().includes(term)
                || String(company.name || '').toLowerCase().includes(term);
        }).slice(0, 8));
    });

    box?.addEventListener('click', (event) => {
        const option = event.target.closest('.vendor-agenda-suggestion');
        if (!option) return;
        const company = companies.find((item) => String(item.id) === String(option.dataset.id));
        if (!company) return;
        if (input) input.value = `${company.nit || 'N/D'} - ${company.name}`;
        if (hidden) hidden.value = company.id;
        renderSuggestions([]);
    });

    input?.addEventListener('blur', () => {
        setTimeout(() => renderSuggestions([]), 160);
    });

    document.querySelectorAll('.btn-edit-visit').forEach((button) => {
        button.addEventListener('click', () => {
            if (!visitEditForm || !visitEditModal) return;
            visitEditForm.action = visitUpdateUrl.replace('__visit__', button.dataset.id);
            document.getElementById('edit_company_id').value = button.dataset.companyId || '';
            document.getElementById('edit_visit_date').value = button.dataset.visitDate || '';
            document.getElementById('edit_status').value = button.dataset.status || 'pendiente';
            document.getElementById('edit_note').value = button.dataset.note || '';
            visitEditModal.classList.add('active');
        });
    });

    function closeVisitEdit() {
        visitEditModal?.classList.remove('active');
    }

    document.getElementById('closeVisitEdit')?.addEventListener('click', closeVisitEdit);
    document.getElementById('cancelVisitEdit')?.addEventListener('click', closeVisitEdit);
    visitEditModal?.addEventListener('click', (event) => {
        if (event.target === visitEditModal) closeVisitEdit();
    });
})();
</script>
@endpush
