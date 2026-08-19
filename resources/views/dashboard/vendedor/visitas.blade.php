@extends('layouts.sidebar-vendedor')

@section('title', 'Agenda de visitas | Vendedor')
@section('page-title', 'Agenda de visitas')

@section('content')
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <div class="card">
        <div class="chart-head">
            <h4>Agendar visita</h4>
        </div>
        <form method="POST" action="{{ route('dashboard.vendedor.visits.store') }}" class="form-grid" id="visitCreateForm">
            @csrf
            <div class="form-group">
                <label for="company_lookup">Cliente (NIT o nombre)</label>
                <div style="position:relative;">
                    <input type="text" id="company_lookup" class="input-ghost" placeholder="Ingresa NIT o nombre" autocomplete="off">
                    <div id="company_suggestions" style="position:absolute; z-index:10; background:#1f2937; border:1px solid rgba(255,255,255,0.1); border-radius:12px; width:100%; max-height:180px; overflow-y:auto; display:none;">
                    </div>
                </div>
                <input type="hidden" id="company_id" name="company_id" value="{{ old('company_id') }}">
                <small style="color:#cbd5e1;">Selecciona de la lista para fijar el cliente.</small>
                @error('company_id')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="visit_date">Fecha de visita</label>
                <input type="date" id="visit_date" name="visit_date" class="input-ghost" required>
                @error('visit_date')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group" style="grid-column:1 / -1;">
                <label for="note">Nota</label>
                <input type="text" id="note" name="note" class="input-ghost" placeholder="Ej. Confirmar horario con el encargado">
                @error('note')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Guardar visita</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top:1rem;">
        <div class="chart-head">
            <h4>Visitas programadas</h4>
            <span class="chip">{{ $visits->total() }} registros</span>
        </div>
        <form method="GET" action="{{ route('dashboard.vendedor.visits') }}" class="form-grid" style="margin-bottom:1rem;" data-live-search-form>
            <div class="form-group">
                <label for="search">Buscar por cliente/NIT</label>
                <input type="text" id="search" name="search" class="input-ghost" value="{{ $search }}" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="visit_date_filter">Fecha</label>
                <input type="date" id="visit_date_filter" name="visit_date" class="input-ghost" value="{{ $visitDate }}">
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route('dashboard.vendedor.visits') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>NIT</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Nota</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $visit)
                        <tr>
                            <td>{{ $visit->company->name ?? 'Sin cliente' }}</td>
                            <td>{{ $visit->company->nit ?? 'N/D' }}</td>
                            <td>{{ optional($visit->visit_date)->format('Y-m-d') }}</td>
                            <td>{{ ucfirst($visit->status) }}</td>
                            <td>{{ $visit->note ?? '-' }}</td>
                            <td>
                                <div class="actions">
                                    <button type="button"
                                        class="btn-secondary btn-edit-visit"
                                        data-id="{{ $visit->id }}"
                                        data-company-id="{{ $visit->company_id }}"
                                        data-visit-date="{{ optional($visit->visit_date)->format('Y-m-d') }}"
                                        data-status="{{ $visit->status }}"
                                        data-note="{{ $visit->note }}">
                                        Editar
                                    </button>
                                    <form method="POST" action="{{ route('dashboard.vendedor.visits.destroy', $visit) }}" onsubmit="return confirm('¿Eliminar esta visita?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Eliminar</button>
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
        <div style="margin-top:1rem;">
            {{ $visits->appends(['search' => $search, 'visit_date' => $visitDate])->links() }}
        </div>
    </div>

    <div class="modal" id="visitEditModal">
        <div class="modal-content" style="max-width:720px;">
            <div class="modal-header">
                <h3>Editar visita</h3>
                <button class="close-button" type="button" id="closeVisitEdit">&times;</button>
            </div>
            <form method="POST" id="visitEditForm" data-base-action="{{ route('dashboard.vendedor.visits.update', ['visit' => '__visit__']) }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_company_id">Cliente</label>
                        <select id="edit_company_id" name="company_id" class="select-light" required>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->nit }} - {{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_visit_date">Fecha</label>
                        <input type="date" id="edit_visit_date" name="visit_date" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_status">Estado</label>
                        <select id="edit_status" name="status" class="select-light" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="completada">Completada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column:1 / -1;">
                        <label for="edit_note">Nota</label>
                        <input type="text" id="edit_note" name="note" class="input-ghost">
                    </div>
                </div>
                <div style="margin-top:1.2rem; display:flex; justify-content:flex-end; gap:0.8rem;">
                    <button type="button" class="btn-secondary" id="cancelVisitEdit">Cancelar</button>
                    <button type="submit" class="pill-button">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const visitEditModal = document.getElementById('visitEditModal');
    const visitEditForm = document.getElementById('visitEditForm');
    const visitUpdateUrl = visitEditForm.dataset.baseAction;

    // Autocomplete de cliente por NIT/nombre con sugerencias
    (function() {
        const input = document.getElementById('company_lookup');
        const hidden = document.getElementById('company_id');
        const box = document.getElementById('company_suggestions');
        const companies = [
            @foreach($companies as $company)
            { id: {{ $company->id }}, nit: "{{ $company->nit }}", name: "{{ addslashes($company->name) }}" },
            @endforeach
        ];

        function renderSuggestions(list) {
            if (!list.length) {
                box.style.display = 'none';
                box.innerHTML = '';
                return;
            }
            box.innerHTML = list.map(c => `<div class="suggest-item" data-id="${c.id}" style="padding:8px 10px; cursor:pointer; border-bottom:1px solid rgba(255,255,255,0.06);">${c.nit} - ${c.name}</div>`).join('');
            box.style.display = 'block';
            box.querySelectorAll('.suggest-item').forEach(item => {
                item.addEventListener('click', () => {
                    input.value = item.textContent.trim();
                    hidden.value = item.dataset.id;
                    box.style.display = 'none';
                });
            });
        }

        input?.addEventListener('input', () => {
            const term = input.value.trim().toLowerCase();
            hidden.value = '';
            if (term.length === 0) {
                box.style.display = 'none';
                return;
            }
            const filtered = companies.filter(function (c) {
                return c.nit.toLowerCase().includes(term) || c.name.toLowerCase().includes(term);
            }).slice(0, 8);
            renderSuggestions(filtered);
        });

        input?.addEventListener('blur', () => {
            setTimeout(() => box.style.display = 'none', 150);
        });
    })();

    document.querySelectorAll('.btn-edit-visit').forEach((button) => {
        button.addEventListener('click', () => {
            visitEditForm.action = visitUpdateUrl.replace('__visit__', button.dataset.id);
            document.getElementById('edit_company_id').value = button.dataset.companyId || '';
            document.getElementById('edit_visit_date').value = button.dataset.visitDate || '';
            document.getElementById('edit_status').value = button.dataset.status || 'pendiente';
            document.getElementById('edit_note').value = button.dataset.note || '';
            visitEditModal.classList.add('active');
        });
    });

    function closeVisitEdit() {
        visitEditModal.classList.remove('active');
    }
    document.getElementById('closeVisitEdit')?.addEventListener('click', closeVisitEdit);
    document.getElementById('cancelVisitEdit')?.addEventListener('click', closeVisitEdit);
    window.addEventListener('click', (event) => {
        if (event.target === visitEditModal) {
            closeVisitEdit();
        }
    });
</script>
@endpush
