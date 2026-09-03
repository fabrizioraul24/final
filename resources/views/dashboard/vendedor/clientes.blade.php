@extends('layouts.sidebar-vendedor')

@section('title', 'Clientes | Vendedor')
@section('page-title', 'Clientes')

@section('content')
<div class="fit-users-page fit-companies-page vendor-companies-page">
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <section class="fit-users-header">
        <div class="fit-users-header-left">
            <div class="fit-header-icon"><i class="ri-user-smile-line"></i></div>
            <div>
                <h1>Clientes del Vendedor</h1>
                <p>Gestiona tu cartera comercial con ubicaciones, responsables y datos de contacto.</p>
            </div>
        </div>
        <div class="fit-users-header-actions">
            <button type="button" class="fit-primary-button" id="openCompanyCreate">
                <i class="ri-add-line"></i>
                <span>Registrar Cliente</span>
            </button>
            <a href="{{ route('dashboard.vendedor.companies.report', ['search' => $search, 'type' => $typeFilter]) }}" class="fit-outline-button" target="_blank" rel="noreferrer" data-live-report-link>
                <i class="ri-download-2-line"></i>
                <span>Descargar Reporte PDF</span>
            </a>
        </div>
    </section>

    <section class="fit-metric-grid fit-company-metric-grid">
        <button type="button" class="fit-metric-card indigo {{ blank($typeFilter) ? 'active' : '' }}" data-company-metric="all">
            <span>
                <small>Cartera Total</small>
                <strong>{{ $stats['total'] }}</strong>
                <em>Haz clic para ver todos</em>
            </span>
            <span class="fit-metric-icon"><i class="ri-community-line"></i></span>
        </button>
        <button type="button" class="fit-metric-card amber {{ $typeFilter === 'empresa_institucional' ? 'active' : '' }}" data-company-metric="institutional">
            <span>
                <small>Institucionales</small>
                <strong>{{ $stats['institutional'] }}</strong>
                <em>Precios corporativos</em>
            </span>
            <span class="fit-metric-icon"><i class="ri-building-4-line"></i></span>
        </button>
        <button type="button" class="fit-metric-card green {{ $typeFilter === 'tienda_barrio' ? 'active' : '' }}" data-company-metric="retail">
            <span>
                <small>Tiendas Barrio</small>
                <strong>{{ $stats['retail'] }}</strong>
                <em>Clientes minoristas</em>
            </span>
            <span class="fit-metric-icon"><i class="ri-store-2-line"></i></span>
        </button>
        <button type="button" class="fit-metric-card rose" data-company-metric="inactive">
            <span>
                <small>Desactivados</small>
                <strong>{{ $stats['inactive'] }}</strong>
                <em>Ver recuperables</em>
            </span>
            <span class="fit-metric-icon"><i class="ri-archive-line"></i></span>
        </button>
    </section>

    <div class="modal" id="companyCreateModal">
        <div class="modal-content fit-modal-content company-create-modal" id="companyCreateForm" data-company-form style="max-width:720px;">
            <div class="modal-header">
                <div>
                    <h3>Registrar cliente</h3>
                    <span class="chip" data-company-badge>Modo Empresa institucional</span>
                </div>
                <button class="close-button" type="button" id="closeCompanyCreate">&times;</button>
            </div>
            <p class="text-white/70" style="margin-bottom:1.4rem;" data-company-hint>Completa los datos base de la compania.</p>
            <form method="POST" action="{{ route('dashboard.vendedor.companies.store') }}" class="form-grid">
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
                    <label for="address">Direccion</label>
                    <input type="text" id="address" name="address" class="input-ghost" value="{{ old('address') }}" required>
                    @error('address')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="maps_url">Enlace de Google Maps (opcional)</label>
                    <input type="url" id="maps_url" name="google_maps_url" class="input-ghost" value="{{ old('google_maps_url') }}" placeholder="https://maps.google.com/...">
                    <small>Comparte el enlace directo del mapa para abrirlo rapidamente desde el sistema.</small>
                    @error('google_maps_url')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="map-preview-wrapper" id="mapsPreview" data-map-preview="create">
                    <iframe data-map-frame src=""></iframe>
                    <div class="map-preview-actions">
                        <p class="map-preview-empty" data-map-empty style="margin:0;color:rgba(255,255,255,0.7);">Agrega un enlace de Google Maps para mostrar la vista previa.</p>
                        <a href="#" class="pill-button ghost" data-map-link target="_blank" rel="noopener">Abrir en Google Maps</a>
                    </div>
                </div>
                <div class="form-group">
                    <label for="city">Ciudad</label>
                    <input type="text" id="city" name="city" class="input-ghost" value="{{ old('city') }}" required>
                    @error('city')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="owner_first_name" data-owner-first>Nombre del responsable</label>
                    <input type="text" id="owner_first_name" name="owner_first_name" class="input-ghost" value="{{ old('owner_first_name') }}" required>
                    @error('owner_first_name')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="owner_last_name_paterno" data-owner-paterno>Apellido paterno</label>
                    <input type="text" id="owner_last_name_paterno" name="owner_last_name_paterno" class="input-ghost" value="{{ old('owner_last_name_paterno') }}" required>
                    @error('owner_last_name_paterno')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group">
                    <label for="owner_last_name_materno" data-owner-materno>Apellido materno</label>
                    <input type="text" id="owner_last_name_materno" name="owner_last_name_materno" class="input-ghost" value="{{ old('owner_last_name_materno') }}">
                    @error('owner_last_name_materno')<small style="color:#f87171">{{ $message }}</small>@enderror
                </div>
                <div class="form-group" style="grid-column:1 / -1; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <span class="chip" style="background:rgba(255,255,255,0.15); border-color:rgba(255,255,255,0.3); color:#fff;">Para tiendas de barrio se requieren los datos del dueno.</span>
                </div>
                <div class="fit-modal-footer">
                    <button type="button" class="fit-outline-button" id="cancelCompanyCreate">Cancelar</button>
                    <button type="submit" class="fit-primary-button">
                        <i class="ri-save-line"></i>
                        <span>Guardar cliente</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card" data-company-section="active">
        <div class="chart-head" style="display:flex; justify-content:space-between; align-items:center; gap:1rem;">
            <h4>Filtrar cartera</h4>
            <a href="{{ route('dashboard.vendedor.companies.report', ['search' => $search, 'type' => $typeFilter]) }}" class="pill-button" target="_blank" rel="noreferrer" data-live-report-link>
                <i class="ri-file-text-line"></i> Generar reporte
            </a>
        </div>
        <form method="GET" action="{{ route('dashboard.vendedor.companies') }}" class="form-grid" data-live-search-form>
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
                <a href="{{ route('dashboard.vendedor.companies') }}" class="clean-link">Limpiar</a>
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
                        <th>Cliente</th>
                        <th>NIT</th>
                        <th>Tipo</th>
                        <th>Ciudad</th>
                        <th>Contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeCompanies as $company)
                        <tr>
                            <td>
                                <strong>{{ $company->name }}</strong>
                                <p style="margin:0; color:rgba(255,255,255,0.7);">ID {{ $company->id }}</p>
                            </td>
                            <td>{{ $company->nit }}</td>
                            <td>{{ \App\Models\Company::TYPES[$company->company_type] ?? $company->company_type }}</td>
                            <td>
                                <div>{{ $company->city }}</div>
                                <p style="margin:0; color:rgba(255,255,255,0.6); font-size:0.85rem;">{{ $company->address }}</p>
                                @php
                                    $mapsLink = $company->google_maps_url ?: ($company->address ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($company->address) : null);
                                @endphp
                                @if($mapsLink)
                                    <a class="map-chip" href="{{ $mapsLink }}" target="_blank" rel="noopener">
                                        <i class="ri-map-pin-line"></i> Ver mapa
                                    </a>
                                @endif
                            </td>
                            <td>{{ $company->owner_first_name }} {{ $company->owner_last_name_paterno }}</td>
                            <td>
                                <div class="actions">
                                    <button type="button"
                                        class="fit-action-button success btn-view-company"
                                        title="Ver detalle"
                                        data-company-name="{{ $company->name }}"
                                        data-company-nit="{{ $company->nit }}"
                                        data-company-type="{{ \App\Models\Company::TYPES[$company->company_type] ?? $company->company_type }}"
                                        data-company-city="{{ $company->city }}"
                                        data-company-email="{{ $company->email ?? 'N/D' }}"
                                        data-company-phone="{{ $company->phone ?? 'N/D' }}"
                                        data-company-address="{{ $company->address }}"
                                        data-company-maps="{{ $company->google_maps_url }}"
                                        data-company-owner="{{ trim($company->owner_first_name . ' ' . $company->owner_last_name_paterno . ' ' . $company->owner_last_name_materno) }}">
                                        <i class="ri-eye-line"></i>
                                    </button>
                                    <button type="button"
                                        class="fit-action-button warning btn-edit-company"
                                        title="Editar cliente"
                                        data-company-id="{{ $company->id }}"
                                        data-company-type="{{ $company->company_type }}"
                                        data-company-name="{{ $company->name }}"
                                        data-company-nit="{{ $company->nit }}"
                                        data-company-email="{{ $company->email }}"
                                        data-company-phone="{{ $company->phone }}"
                                        data-company-address="{{ $company->address }}"
                                        data-company-city="{{ $company->city }}"
                                        data-company-maps="{{ $company->google_maps_url }}"
                                        data-owner-first="{{ $company->owner_first_name }}"
                                        data-owner-paterno="{{ $company->owner_last_name_paterno }}"
                                        data-owner-materno="{{ $company->owner_last_name_materno }}">
                                        <i class="ri-pencil-line"></i>
                                    </button>
                                    <form method="POST" action="{{ route('dashboard.vendedor.companies.destroy', $company) }}" onsubmit="return confirm('Desactivar a {{ $company->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="fit-action-button danger" title="Desactivar cliente">
                                            <i class="ri-prohibited-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:1.5rem;">No hay clientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $activeCompanies->appends(['search' => $search, 'type' => $typeFilter])->links() }}
        </div>
    </div>

    <div class="card" style="margin-top:1.5rem;" data-company-section="inactive">
        <div class="chart-head">
            <h4>Clientes desactivados</h4>
            <span class="chip text-white/70">{{ $inactiveCompanies->total() }} en papelera</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>NIT</th>
                        <th>Tipo</th>
                        <th>Ciudad</th>
                        <th>Contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inactiveCompanies as $company)
                        <tr>
                            <td>
                                <strong>{{ $company->name }}</strong>
                                <p style="margin:0; color:rgba(255,255,255,0.7);">ID {{ $company->id }}</p>
                            </td>
                            <td>{{ $company->nit }}</td>
                            <td>{{ \App\Models\Company::TYPES[$company->company_type] ?? $company->company_type }}</td>
                            <td>{{ $company->city }}</td>
                            <td>{{ $company->owner_first_name }} {{ $company->owner_last_name_paterno }}</td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="{{ route('dashboard.vendedor.companies.restore', $company->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="fit-action-button success" title="Restaurar cliente">
                                            <i class="ri-refresh-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:1.5rem;">Sin clientes desactivados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $inactiveCompanies->appends(['search' => $search, 'type' => $typeFilter])->links() }}
        </div>
    </div>

    <div class="modal" id="companyEditModal">
        <div class="modal-content fit-modal-content company-edit-modal" style="max-width:720px;">
            <div class="modal-header">
                <h3>Editar cliente</h3>
                <button class="close-button" type="button" id="closeCompanyEdit">&times;</button>
            </div>
            <form method="POST" id="companyEditForm" data-base-action="{{ route('dashboard.vendedor.companies.update', ['company' => '__company__']) }}">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_company_type">Tipo de empresa</label>
                        <select id="edit_company_type" name="company_type" class="select-light" required>
                            @foreach($companyTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_name">Nombre comercial</label>
                        <input type="text" id="edit_name" name="name" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_nit">NIT</label>
                        <input type="text" id="edit_nit" name="nit" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Correo</label>
                        <input type="email" id="edit_email" name="email" class="input-ghost">
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Telefono</label>
                        <input type="text" id="edit_phone" name="phone" class="input-ghost">
                    </div>
                    <div class="form-group">
                        <label for="edit_address">Direccion</label>
                        <input type="text" id="edit_address" name="address" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_google_maps_url">Enlace de Google Maps</label>
                        <input type="url" id="edit_google_maps_url" name="google_maps_url" class="input-ghost" placeholder="https://maps.google.com/...">
                        <small>Almacena un enlace directo para abrir la ubicacion en una pestana nueva.</small>
                    </div>
                    <div class="map-preview-wrapper" id="editMapsPreview">
                        <iframe data-map-frame src=""></iframe>
                        <div class="map-preview-actions">
                            <p class="map-preview-empty" data-map-empty style="margin:0;color:rgba(255,255,255,0.7);">Agrega un enlace o direccion para previsualizar el mapa.</p>
                            <a href="#" class="pill-button ghost" data-map-link target="_blank" rel="noopener">Ver en Google Maps</a>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_city">Ciudad</label>
                        <input type="text" id="edit_city" name="city" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_owner_first_name">Nombre responsable</label>
                        <input type="text" id="edit_owner_first_name" name="owner_first_name" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_owner_last_name_paterno">Apellido paterno</label>
                        <input type="text" id="edit_owner_last_name_paterno" name="owner_last_name_paterno" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_owner_last_name_materno">Apellido materno</label>
                        <input type="text" id="edit_owner_last_name_materno" name="owner_last_name_materno" class="input-ghost">
                    </div>
                </div>
                <div class="fit-modal-footer">
                    <button type="button" class="fit-outline-button" id="cancelCompanyEdit">Cancelar</button>
                    <button type="submit" class="fit-primary-button">
                        <i class="ri-save-line"></i>
                        <span>Guardar cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function buildEmbedUrl(rawUrl, fallbackText) {
        const url = (rawUrl || '').trim();
        const fallback = (fallbackText || '').trim();
        if (url) {
            if (url.includes('google.com/maps') && !url.includes('output=embed')) {
                return url.includes('?') ? `${url}&output=embed` : `${url}?output=embed`;
            }
            return url;
        }
        if (fallback) {
            return `https://www.google.com/maps?q=${encodeURIComponent(fallback)}&output=embed`;
        }
        return '';
    }

    function initMapsPreview(input, preview, addressInput) {
        if (!preview) return null;
        const iframe = preview.querySelector('[data-map-frame]');
        const link = preview.querySelector('[data-map-link]');
        const empty = preview.querySelector('[data-map-empty]');

        const update = () => {
            const url = input?.value?.trim() ?? '';
            const fallback = addressInput?.value?.trim() ?? '';
            const hasData = Boolean(url || fallback);
            preview.classList.toggle('active', hasData);

            if (!hasData) {
                iframe?.removeAttribute('src');
                if (link) link.href = '#';
                if (empty) empty.style.display = 'block';
                return;
            }

            const embed = buildEmbedUrl(url, fallback);
            if (iframe && embed) {
                iframe.src = embed;
            }
            if (link) {
                link.href = url || embed || '#';
            }
            if (empty) {
                empty.style.display = url || embed ? 'none' : 'block';
            }
        };

        input?.addEventListener('input', update);
        addressInput?.addEventListener('input', () => {
            if (!input?.value?.trim()) {
                update();
            }
        });

        update();
        return update;
    }

    const createMapPreviewUpdate = initMapsPreview(
        document.getElementById('maps_url'),
        document.getElementById('mapsPreview'),
        document.getElementById('address')
    );
    const editMapInput = document.getElementById('edit_google_maps_url');
    const editMapPreview = document.getElementById('editMapsPreview');
    const editAddressInput = document.getElementById('edit_address');
    const refreshEditMapPreview = initMapsPreview(editMapInput, editMapPreview, editAddressInput);

    const metricButtons = document.querySelectorAll('[data-company-metric]');
    const activeCompaniesSection = document.querySelector('[data-company-section="active"]');
    const inactiveCompaniesSection = document.querySelector('[data-company-section="inactive"]');
    const indexRoute = @json(route('dashboard.vendedor.companies'));
    const currentSearch = @json($search);
    const currentType = @json($typeFilter);

    function buildCompanyIndexUrl(type) {
        const url = new URL(indexRoute, window.location.origin);
        if (currentSearch) {
            url.searchParams.set('search', currentSearch);
        }
        if (type) {
            url.searchParams.set('type', type);
        }
        return `${url.pathname}${url.search}`;
    }

    function setActiveCompanyMetric(metric) {
        metricButtons.forEach((button) => {
            button.classList.toggle('active', button.dataset.companyMetric === metric);
        });
    }

    function showCompanySections(showActive, showInactive) {
        if (activeCompaniesSection) {
            activeCompaniesSection.hidden = !showActive;
        }
        if (inactiveCompaniesSection) {
            inactiveCompaniesSection.hidden = !showInactive;
        }
    }

    metricButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const metric = button.dataset.companyMetric;
            const metricTypes = {
                institutional: 'empresa_institucional',
                retail: 'tienda_barrio',
                all: '',
            };

            if (metric === 'inactive') {
                setActiveCompanyMetric('inactive');
                showCompanySections(false, true);
                inactiveCompaniesSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                return;
            }

            if (Object.prototype.hasOwnProperty.call(metricTypes, metric)) {
                const nextType = metricTypes[metric];
                if (nextType !== currentType) {
                    window.location.href = buildCompanyIndexUrl(nextType);
                    return;
                }

                setActiveCompanyMetric(metric);
                showCompanySections(true, true);
            }
        });
    });

    const companyCreateModal = document.getElementById('companyCreateModal');
    function openCompanyCreate() {
        companyCreateModal?.classList.add('active');
        createMapPreviewUpdate?.();
    }
    function closeCompanyCreate() {
        companyCreateModal?.classList.remove('active');
    }
    document.getElementById('openCompanyCreate')?.addEventListener('click', openCompanyCreate);
    document.getElementById('closeCompanyCreate')?.addEventListener('click', closeCompanyCreate);
    document.getElementById('cancelCompanyCreate')?.addEventListener('click', closeCompanyCreate);
    window.addEventListener('click', (event) => {
        if (event.target === companyCreateModal) {
            closeCompanyCreate();
        }
    });
    @if($errors->any())
        openCompanyCreate();
    @endif

    const companyEditModal = document.getElementById('companyEditModal');
    const companyEditForm = document.getElementById('companyEditForm');
    const companyUpdateUrl = companyEditForm.dataset.baseAction;

    document.querySelectorAll('.btn-edit-company').forEach((button) => {
        button.addEventListener('click', () => {
            companyEditForm.action = companyUpdateUrl.replace('__company__', button.dataset.companyId);
            document.getElementById('edit_company_type').value = button.dataset.companyType || '';
            document.getElementById('edit_name').value = button.dataset.companyName || '';
            document.getElementById('edit_nit').value = button.dataset.companyNit || '';
            document.getElementById('edit_email').value = button.dataset.companyEmail || '';
            document.getElementById('edit_phone').value = button.dataset.companyPhone || '';
            document.getElementById('edit_address').value = button.dataset.companyAddress || '';
            if (editMapInput) {
                editMapInput.value = button.dataset.companyMaps || '';
            }
            document.getElementById('edit_city').value = button.dataset.companyCity || '';
            document.getElementById('edit_owner_first_name').value = button.dataset.ownerFirst || '';
            document.getElementById('edit_owner_last_name_paterno').value = button.dataset.ownerPaterno || '';
            document.getElementById('edit_owner_last_name_materno').value = button.dataset.ownerMaterno || '';
            refreshEditMapPreview?.();
            companyEditModal.classList.add('active');
        });
    });

    function closeCompanyEdit() {
        companyEditModal.classList.remove('active');
    }
    document.getElementById('closeCompanyEdit')?.addEventListener('click', closeCompanyEdit);
    document.getElementById('cancelCompanyEdit')?.addEventListener('click', closeCompanyEdit);
    window.addEventListener('click', (event) => {
        if (event.target === companyEditModal) {
            closeCompanyEdit();
        }
    });

    // Modal de vista detalle
    const companyViewModal = document.createElement('div');
    companyViewModal.className = 'modal';
    companyViewModal.id = 'companyViewModal';
    companyViewModal.innerHTML = `
        <div class="modal-content fit-modal-content company-view-modal" style="max-width:640px;">
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
                    <p><strong>Telefono:</strong> <span id="viewPhone"></span></p>
                    <p><strong>Direccion:</strong> <span id="viewAddress"></span></p>
                    <p><strong>Responsable:</strong> <span id="viewOwner"></span></p>
                </div>
                <div style="grid-column:1 / -1;">
                    <div class="map-preview-wrapper" id="viewMapsPreview">
                        <iframe id="viewMapFrame" src=""></iframe>
                        <div class="map-preview-actions">
                            <p class="map-preview-empty" id="viewMapEmpty" style="margin:0;color:rgba(255,255,255,0.7);">Sin enlace de mapa disponible.</p>
                            <a href="#" id="viewMapLink" class="pill-button ghost" target="_blank" rel="noopener">Abrir en Google Maps</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(companyViewModal);
    const viewMapWrapper = companyViewModal.querySelector('#viewMapsPreview');
    const viewMapFrame = companyViewModal.querySelector('#viewMapFrame');
    const viewMapLink = companyViewModal.querySelector('#viewMapLink');
    const viewMapEmpty = companyViewModal.querySelector('#viewMapEmpty');

    function updateViewMapPreview(url, fallbackAddress, fallbackCity) {
        if (!viewMapWrapper) return;
        const fallback = fallbackAddress || fallbackCity || '';
        const hasData = Boolean((url || '').trim() || fallback.trim());
        viewMapWrapper.classList.toggle('active', hasData);
        if (!hasData) {
            viewMapFrame?.removeAttribute('src');
            if (viewMapLink) viewMapLink.href = '#';
            if (viewMapEmpty) viewMapEmpty.style.display = 'block';
            return;
        }
        const embed = buildEmbedUrl(url, fallback);
        if (viewMapFrame && embed) {
            viewMapFrame.src = embed;
        }
        if (viewMapLink) {
            viewMapLink.href = url || embed || '#';
        }
        if (viewMapEmpty) {
            viewMapEmpty.style.display = url || embed ? 'none' : 'block';
        }
    }

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
            updateViewMapPreview(button.dataset.companyMaps || '', button.dataset.companyAddress || '', button.dataset.companyCity || '');
            companyViewModal.classList.add('active');
        });
    });

    companyViewModal.querySelector('#closeCompanyView')?.addEventListener('click', closeCompanyView);
    window.addEventListener('click', (event) => {
        if (event.target === companyViewModal) {
            closeCompanyView();
        }
    });

    // Toggle etiquetas/hints segun tipo de empresa
    const typeSelect = document.querySelector('[data-company-type-select]');
    const badge = document.querySelector('[data-company-badge]');
    const hint = document.querySelector('[data-company-hint]');
    const labelOwner = document.querySelector('[data-owner-first]');
    const labelPaterno = document.querySelector('[data-owner-paterno]');
    const labelMaterno = document.querySelector('[data-owner-materno]');

    function adjustLabels() {
        const isStore = typeSelect?.value === 'tienda_barrio';
        badge.textContent = isStore ? 'Modo Tienda de barrio' : 'Modo Empresa institucional';
        hint.textContent = isStore ? 'Registra datos de la tienda y su duena.' : 'Completa los datos base de la compania.';
        if (labelOwner) labelOwner.textContent = isStore ? 'Nombre de la duena' : 'Nombre del responsable';
        if (labelPaterno) labelPaterno.textContent = isStore ? 'Apellido paterno de la duena' : 'Apellido paterno';
        if (labelMaterno) labelMaterno.textContent = isStore ? 'Apellido materno de la duena' : 'Apellido materno';
    }
    typeSelect?.addEventListener('change', adjustLabels);
    adjustLabels();
</script>
@endpush
