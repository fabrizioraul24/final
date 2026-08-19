@extends('layouts.sidebar')

@section('title', 'Logs del sistema | Pil Andina')
@section('page-title', 'Bitacora de acciones')

@section('content')
    <style>
        .logs-filter-actions {
            display: flex;
            align-items: flex-end;
        }

        .logs-filter-buttons {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.65rem;
            flex-wrap: wrap;
            width: 100%;
        }

        .logs-filter-buttons .pill-button,
        .logs-filter-buttons .btn-secondary,
        .logs-filter-buttons .clean-link {
            min-height: 42px;
        }

        .logs-filter-buttons .pill-button {
            width: auto;
            padding-inline: 1.15rem;
        }

        .logs-pdf-link {
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.45rem;
            min-height: 42px;
            padding: 0.55rem 1rem;
            border-radius: 0.95rem;
            white-space: nowrap;
        }

        @media (max-width: 900px) {
            .logs-filter-actions {
                grid-column: 1 / -1;
            }

            .logs-filter-buttons {
                justify-content: stretch;
            }

            .logs-filter-buttons .pill-button,
            .logs-filter-buttons .btn-secondary,
            .logs-filter-buttons .clean-link {
                flex: 1 1 160px;
                justify-content: center;
            }
        }
    </style>

    <div class="card">
        <div class="chip" style="margin-bottom:1rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
            @php $scope = $filters['scope'] ?? 'all'; @endphp
            <a href="{{ route('dashboard.logs', array_merge(request()->except('page'), ['scope' => 'all'])) }}"
               class="btn-secondary" style="text-decoration:none; padding:0.4rem 0.9rem; border-radius:999px; {{ $scope === 'all' ? 'background: rgba(255,255,255,0.12);' : '' }}">
                Todos
            </a>
            <a href="{{ route('dashboard.logs', array_merge(request()->except('page'), ['scope' => 'login'])) }}"
               class="btn-secondary" style="text-decoration:none; padding:0.4rem 0.9rem; border-radius:999px; {{ $scope === 'login' ? 'background: rgba(255,255,255,0.12);' : '' }}">
                Login/Logout
            </a>
            <a href="{{ route('dashboard.logs', array_merge(request()->except('page'), ['scope' => 'register'])) }}"
               class="btn-secondary" style="text-decoration:none; padding:0.4rem 0.9rem; border-radius:999px; {{ $scope === 'register' ? 'background: rgba(255,255,255,0.12);' : '' }}">
                Registro
            </a>
            <a href="{{ route('dashboard.logs', array_merge(request()->except('page'), ['scope' => 'users'])) }}"
               class="btn-secondary" style="text-decoration:none; padding:0.4rem 0.9rem; border-radius:999px; {{ $scope === 'users' ? 'background: rgba(255,255,255,0.12);' : '' }}">
                Usuarios
            </a>
            <a href="{{ route('dashboard.logs', array_merge(request()->except('page'), ['scope' => 'customers'])) }}"
               class="btn-secondary" style="text-decoration:none; padding:0.4rem 0.9rem; border-radius:999px; {{ $scope === 'customers' ? 'background: rgba(255,255,255,0.12);' : '' }}">
                Clientes
            </a>
            <a href="{{ route('dashboard.logs', array_merge(request()->except('page'), ['scope' => 'products'])) }}"
               class="btn-secondary" style="text-decoration:none; padding:0.4rem 0.9rem; border-radius:999px; {{ $scope === 'products' ? 'background: rgba(255,255,255,0.12);' : '' }}">
                Productos
            </a>
            <a href="{{ route('dashboard.logs', array_merge(request()->except('page'), ['scope' => 'categories'])) }}"
               class="btn-secondary" style="text-decoration:none; padding:0.4rem 0.9rem; border-radius:999px; {{ $scope === 'categories' ? 'background: rgba(255,255,255,0.12);' : '' }}">
                Categorias
            </a>
            <a href="{{ route('dashboard.logs', array_merge(request()->except('page'), ['scope' => 'transfers'])) }}"
               class="btn-secondary" style="text-decoration:none; padding:0.4rem 0.9rem; border-radius:999px; {{ $scope === 'transfers' ? 'background: rgba(255,255,255,0.12);' : '' }}">
                Traspasos
            </a>
        </div>
        <div class="chart-head">
            <h4>Filtros</h4>
        </div>
        <form method="GET" action="{{ route('dashboard.logs') }}" class="form-grid">
            <div class="form-group">
                <label for="actor_id">Usuario (actor)</label>
                <select id="actor_id" name="actor_id" class="select-light">
                    <option value="">Todos</option>
                    @foreach($actors as $actor)
                        <option value="{{ $actor->id }}" @selected($filters['actor_id'] == $actor->id)>{{ $actor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="entity_type">Entidad</label>
                <select id="entity_type" name="entity_type" class="select-light">
                    <option value="">Todas</option>
                    @foreach($entityTypes as $type)
                        <option value="{{ $type }}" @selected($filters['entity_type'] === $type)>{{ class_basename($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="action">Accion</label>
                <select id="action" name="action" class="select-light">
                    <option value="">Todas</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group logs-filter-actions">
                <div class="logs-filter-buttons">
                    <button type="submit" class="pill-button">Aplicar</button>
                    <a href="{{ route('dashboard.logs.report', request()->all()) }}" target="_blank" class="btn-secondary logs-pdf-link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                        <path d="M4.603 12.087a.81.81 0 0 1-.438-.42c-.195-.406-.117-.901.216-1.346.101-.132.253-.266.442-.395a16.185 16.185 0 0 1 1.157-.698l.412-.224-.131-.41c-.267-.833-.427-1.503-.477-2.01-.013-.13-.013-.243-.002-.338.012-.107.039-.241.15-.36.143-.155.336-.215.533-.18c.147.027.28.106.335.25.064.166.046.447-.074.877-.04.144-.093.309-.153.489l-.16.486c.451.277.873.57 1.25.873l.288.232c-.085.49-.168.954-.234 1.341l-.037.214a11.94 11.94 0 0 1-.225 1.05c-.046.173-.092.34-.139.5l-.066.216c.321.05.62.08.887.08.31 0 .584-.04.776-.115a.443.443 0 0 0 .216-.328c.01-.045.01-.103-.003-.173a.818.818 0 0 0-.256-.41c-.082-.075-.2-.14-.34-.197-.13-.054-.28-.093-.448-.116l-.236-.026.04-.25a.824.824 0 0 1 .103-.291c.113-.19.336-.33.74-.33.402 0 .66.215.772.439.143.287.126.657-.058.973-.181.312-.49.53-.889.658-.33.105-.72.158-1.133.158-.293 0-.613-.027-.95-.08a16.242 16.242 0 0 1-1.391-.322c-.611-.183-1.127-.412-1.574-.636l-.371-.188-.415.422a10.03 10.03 0 0 1-.806.75c-.246.202-.45.348-.654.437a1.05 1.05 0 0 1-.418.103zM5.312 9.474a15.46 15.46 0 0 0-1.02.664c-.161.116-.28.235-.353.339a.703.703 0 0 0-.118.435c.002.04.015.08.04.116.035.05.083.076.128.076.064 0 .148-.04.25-.13a7.864 7.864 0 0 0 .638-.636l.435-.487-.001-.271zM11.693 11.19c.142.022.254.05.334.084.06.026.108.053.155.1a.48.48 0 0 1 .135.244c.005.02.007.033.007.037a.16.16 0 0 1-.06.115c-.078.03-.223.05-.411.05-.181 0-.401-.023-.626-.067.198.053.339.04.466.023zm-5.071-2.923c.026.155.053.342.083.56l.08.572c.154-.31.32-.61.492-.888l.112-.178-.008-.007a5.55 5.55 0 0 1-.59-.444 3.111 3.111 0 0 1-.169-.215zM7.147 6.423c.08-.291.139-.55.178-.772.039-.241.059-.418.06-.525a.311.311 0 0 0-.012-.112l-.005-.01a.155.155 0 0 0-.056-.053.254.254 0 0 0-.106-.027c-.035 0-.111.01-.193.099a.382.382 0 0 0-.083.21c-.006.052-.006.13.003.245.03.35.105.794.214 1.245z"/>
                        </svg>
                        PDF
                    </a>
                    <a href="{{ route('dashboard.logs') }}" class="clean-link">Limpiar</a>
                </div>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Listado de logs</h4>
            <span class="chip">{{ $logs->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Actor</th>
                    <th>Entidad</th>
                    <th>Accion</th>
                    <th>Descripcion</th>
                    <th>Detalle</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>
                        <td>{{ $log->user->name ?? 'Sistema' }}</td>
                        <td>{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</td>
                        <td><span class="status-pill">{{ ucfirst($log->action) }}</span></td>
                        <td>{{ $log->description ?? '-' }}</td>
                        <td>
                            @if($log->old_values || $log->new_values)
                                @php
                                    $pdfUrl = class_basename($log->entity_type) === 'Transfer'
                                        ? route('dashboard.transfers.report.single', $log->entity_id)
                                        : null;
                                @endphp
                                <button type="button" class="btn-secondary btn-log-detail"
                                        data-old='@json($log->old_values)'
                                        data-new='@json($log->new_values)'
                                        data-entity="{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}"
                                        @if($pdfUrl) data-pdf="{{ $pdfUrl }}" @endif
                                        style="padding:0.35rem 0.75rem;">
                                    Ver
                                </button>
                            @else
                                <span class="text-white/60">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:1.2rem;">Sin registros para los filtros.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $logs->links() }}
        </div>
    </div>

    <div class="modal" id="logDetailModal">
        <div class="modal-content" style="max-width:800px;">
            <div class="modal-header">
                <h3>Detalle de cambio</h3>
                <button class="close-button" type="button" id="closeLogDetail">&times;</button>
            </div>
            <div id="logDetailBody" style="display:grid; gap:1rem;"></div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const logModal = document.getElementById('logDetailModal');
    const logBody = document.getElementById('logDetailBody');
    const closeLogDetail = document.getElementById('closeLogDetail');

    function renderDiff(oldData, newData) {
        const wrapper = document.createElement('div');
        wrapper.style.display = 'grid';
        wrapper.style.gap = '0.6rem';
        wrapper.style.gridTemplateColumns = 'repeat(auto-fit, minmax(220px, 1fr))';
        const keys = new Set([...Object.keys(oldData || {}), ...Object.keys(newData || {})]);
        keys.forEach((key) => {
            const oldVal = oldData ? oldData[key] : undefined;
            const newVal = newData ? newData[key] : undefined;
            const card = document.createElement('div');
            card.style.border = '1px solid rgba(255,255,255,0.12)';
            card.style.borderRadius = '1rem';
            card.style.padding = '0.75rem 1rem';
            card.innerHTML = `
                <p style="margin:0; font-size:0.85rem; color:rgba(255,255,255,0.7);">${key}</p>
                <p style="margin:0.2rem 0 0;"><strong>Antes:</strong> ${oldVal ?? '-'}</p>
                <p style="margin:0.1rem 0 0;"><strong>Despues:</strong> ${newVal ?? '-'}</p>
            `;
            wrapper.appendChild(card);
        });
        return wrapper;
    }

    document.querySelectorAll('.btn-log-detail').forEach((btn) => {
        btn.addEventListener('click', () => {
            const oldData = JSON.parse(btn.dataset.old || 'null');
            const newData = JSON.parse(btn.dataset.new || 'null');
            const entity = btn.dataset.entity || '';
            const pdfUrl = btn.dataset.pdf || '';
            logBody.innerHTML = '';
            const title = document.createElement('p');
            title.style.margin = '0';
            title.style.color = 'rgba(255,255,255,0.8)';
            title.innerHTML = `<strong>Entidad:</strong> ${entity}`;
            logBody.appendChild(title);
            if (pdfUrl) {
                const link = document.createElement('a');
                link.href = pdfUrl;
                link.target = '_blank';
                link.rel = 'noopener';
                link.textContent = 'Abrir PDF del traspaso';
                link.className = 'pill-button';
                link.style.display = 'inline-flex';
                link.style.width = 'fit-content';
                logBody.appendChild(link);
            }
            logBody.appendChild(renderDiff(oldData, newData));
            logModal.classList.add('active');
        });
    });

    function closeModal() {
        logModal.classList.remove('active');
    }

    closeLogDetail?.addEventListener('click', closeModal);
    window.addEventListener('click', (event) => {
        if (event.target === logModal) closeModal();
    });
</script>
@endpush
