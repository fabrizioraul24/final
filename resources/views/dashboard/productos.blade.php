@extends('layouts.sidebar')

@section('title', 'catalogo de Productos | Pil Andina')
@section('page-title', 'Gestion de Productos')

@section('content')
    @php
        // Usar el método getImageUrl() del modelo Product
        $productImageUrl = function ($product) {
            return $product->getImageUrl();
        };
        $laPazWarehouse = $laPazWarehouse ?? null;
    @endphp
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif
    @if($predictionError)
        <div class="card">
            <span class="chip text-yellow-200"><i class="ri-alert-line"></i> Prediccion IA no disponible: {{ $predictionError }}</span>
        </div>
    @endif

    <style>
        .badge-grid { display:flex; gap:0.5rem; flex-wrap:wrap; margin:0.5rem 0 1rem; }
        .highlight-card { background: linear-gradient(120deg, rgba(78,107,175,0.25), rgba(14,165,233,0.1)); border:1px solid rgba(134,172,212,0.45); border-radius:1rem; padding:1rem; box-shadow: inset 0 1px 0 rgba(255,255,255,0.08); }
        .modal-content.enhanced { animation: riseIn 0.45s ease; border:1px solid rgba(255,255,255,0.15); box-shadow: 0 20px 45px rgba(0,0,0,0.55); }
        .product-detail-modal {
            width: min(960px, 94vw);
            max-width: 960px;
            border-radius: 8px;
        }
        .product-detail-layout {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 1.25rem;
            align-items: stretch;
        }
        .product-detail-media {
            min-width: 0;
        }
        .product-detail-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.06);
            box-shadow: 0 16px 28px rgba(0,0,0,0.28);
        }
        .product-detail-body {
            display: grid;
            gap: 1rem;
            min-width: 0;
        }
        .product-detail-title-row {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
        }
        .product-detail-title-row h2 {
            margin: 0.25rem 0 0;
            line-height: 1.1;
            overflow-wrap: anywhere;
        }
        .product-detail-kicker {
            margin: 0;
            color: rgba(255,255,255,0.62);
            font-size: 0.78rem;
            text-transform: uppercase;
        }
        .product-detail-section {
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 8px;
            padding: 1rem;
            background: rgba(255,255,255,0.04);
        }
        .product-detail-section h4 {
            margin: 0 0 0.8rem;
            font-size: 0.92rem;
        }
        .product-detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.8rem;
        }
        .product-detail-field {
            border-radius: 8px;
            padding: 0.85rem;
            background: rgba(255,255,255,0.055);
            border: 1px solid rgba(255,255,255,0.08);
            min-width: 0;
        }
        .product-detail-field span {
            display: block;
            color: rgba(255,255,255,0.58);
            font-size: 0.76rem;
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }
        .product-detail-field strong {
            display: block;
            font-size: 1rem;
            overflow-wrap: anywhere;
        }
        .product-detail-description {
            margin: 0;
            color: rgba(255,255,255,0.78);
            line-height: 1.55;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }
        @media (max-width: 760px) {
            .product-detail-layout {
                grid-template-columns: 1fr;
            }
            .product-detail-image {
                height: 220px;
            }
            .product-detail-title-row {
                display: grid;
            }
            .product-detail-grid {
                grid-template-columns: 1fr;
            }
        }
        @keyframes riseIn { from { opacity:0; transform: translateY(20px) scale(0.98); } to { opacity:1; transform: translateY(0) scale(1); } }
    </style>

    <div class="stats-grid">
        <div class="card">
            <h3>Productos en catalogo</h3>
            <div class="value">{{ $stats['catalog'] }}</div>
            <span class="chip text-white/70"><i class="ri-archive-line"></i> Total registrados</span>
        </div>
        <div class="card">
            <h3>Activos para venta</h3>
            <div class="value">{{ $stats['active'] }}</div>
            <span class="chip text-green-300"><i class="ri-check-line"></i> Disponibles</span>
        </div>
        <div class="card">
            <h3>En pausa</h3>
            <div class="value">{{ $stats['inactive'] }}</div>
            <span class="chip text-red-300"><i class="ri-pause-line"></i> Revision</span>
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Crear producto</h4>
        </div>
        <form method="POST" action="{{ route('dashboard.products.store') }}" class="form-grid" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="category_create">categoria</label>
                <select id="category_create" name="category_id" class="select-light" required>
                    <option value="">Selecciona</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="name">Nombre</label>
                <input type="text" id="name" name="name" class="input-ghost" value="{{ old('name') }}" required>
                @error('name')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="sku">SKU / Codigo</label>
                <input type="text" id="sku" name="sku" class="input-ghost" value="{{ old('sku') }}" required>
                @error('sku')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="image">Imagen del producto</label>
                <input type="file" id="image" name="image" class="input-ghost" accept="image/*" required>
                @error('image')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="suggested_price_public">Precio publico sugerido</label>
                <input type="number" step="0.01" min="0" id="suggested_price_public" name="suggested_price_public" class="input-ghost" value="{{ old('suggested_price_public') }}" required>
                @error('suggested_price_public')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="price_institutional">Precio institucional</label>
                <input type="number" step="0.01" min="0" id="price_institutional" name="price_institutional" class="input-ghost" value="{{ old('price_institutional') }}" required>
                @error('price_institutional')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="min_quantity">Stock minimo</label>
                <input type="number" min="0" id="min_quantity" name="min_quantity" class="input-ghost" value="{{ old('min_quantity', 0) }}" required>
                @error('min_quantity')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="max_quantity">Stock maximo</label>
                <input type="number" min="0" id="max_quantity" name="max_quantity" class="input-ghost" value="{{ old('max_quantity', 0) }}" required>
                @error('max_quantity')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="description">Descripcion</label>
                <textarea id="description" name="description" class="input-ghost" rows="2">{{ old('description') }}</textarea>
                @error('description')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="is_active">Estado</label>
                <select id="is_active" name="is_active" class="select-light" required>
                    <option value="1" @selected(old('is_active', '1') === '1')>Activo</option>
                    <option value="0" @selected(old('is_active', '1') === '0')>Inactivo</option>
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Guardar producto</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Filtrar catalogo</h4>
            <a class="pill-button" target="_blank" rel="noopener" data-live-report-link
               href="{{ route('dashboard.products.report', ['search' => $search, 'category_id' => $categoryId]) }}">
                <i class="ri-file-pdf-line mr-1"></i> Generar catalogo PDF
            </a>
        </div>
        <form class="form-grid" method="GET" action="{{ route('dashboard.products') }}" data-live-search-form>
            <div class="form-group">
                <label for="search">Buscar por nombre, SKU o descripcion</label>
                <input type="text" id="search" name="search" class="input-ghost" placeholder="Ej. Yogurt bebible" value="{{ $search }}" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="category_id">categoria</label>
                <select id="category_id" name="category_id" class="select-light">
                    <option value="">Todas</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected($categoryId == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route('dashboard.products') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Productos activos</h4>
            <span class="chip">{{ $activeProducts->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>categoria</th>
                        <th>Precio publico</th>
                        <th>Precio institucional</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeProducts as $product)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.8rem;">
                                    <img src="{{ $productImageUrl($product) }}" alt="{{ $product->name }}" style="width:52px;height:52px;object-fit:cover;border-radius:1rem;border:1px solid rgba(255,255,255,0.1);">
                                    @if(request()->query('debug_images'))
                                        <div style="font-size:11px;margin-top:0.25rem;color:#fff;opacity:0.9;">
                                            <div>DB path: <code>{{ $product->image_path ?? 'NULL' }}</code></div>
                                            <div>Public exists: <strong>{{ $product->image_path ? (file_exists(public_path('storage/' . $product->image_path)) ? 'yes' : 'no') : 'no' }}</strong></div>
                                            <div>Storage exists: <strong>{{ $product->image_path ? (file_exists(storage_path('app/public/' . $product->image_path)) ? 'yes' : 'no') : 'no' }}</strong></div>
                                            <div>Computed URL: <code>{{ $productImageUrl($product) }}</code></div>
                                        </div>
                                    @endif
                                    @if(request()->query('debug_images'))
                                        <div style="font-size:11px;margin-top:0.25rem;color:#fff;opacity:0.9;">
                                            <div>DB path: <code>{{ $product->image_path ?? 'NULL' }}</code></div>
                                            <div>Public exists: <strong>{{ $product->image_path ? (file_exists(public_path('storage/' . $product->image_path)) ? 'yes' : 'no') : 'no' }}</strong></div>
                                            <div>Storage exists: <strong>{{ $product->image_path ? (file_exists(storage_path('app/public/' . $product->image_path)) ? 'yes' : 'no') : 'no' }}</strong></div>
                                            <div>Computed URL: <code>{{ $productImageUrl($product) }}</code></div>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <p style="margin:0;font-size:0.8rem;color:rgba(255,255,255,0.7);">{{ \Illuminate\Support\Str::limit($product->description ?? 'Sin descripcion', 60) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->category->name ?? 'Sin categoraa' }}</td>
                            <td>Bs {{ number_format($product->suggested_price_public, 2) }}</td>
                            <td>Bs {{ number_format($product->price_institutional, 2) }}</td>
                            <td>
                                <span class="status-pill active">Activo</span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button type="button"
                                        class="btn-secondary btn-view-product"
                                        data-product-name="{{ $product->name }}"
                                        data-product-sku="{{ $product->sku }}"
                                        data-product-category="{{ $product->category->name ?? 'Sin categoria' }}"
                                        data-product-description="{{ $product->description }}"
                                        data-product-public="{{ number_format($product->suggested_price_public, 2) }}"
                                        data-product-institutional="{{ number_format($product->price_institutional, 2) }}"
                                        data-product-image="{{ $productImageUrl($product) }}"
                                        data-product-min="{{ $product->min_quantity }}"
                                        data-product-max="{{ $product->max_quantity }}"
                                        data-product-status="{{ $product->is_active ? 'Activo' : 'Inactivo' }}"
                                        data-product-stock-total="{{ $product->inventory->sum('quantity') }}"
                                        >
                                        Detalles
                                    </button>
                                    <button type="button"
                                        class="btn-secondary btn-edit-product"
                                        data-product-id="{{ $product->id }}"
                                        data-product-category="{{ $product->category_id }}"
                                        data-product-name="{{ $product->name }}"
                                        data-product-sku="{{ $product->sku }}"
                                        data-product-image="{{ $productImageUrl($product) }}"
                                        data-product-public="{{ $product->suggested_price_public }}"
                                        data-product-institutional="{{ $product->price_institutional }}"
                                        data-product-min="{{ $product->min_quantity }}"
                                        data-product-max="{{ $product->max_quantity }}"
                                        data-product-description="{{ $product->description }}"
                                        data-product-active="{{ $product->is_active ? '1' : '0' }}">
                                        Editar
                                    </button>
                                    <form method="POST" action="{{ route('dashboard.products.toggle', $product) }}">
                                        @csrf
                                        @method('PaTCH')
                                        <button type="submit" class="btn-danger">
                                            Desactivar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:1.5rem;">No hay productos con los filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $activeProducts->appends(['search' => $search, 'category_id' => $categoryId])->links() }}
        </div>
    </div>

    <div class="card" style="margin-top:1.5rem;">
        <div class="chart-head">
            <h4>Productos desactivados</h4>
            <span class="chip text-white/70">{{ $inactiveProducts->total() }} en pausa</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>SKU</th>
                        <th>categoria</th>
                        <th>Precio publico</th>
                        <th>Precio institucional</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inactiveProducts as $product)
                        <tr>
                            <td>
                                <div style="display:flex; align-items:center; gap:0.8rem;">
                                    <img src="{{ $productImageUrl($product) }}" alt="{{ $product->name }}" style="width:52px;height:52px;object-fit:cover;border-radius:1rem;border:1px solid rgba(255,255,255,0.1);">
                                    <div>
                                        <strong>{{ $product->name }}</strong>
                                        <p style="margin:0;font-size:0.8rem;color:rgba(255,255,255,0.7);">{{ \Illuminate\Support\Str::limit($product->description ?? 'Sin descripcion', 60) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->category->name ?? 'Sin categoria' }}</td>
                            <td>Bs {{ number_format($product->suggested_price_public, 2) }}</td>
                            <td>Bs {{ number_format($product->price_institutional, 2) }}</td>
                            <td>
                                <span class="status-pill inactive">Inactivo</span>
                            </td>
                            <td>
                                <div class="actions">
                                    <button type="button"
                                        class="btn-secondary btn-view-product"
                                        data-product-name="{{ $product->name }}"
                                        data-product-sku="{{ $product->sku }}"
                                        data-product-category="{{ $product->category->name ?? 'Sin categoria' }}"
                                        data-product-description="{{ $product->description }}"
                                        data-product-public="{{ number_format($product->suggested_price_public, 2) }}"
                                        data-product-institutional="{{ number_format($product->price_institutional, 2) }}"
                                        data-product-image="{{ $productImageUrl($product) }}"
                                        data-product-min="{{ $product->min_quantity }}"
                                        data-product-max="{{ $product->max_quantity }}"
                                        data-product-status="{{ $product->is_active ? 'Activo' : 'Inactivo' }}"
                                        data-product-stock-total="{{ $product->inventory->sum('quantity') }}">
                                        Detalles
                                    </button>
                                    <form method="POST" action="{{ route('dashboard.products.toggle', $product) }}">
                                        @csrf
                                        @method('PaTCH')
                                        <button type="submit" class="btn-secondary">
                                            Activar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center;padding:1.5rem;">No hay productos desactivados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $inactiveProducts->appends(['search' => $search, 'category_id' => $categoryId])->links() }}
        </div>
    </div>

    <div class="modal" id="productEditModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar producto</h3>
                <button class="close-button" type="button" id="closeProductEdit">&times;</button>
            </div>
            <form method="POST" id="productEditForm" data-base-action="{{ route('dashboard.products.update', ['product' => '__product__']) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_category_id">categoria</label>
                        <select id="edit_category_id" name="category_id" class="select-light" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_name">Nombre</label>
                        <input type="text" id="edit_name" name="name" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_sku">SKU</label>
                        <input type="text" id="edit_sku" name="sku" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_image">Imagen nueva (opcional)</label>
                        <input type="file" id="edit_image" name="image" class="input-ghost" accept="image/*">
                        @error('image')<small style="color:#f87171">{{ $message }}</small>@enderror
                    </div>
                    <div class="form-group">
                        <label for="edit_suggested_price_public">Precio publico sugerido</label>
                        <input type="number" step="0.01" min="0" id="edit_suggested_price_public" name="suggested_price_public" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_price_institutional">Precio institucional</label>
                        <input type="number" step="0.01" min="0" id="edit_price_institutional" name="price_institutional" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_min_quantity">Stock minimo</label>
                        <input type="number" min="0" id="edit_min_quantity" name="min_quantity" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_max_quantity">Stock maximo</label>
                        <input type="number" min="0" id="edit_max_quantity" name="max_quantity" class="input-ghost" required>
                    </div>
                    <div class="form-group" style="grid-column:1 / -1;">
                        <label for="edit_description">Descripcion</label>
                        <textarea id="edit_description" name="description" class="input-ghost" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit_is_active">Estado</label>
                        <select id="edit_is_active" name="is_active" class="select-light" required>
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top:1.2rem; display:flex; justify-content:flex-end; gap:0.8rem;">
                    <button type="button" class="btn-secondary" id="cancelProductEdit">cancelar</button>
                    <button type="submit" class="pill-button">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal" id="productViewModal">
        <div class="modal-content enhanced product-detail-modal">
            <div class="modal-header">
                <h3>Detalle del producto</h3>
                <button class="close-button" type="button" id="closeProductView">&times;</button>
            </div>
            <div class="product-detail-layout">
                <div class="product-detail-media">
                    <img id="previewProductImage" src="" alt="Producto" class="product-detail-image">
                </div>
                <div class="product-detail-body">
                    <div class="product-detail-title-row">
                        <div>
                            <p class="product-detail-kicker">SKU <span id="previewProductSku"></span></p>
                            <h2 id="previewProductName"></h2>
                        </div>
                        <span class="status-pill active" id="previewProductStatus"></span>
                    </div>
                    <div class="badge-grid">
                        <span class="chip">categoria: <strong id="previewProductcategory"></strong></span>
                        <span class="chip">Stock: <strong id="previewProductStock"></strong> uds</span>
                    </div>

                    <div class="product-detail-section">
                        <h4>Precios</h4>
                        <div class="product-detail-grid">
                            <div class="product-detail-field">
                                <span>Publico sugerido</span>
                                <strong>Bs <span id="previewProductPublic"></span></strong>
                            </div>
                            <div class="product-detail-field">
                                <span>Institucional</span>
                                <strong>Bs <span id="previewProductInstitutional"></span></strong>
                            </div>
                        </div>
                    </div>

                    <div class="product-detail-section">
                        <h4>Inventario</h4>
                        <div class="product-detail-grid">
                            <div class="product-detail-field">
                                <span>Stock minimo</span>
                                <strong><span id="previewProductMin"></span> uds</strong>
                            </div>
                            <div class="product-detail-field">
                                <span>Stock maximo</span>
                                <strong><span id="previewProductMax"></span> uds</strong>
                            </div>
                        </div>
                    </div>

                    <div class="product-detail-section">
                        <h4>Descripcion</h4>
                        <p id="previewProductDescription" class="product-detail-description"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const productEditModal = document.getElementById('productEditModal');
    const productEditForm = document.getElementById('productEditForm');
    const productEditClose = document.getElementById('closeProductEdit');
    const productEditcancel = document.getElementById('cancelProductEdit');
    const productUpdateUrl = productEditForm.dataset.baseAction;

    document.querySelectorAll('.btn-edit-product').forEach((button) => {
        button.addEventListener('click', () => {
            productEditForm.action = productUpdateUrl.replace('__product__', button.dataset.productId);
            document.getElementById('edit_category_id').value = button.dataset.productCategory || '';
            document.getElementById('edit_name').value = button.dataset.productName || '';
            document.getElementById('edit_sku').value = button.dataset.productSku || '';
            document.getElementById('edit_image').value = '';
            document.getElementById('edit_suggested_price_public').value = button.dataset.productPublic || '';
            document.getElementById('edit_price_institutional').value = button.dataset.productInstitutional || '';
            document.getElementById('edit_min_quantity').value = button.dataset.productMin || 0;
            document.getElementById('edit_max_quantity').value = button.dataset.productMax || '';
            document.getElementById('edit_description').value = button.dataset.productDescription || '';
            document.getElementById('edit_is_active').value = button.dataset.productActive === '0' ? '0' : '1';

            productEditModal.classList.add('active');
        });
    });

    function closeEditModal() {
        productEditModal.classList.remove('active');
    }

    productEditClose.addEventListener('click', closeEditModal);
    productEditcancel.addEventListener('click', closeEditModal);
    window.addEventListener('click', (event) => {
        if (event.target === productEditModal) {
            closeEditModal();
        }
    });

    const productViewModal = document.getElementById('productViewModal');
    const productViewClose = document.getElementById('closeProductView');
    document.querySelectorAll('.btn-view-product').forEach((button) => {
        button.addEventListener('click', () => {
            document.getElementById('previewProductImage').src = button.dataset.productImage || 'https://placehold.co/400x400?text=Producto';
            document.getElementById('previewProductName').textContent = button.dataset.productName || '';
            document.getElementById('previewProductSku').textContent = button.dataset.productSku || '';
            document.getElementById('previewProductcategory').textContent = button.dataset.productCategory || '';
            document.getElementById('previewProductPublic').textContent = button.dataset.productPublic || '0.00';
            document.getElementById('previewProductInstitutional').textContent = button.dataset.productInstitutional || '0.00';
            const previewStatus = document.getElementById('previewProductStatus');
            previewStatus.textContent = button.dataset.productStatus || '';
            previewStatus.className = `status-pill ${(button.dataset.productStatus || '').toLowerCase() === 'activo' ? 'active' : 'inactive'}`;
            document.getElementById('previewProductMin').textContent = button.dataset.productMin || '0';
            document.getElementById('previewProductMax').textContent = button.dataset.productMax || '0';
            document.getElementById('previewProductStock').textContent = button.dataset.productStockTotal || '0';
            document.getElementById('previewProductDescription').textContent = button.dataset.productDescription || 'Sin descripcion';

            productViewModal.classList.add('active');
        });
    });

    function closeViewModal() {
        productViewModal.classList.remove('active');
    }

    productViewClose.addEventListener('click', closeViewModal);
    window.addEventListener('click', (event) => {
        if (event.target === productViewModal) {
            closeViewModal();
        }
    });
</script>
@endpush
