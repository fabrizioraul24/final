@extends('layouts.sidebar')

@section('title', 'Categorias de Productos | Pil Andina')
@section('page-title', 'Gestion de Categorias')

@section('content')
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <style>
        .category-name-stack {
            display: grid;
            gap: 0.35rem;
        }

        .category-name-stack p {
            margin: 0;
            color: rgba(255,255,255,0.72);
            font-size: 0.85rem;
        }

        .category-summary {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: rgba(255,255,255,0.74);
            font-size: 0.82rem;
        }

        .category-summary i {
            color: #86acd4;
        }
    </style>

    <div class="stats-grid">
        <div class="card">
            <h3>Total categorias</h3>
            <div class="value">{{ $total }}</div>
            <span class="chip"><i class="ri-price-tag-3-line"></i> Activas + desactivadas</span>
        </div>
        <div class="card">
            <h3>Con productos asignados</h3>
            <div class="value">{{ $withProducts }}</div>
            <span class="chip text-white/70"><i class="ri-stack-line"></i> Operativas</span>
        </div>
        <div class="card">
            <h3>Desactivadas</h3>
            <div class="value">{{ $inactive }}</div>
            <span class="chip text-white/70"><i class="ri-pause-circle-line"></i> En pausa</span>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Crear categoria</h4>
            <span class="chip">Agrupa productos y organiza el catalogo</span>
        </div>
        <form method="POST" action="{{ route('dashboard.categories.store') }}" class="form-grid">
            @csrf
            <div class="form-group">
                <label for="category_name">Nombre</label>
                <input type="text" id="category_name" name="name" class="input-ghost" placeholder="Ej. Lacteos" value="{{ old('name') }}" required>
                @error('name')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="category_description">Descripcion</label>
                <textarea id="category_description" name="description" rows="2" class="input-ghost" placeholder="Describe el tipo de productos que pertenecen a esta categoria">{{ old('description') }}</textarea>
                @error('description')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Guardar categoria</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Filtrar categorias</h4>
            <a href="{{ route('dashboard.categories.report', ['search' => $search]) }}" target="_blank" rel="noopener" class="pill-button" data-live-report-link>
                <i class="ri-file-pdf-line mr-1"></i> Generar reporte PDF
            </a>
        </div>
        <form method="GET" action="{{ route('dashboard.categories') }}" class="form-grid" data-live-search-form>
            <div class="form-group">
                <label for="search">Buscar por nombre</label>
                <input type="text" id="search" name="search" class="input-ghost" value="{{ $search }}" placeholder="Ej. Lacteos, Bebidas..." data-live-search-input>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route('dashboard.categories') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Categorias activas</h4>
            <span class="chip">{{ $activeCategories->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Creada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeCategories as $category)
                        <tr>
                            <td>
                                <div class="category-name-stack">
                                    <strong>{{ $category->name }}</strong>
                                    <p>{{ \Illuminate\Support\Str::limit($category->description ?: 'Sin descripcion registrada.', 100) }}</p>
                                </div>
                            </td>
                            <td>
                                <span class="category-summary">
                                    <i class="ri-archive-drawer-line"></i>
                                    {{ $category->products_count }} productos
                                </span>
                            </td>
                            <td><span class="status-pill active">Activa</span></td>
                            <td>{{ optional($category->created_at)->format('d/m/Y') }}</td>
                            <td>
                                <div class="actions">
                                    <button
                                        type="button"
                                        class="btn-secondary btn-edit-category"
                                        data-category-id="{{ $category->id }}"
                                        data-category-name="{{ e($category->name) }}"
                                        data-category-description="{{ e($category->description ?? '') }}"
                                    >
                                        Editar
                                    </button>
                                    <form method="POST" action="{{ route('dashboard.categories.destroy', $category) }}" onsubmit="return confirm('Desactivar la categoria {{ $category->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-danger" type="submit">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:1.5rem;">No hay categorias activas para los filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $activeCategories->links() }}
        </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <div class="chart-head">
            <h4>Categorias desactivadas</h4>
            <span class="chip text-white/70">{{ $inactiveCategories->total() }} en pausa</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Categoria</th>
                        <th>Productos</th>
                        <th>Estado</th>
                        <th>Desactivada</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inactiveCategories as $category)
                        <tr>
                            <td>
                                <div class="category-name-stack">
                                    <strong>{{ $category->name }}</strong>
                                    <p>{{ \Illuminate\Support\Str::limit($category->description ?: 'Sin descripcion registrada.', 100) }}</p>
                                </div>
                            </td>
                            <td>
                                <span class="category-summary">
                                    <i class="ri-archive-drawer-line"></i>
                                    {{ $category->products_count }} productos
                                </span>
                            </td>
                            <td><span class="status-pill inactive">Inactiva</span></td>
                            <td>{{ optional($category->deleted_at)->format('d/m/Y') ?? 'Sin fecha' }}</td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="{{ route('dashboard.categories.restore', $category->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn-secondary" type="submit">Reactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:1.5rem;">No hay categorias desactivadas para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $inactiveCategories->links() }}
        </div>
    </div>

    <div class="modal" id="editModal" style="display:none;">
        <div class="modal-content">
            <button class="icon-button close" type="button" id="closeEditModal"><i class="ri-close-line"></i></button>
            <h3>Editar categoria</h3>
            <form id="editForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="edit_category_name">Nombre</label>
                    <input type="text" id="edit_category_name" name="name" class="input-ghost" required>
                </div>
                <div class="form-group">
                    <label for="edit_category_description">Descripcion</label>
                    <textarea id="edit_category_description" name="description" rows="3" class="input-ghost"></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:0.75rem;">
                    <button type="button" class="btn-secondary" id="cancelEditModal">Cancelar</button>
                    <button type="submit" class="pill-button" style="width:auto;">Actualizar categoria</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('editModal');
            const form = document.getElementById('editForm');
            const nameInput = document.getElementById('edit_category_name');
            const descriptionInput = document.getElementById('edit_category_description');
            const closeButtons = [document.getElementById('closeEditModal'), document.getElementById('cancelEditModal')];
            const updateUrlTemplate = @json(route('dashboard.categories.update', ['category' => '__CATEGORY__']));

            if (!modal || !form || !nameInput || !descriptionInput) {
                return;
            }

            const openModal = (button) => {
                form.action = updateUrlTemplate.replace('__CATEGORY__', button.dataset.categoryId);
                nameInput.value = button.dataset.categoryName || '';
                descriptionInput.value = button.dataset.categoryDescription || '';
                modal.style.display = 'flex';
            };

            const closeModal = () => {
                modal.style.display = 'none';
            };

            document.querySelectorAll('.btn-edit-category').forEach((button) => {
                button.addEventListener('click', () => openModal(button));
            });

            closeButtons.forEach((button) => {
                button?.addEventListener('click', closeModal);
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modal.style.display === 'flex') {
                    closeModal();
                }
            });
        });
    </script>
@endpush
