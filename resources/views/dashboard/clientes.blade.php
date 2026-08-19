@extends(request()->routeIs('dashboard.vendedor.*') ? 'layouts.sidebar-vendedor' : 'layouts.sidebar')

@section('title', 'Clientes empresariales | Pil Andina')
@section('page-title', 'Empresas institucionales y Tiendas de Barrio')

@section('content')
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <div class="stats-grid">
        <div class="card">
            <h3>Cartera total</h3>
            <div class="value">{{ $stats['total'] }}</div>
            <span class="chip"><i class="ri-building-4-line"></i> Activos + desactivados</span>
        </div>
        <div class="card">
            <h3>Empresas institucionales</h3>
            <div class="value">{{ $stats['institutional'] }}</div>
            <span class="chip text-white/70">Usan precios corporativos</span>
        </div>
        <div class="card">
            <h3>Tiendas de barrio</h3>
            <div class="value">{{ $stats['retail'] }}</div>
            <span class="chip text-white/70">Con duenas registradas</span>
        </div>
        <div class="card">
            <h3>Desactivados</h3>
            <div class="value">{{ $stats['inactive'] }}</div>
            <span class="chip text-white/70">En papelera (recuperables)</span>
        </div>
    </div>

    <div class="card" id="companyCreateForm" data-company-form>
        <div class="chart-head">
            <h4>Registrar cliente</h4>
            <span class="chip" data-company-badge>Modo Empresa institucional</span>
        </div>
        <p class="text-white/70" style="margin-bottom:1.4rem;" data-company-hint>Completa los datos base de la compania.</p>
        <form method="POST" action="{{ route('dashboard.companies.store') }}" class="form-grid">
            @csrf
            <div class="form-group">
                <label for="company_type" data-company-label data-label-company="Tipo de empresa" data-label-store="Tipo de tienda">Tipo de empresa</label>
                <select id="company_type" name="company_type" class="select-light" required data-company-type-select>
                    @foreach($companyTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('company_type', 'empresa_institucional') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('company_type')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="name">Nombre comercial / Razon social</label>
                <input type="text" id="name" name="name" class="input-ghost" value="{{ old('name') }}" required>
                @error('name')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="nit">NIT</label>
                <input type="text" id="nit" name="nit" class="input-ghost" value="{{ old('nit') }}" required>
                @error('nit')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="email">Correo electronico</label>
                <input type="email" id="email" name="email" class="input-ghost" value="{{ old('email') }}">
                @error('email')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="phone">Telefono</label>
                <input type="text" id="phone" name="phone" class="input-ghost" value="{{ old('phone') }}">
                @error('phone')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="address" data-company-label data-label-company="Direccion fiscal" data-label-store="Direccion de entrega">Direccion fiscal</label>
                <input type="text" id="address" name="address" class="input-ghost" value="{{ old('address') }}" required>
                @error('address')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="city">Ciudad</label>
                <input type="text" id="city" name="city" class="input-ghost" value="{{ old('city') }}" required>
                @error('city')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>

            <div class="form-group" style="grid-column:1 / -1;">
                <hr style="border-color: rgba(255,255,255,0.1);">
                <p class="text-white/70" style="margin:0.6rem 0;">Datos de contacto / duena</p>
            </div>

            <div class="form-group">
                <label for="owner_first_name" data-company-label data-label-company="Nombre del representante" data-label-store="Nombre de la duena">Nombre del representante</label>
                <input type="text" id="owner_first_name" name="owner_first_name" class="input-ghost" value="{{ old('owner_first_name') }}" required>
                @error('owner_first_name')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="owner_last_name_paterno" data-company-label data-label-company="Apellido paterno" data-label-store="Apellido paterno de la duena">Apellido paterno</label>
                <input type="text" id="owner_last_name_paterno" name="owner_last_name_paterno" class="input-ghost" value="{{ old('owner_last_name_paterno') }}" required>
                @error('owner_last_name_paterno')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="owner_last_name_materno" data-company-label data-label-company="Apellido materno" data-label-store="Apellido materno de la duena">Apellido materno</label>
                <input type="text" id="owner_last_name_materno" name="owner_last_name_materno" class="input-ghost" value="{{ old('owner_last_name_materno') }}">
                @error('owner_last_name_materno')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Guardar cliente</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Filtrar cartera</h4>
            <a class="pill-button" target="_blank" rel="noopener" data-live-report-link href="{{ route('dashboard.companies.report', ['search' => $search, 'type' => $typeFilter]) }}">
                <i class="ri-share-forward-line mr-1"></i> Generar reporte PDF
            </a>
        </div>
        <form method="GET" action="{{ route('dashboard.companies') }}" class="form-grid" data-live-search-form>
            <div class="form-group">
                <label for="search">Buscar por nombre, NIT, ciudad o contacto</label>
                <input type="text" id="search" name="search" class="input-ghost" value="{{ $search }}" placeholder="Ej. Supermercado Victoria o 1234567890" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="type">Tipo</label>
                <select id="type" name="type" class="select-light">
                    <option value="">Todos</option>
                    @foreach($companyTypes as $value => $label)
                        <option value="{{ $value }}" @selected($typeFilter === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route('dashboard.companies') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Clientes activos</h4>
            <span class="chip">{{ $activeCompanies->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nombre / NIT</th>
                        <th>Contacto</th>
                        <th>Ciudad</th>
                        <th>Email / Telefono</th>
                        <th>Creado por</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeCompanies as $company)
                        @php
                            $typeLabel = $companyTypes[$company->company_type] ?? 'Tipo no definido';
                            $owner = trim($company->owner_first_name . ' ' . $company->owner_last_name_paterno . ' ' . $company->owner_last_name_materno);
                        @endphp
                        <tr>
                            <td>
                                <span class="status-pill {{ $company->company_type === 'tienda_barrio' ? 'retail' : 'institutional' }}">{{ $typeLabel }}</span>
                            </td>
                            <td>
                                <strong>{{ $company->name }}</strong>
                                <p style="margin:0;color:rgba(255,255,255,0.7); font-size:0.85rem;">NIT: {{ $company->nit }}</p>
                            </td>
                            <td>
                                <p style="margin:0;">{{ $owner ?: 'Sin datos' }}</p>
                            </td>
                            <td>{{ $company->city }}</td>
                            <td>
                                <p style="margin:0;">{{ $company->email ?? 'Sin correo' }}</p>
                                <p style="margin:0;color:rgba(255,255,255,0.7); font-size:0.85rem;">{{ $company->phone ?? 'Sin Telefono' }}</p>
                            </td>
                            <td>{{ $company->creator->name ?? 'Usuario Pil' }}</td>
                            <td>{{ optional($company->created_at)->format('d/m/Y') }}</td>
                            <td>
                                <div class="actions">
                                    <button type="button"
                                        class="btn-secondary btn-view-company"
                                        data-company-name="{{ $company->name }}"
                                        data-company-nit="{{ $company->nit }}"
                                        data-company-type="{{ \App\Models\Company::TYPES[$company->company_type] ?? $company->company_type }}"
                                        data-company-city="{{ $company->city }}"
                                        data-company-email="{{ $company->email ?? 'N/D' }}"
                                        data-company-phone="{{ $company->phone ?? 'N/D' }}"
                                        data-company-address="{{ $company->address }}"
                                        data-company-owner="{{ trim($company->owner_first_name . ' ' . $company->owner_last_name_paterno . ' ' . $company->owner_last_name_materno) }}">
                                        Ver
                                    </button>
                                    <button type="button"
                                        class="btn-secondary btn-edit-company"
                                        data-company-id="{{ $company->id }}"
                                        data-company-type="{{ $company->company_type }}"
                                        data-company-name="{{ $company->name }}"
                                        data-company-nit="{{ $company->nit }}"
                                        data-company-email="{{ $company->email }}"
                                        data-company-phone="{{ $company->phone }}"
                                        data-company-address="{{ $company->address }}"
                                        data-company-city="{{ $company->city }}"
                                        data-company-owner-first="{{ $company->owner_first_name }}"
                                        data-company-owner-lastp="{{ $company->owner_last_name_paterno }}"
                                        data-company-owner-lastm="{{ $company->owner_last_name_materno }}">
                                        Editar
                                    </button>
                                    <form method="POST" action="{{ route('dashboard.companies.destroy', $company) }}" onsubmit="return confirm('ADesactivar a {{ $company->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:1.5rem;">No hay clientes para los filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $activeCompanies->links() }}
        </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <div class="chart-head">
            <h4>Clientes desactivados</h4>
            <span class="chip text-white/70">{{ $inactiveCompanies->total() }} en papelera</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Nombre / NIT</th>
                        <th>Contacto</th>
                        <th>Ciudad</th>
                        <th>Email / Telefono</th>
                        <th>Creado por</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inactiveCompanies as $company)
                        @php
                            $typeLabel = $companyTypes[$company->company_type] ?? 'Tipo no definido';
                            $owner = trim($company->owner_first_name . ' ' . $company->owner_last_name_paterno . ' ' . $company->owner_last_name_materno);
                        @endphp
                        <tr>
                            <td>
                                <span class="status-pill {{ $company->company_type === 'tienda_barrio' ? 'retail' : 'institutional' }}">{{ $typeLabel }}</span>
                            </td>
                            <td>
                                <strong>{{ $company->name }}</strong>
                                <p style="margin:0;color:rgba(255,255,255,0.7); font-size:0.85rem;">NIT: {{ $company->nit }}</p>
                            </td>
                            <td>
                                <p style="margin:0;">{{ $owner ?: 'Sin datos' }}</p>
                            </td>
                            <td>{{ $company->city }}</td>
                            <td>
                                <p style="margin:0;">{{ $company->email ?? 'Sin correo' }}</p>
                                <p style="margin:0;color:rgba(255,255,255,0.7); font-size:0.85rem;">{{ $company->phone ?? 'Sin Telefono' }}</p>
                            </td>
                            <td>{{ $company->creator->name ?? 'Usuario Pil' }}</td>
                            <td>{{ optional($company->created_at)->format('d/m/Y') }}</td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="{{ route('dashboard.companies.restore', $company->id) }}">
                                        @csrf
                                        @method('PaTCH')
                                        <button type="submit" class="btn-secondary">Reactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:1.5rem;">No hay clientes desactivados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $inactiveCompanies->links() }}
        </div>
    </div>

    <div class="modal" id="companyEditModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar cliente</h3>
                <button class="close-button" type="button" id="closeCompanyEdit">&times;</button>
            </div>
            <form method="POST" id="companyEditForm" data-base-action="{{ route('dashboard.companies.update', ['company' => '__company__']) }}">
                @csrf
                @method('PUT')
                <div class="form-grid" data-company-form>
                    <div class="form-group">
                        <label for="edit_company_type" data-company-label data-label-company="Tipo de empresa" data-label-store="Tipo de tienda">Tipo de empresa</label>
                        <select id="edit_company_type" name="company_type" class="select-light" required data-company-type-select>
                            @foreach($companyTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_name">Nombre comercial / Razon social</label>
                        <input type="text" id="edit_name" name="name" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nit">NIT</label>
                        <input type="text" id="edit_nit" name="nit" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Correo electronico</label>
                        <input type="email" id="edit_email" name="email" class="input-ghost">
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Telefono</label>
                        <input type="text" id="edit_phone" name="phone" class="input-ghost">
                    </div>
                    <div class="form-group">
                        <label for="edit_address" data-company-label data-label-company="Direccion fiscal" data-label-store="Direccion de entrega">Direccion fiscal</label>
                        <input type="text" id="edit_address" name="address" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_city">Ciudad</label>
                        <input type="text" id="edit_city" name="city" class="input-ghost" required>
                    </div>
                    <div class="form-group" style="grid-column:1 / -1;">
                        <hr style="border-color: rgba(255,255,255,0.1);">
                        <p class="text-white/70" style="margin:0.6rem 0;" data-company-hint>Actualiza los datos del contacto principal.</p>
                        <span class="chip" data-company-badge>Modo Empresa institucional</span>
                    </div>
                    <div class="form-group">
                        <label for="edit_owner_first_name" data-company-label data-label-company="Nombre del representante" data-label-store="Nombre de la duena">Nombre del representante</label>
                        <input type="text" id="edit_owner_first_name" name="owner_first_name" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_owner_last_name_paterno" data-company-label data-label-company="Apellido paterno" data-label-store="Apellido paterno de la duena">Apellido paterno</label>
                        <input type="text" id="edit_owner_last_name_paterno" name="owner_last_name_paterno" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_owner_last_name_materno" data-company-label data-label-company="Apellido materno" data-label-store="Apellido materno de la duena">Apellido materno</label>
                        <input type="text" id="edit_owner_last_name_materno" name="owner_last_name_materno" class="input-ghost">
                    </div>
                </div>
                <div style="margin-top:1.2rem; display:flex; justify-content:flex-end; gap:0.8rem;">
                    <button type="button" class="btn-secondary" id="cancelCompanyEdit">cancelar</button>
                    <button type="submit" class="pill-button">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function setupCompanyForm(scope) {
        if (!scope) return null;
        const typeSelect = scope.querySelector('[data-company-type-select]');
        if (!typeSelect) return null;
        const badge = scope.querySelector('[data-company-badge]');
        const hint = scope.querySelector('[data-company-hint]');
        const labels = scope.querySelectorAll('[data-company-label]');

        const updateCopy = () => {
            const isStore = typeSelect.value === 'tienda_barrio';
            labels.forEach((label) => {
                const copy = isStore ? label.dataset.labelStore : label.dataset.labelCompany;
                if (copy) {
                    label.textContent = copy;
                }
            });
            if (badge) {
                badge.textContent = isStore ? 'Modo Tienda de barrio' : 'Modo Empresa institucional';
            }
            if (hint) {
                hint.textContent = isStore
                    ? 'captura los datos de la duena para coordinar entregas.'
                    : 'Registra responsable y datos fiscales para la empresa.';
            }
        };

        updateCopy();
        typeSelect.addEventListener('change', updateCopy);

        return updateCopy;
    }

    setupCompanyForm(document.getElementById('companyCreateForm'));

    const companyEditModal = document.getElementById('companyEditModal');
    const companyEditForm = document.getElementById('companyEditForm');
    const companyEditcancel = document.getElementById('cancelCompanyEdit');
    const companyEditClose = document.getElementById('closeCompanyEdit');
    const companyUpdateUrl = companyEditForm.dataset.baseaction;

    const editFormUpdater = setupCompanyForm(companyEditForm);

    document.querySelectorAll('.btn-edit-company').forEach((button) => {
        button.addEventListener('click', () => {
            const companyId = button.dataset.companyId;
            companyEditForm.action = companyUpdateUrl.replace('__company__', companyId);

            document.getElementById('edit_company_type').value = button.dataset.companyType;
            document.getElementById('edit_name').value = button.dataset.companyName || '';
            document.getElementById('edit_nit').value = button.dataset.companyNit || '';
            document.getElementById('edit_email').value = button.dataset.companyEmail || '';
            document.getElementById('edit_phone').value = button.dataset.companyPhone || '';
            document.getElementById('edit_address').value = button.dataset.companyAddress || '';
            document.getElementById('edit_city').value = button.dataset.companyCity || '';
            document.getElementById('edit_owner_first_name').value = button.dataset.companyOwnerFirst || '';
            document.getElementById('edit_owner_last_name_paterno').value = button.dataset.companyOwnerLastp || '';
            document.getElementById('edit_owner_last_name_materno').value = button.dataset.companyOwnerLastm || '';

            if (typeof editFormUpdater === 'function') {
                editFormUpdater();
            }
            companyEditModal.classList.add('active');
        });
    });

    function closeCompanyModal() {
        companyEditModal.classList.remove('active');
    }

    companyEditcancel.addEventListener('click', closeCompanyModal);
    companyEditClose.addEventListener('click', closeCompanyModal);
    window.addEventListener('click', (event) => {
        if (event.target === companyEditModal) {
            closeCompanyModal();
        }
    });

    // Modal de vista detalle
    const companyViewModal = document.createElement('div');
    companyViewModal.className = 'modal';
    companyViewModal.id = 'companyViewModal';
    companyViewModal.innerHTML = `
        <div class="modal-content" style="max-width:640px;">
            <div class="modal-header">
                <h3>Detalle del cliente</h3>
                <button class="close-button" type="button" id="closeCompanyView">&times;</button>
            </div>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:12px;">
                <div>
                    <p><strong>Nombre:</strong> <span id="viewName"></span></p>
                    <p><strong>NIT:</strong> <span id="viewNit"></span></p>
                    <p><strong>Tipo:</strong> <span id="viewType"></span></p>
                    <p><strong>Ciudad:</strong> <span id="viewCity"></span></p>
                </div>
                <div>
                    <p><strong>Email:</strong> <span id="viewEmail"></span></p>
                    <p><strong>Teléfono:</strong> <span id="viewPhone"></span></p>
                    <p><strong>Dirección:</strong> <span id="viewAddress"></span></p>
                    <p><strong>Responsable:</strong> <span id="viewOwner"></span></p>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(companyViewModal);

    function closeCompanyView() {
        companyViewModal.classList.remove('active');
    }

    document.querySelectorAll('.btn-view-company').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('viewName').textContent = button.dataset.companyName || '';
            document.getElementById('viewNit').textContent = button.dataset.companyNit || '';
            document.getElementById('viewType').textContent = button.dataset.companyType || '';
            document.getElementById('viewCity').textContent = button.dataset.companyCity || '';
            document.getElementById('viewEmail').textContent = button.dataset.companyEmail || 'N/D';
            document.getElementById('viewPhone').textContent = button.dataset.companyPhone || 'N/D';
            document.getElementById('viewAddress').textContent = button.dataset.companyAddress || '';
            document.getElementById('viewOwner').textContent = button.dataset.companyOwner || '';
            companyViewModal.classList.add('active');
        });
    });

    companyViewModal.querySelector('#closeCompanyView')?.addEventListener('click', closeCompanyView);
    window.addEventListener('click', (event) => {
        if (event.target === companyViewModal) {
            closeCompanyView();
        }
    });
</script>
@endpush




