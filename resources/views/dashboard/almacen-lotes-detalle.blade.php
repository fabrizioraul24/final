@extends('layouts.sidebar-almacen')

@section('title', 'Detalle de inventario | Pil Andina')
@section('page-title', 'Detalle de inventario')

@php
    $currentStock = (int) ($product->current_stock ?? 0);
    $minimumStock = (int) ($product->min_quantity ?? 0);
    $isCritical = $minimumStock > 0 && $currentStock <= $minimumStock;
    $nextExpiry = $product->next_expiry ? \Carbon\Carbon::parse($product->next_expiry)->format('d/m/Y') : 'Sin fecha';
    $lotsCount = (int) ($product->lots_count ?? 0);
@endphp

@section('content')
<div class="warehouse-inventory-page warehouse-lot-detail-page">
    <section class="warehouse-lot-detail-hero">
        <div class="warehouse-lot-detail-hero-main">
            <a href="{{ route('dashboard.almacen.lots', ['warehouse_id' => $filters['warehouse_id'] ?? null]) }}" class="fit-outline-button compact warehouse-lot-detail-back">
                <i class="ri-arrow-left-line"></i>
                <span>Volver</span>
            </a>
            <div class="warehouse-lot-detail-head">
                <img src="{{ $product->getImageUrl() }}" alt="{{ $product->name }}">
                <div>
                    <span>{{ $product->category->name ?? 'Sin categoria' }}</span>
                    <h2>{{ $product->name }}</h2>
                    <p>SKU: {{ $product->sku }}</p>
                    <div class="warehouse-inventory-tags">
                        <span>{{ $lotsCount }} lote{{ $lotsCount === 1 ? '' : 's' }}</span>
                        <span>{{ $warehouse?->name ?? 'La Paz' }}</span>
                        @if($isCritical)
                            <span class="danger">Stock critico</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="warehouse-inventory-branch">
            <span>Bodega activa</span>
            <strong>{{ $warehouse?->name ?? 'La Paz' }}</strong>
        </div>
    </section>

    <section class="warehouse-inventory-stats warehouse-lot-detail-stats">
        <div>
            <span>Stock actual</span>
            <strong>{{ number_format($currentStock) }}</strong>
        </div>
        <div>
            <span>Stock minimo</span>
            <strong>{{ $minimumStock ?: 'N/D' }}</strong>
        </div>
        <div>
            <span>Total lotes</span>
            <strong>{{ $lotsCount }}</strong>
        </div>
        <div>
            <span>Proximo venc.</span>
            <strong>{{ $nextExpiry }}</strong>
        </div>
    </section>

    @if($isCritical)
        <div class="warehouse-inventory-alert">
            <i class="ri-error-warning-line"></i>
            <span>Producto en estado critico por stock minimo. Revisa reposicion o traspaso desde bodega.</span>
        </div>
    @endif

    <div class="warehouse-lot-detail-layout">
        <section class="warehouse-lot-detail-main">
            <div class="warehouse-lot-panel">
                <h4>Historial de lotes</h4>
                <div class="fit-table-scroll">
                    <table class="fit-users-table warehouse-lot-table">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Stock</th>
                                <th>Bodega</th>
                                <th>Vence</th>
                                <th>Ultimo movimiento</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($product->history_rows as $row)
                                <tr>
                                    <td><code class="fit-code">{{ $row['code'] }}</code></td>
                                    <td><strong>{{ number_format($row['quantity']) }}</strong></td>
                                    <td><span class="fit-muted-text">{{ $row['warehouse'] }}</span></td>
                                    <td><span class="fit-muted-text">{{ $row['expires_at'] }}</span></td>
                                    <td>
                                        <span class="fit-muted-text">
                                            {{ $row['last_movement'] }}
                                            @if($row['last_movement_qty'] !== null)
                                                {{ (int) $row['last_movement_qty'] > 0 ? '+' : '' }}{{ $row['last_movement_qty'] }}
                                            @endif
                                        </span>
                                    </td>
                                    <td><span class="fit-muted-text">{{ $row['last_movement_user'] ?? 'Sistema' }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="warehouse-empty-cell">Sin lotes registrados para este producto.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="warehouse-lot-panel">
                <h4>Descripcion</h4>
                <p>{{ $product->description ?: 'Sin descripcion registrada.' }}</p>
            </div>
        </section>

        <aside class="warehouse-lot-detail-side">
            <div class="warehouse-lot-panel">
                <h4>Movimientos recientes</h4>
                <div class="warehouse-lot-movement-list">
                    @forelse($product->movement_history as $item)
                        <div>
                            <strong>{{ $item['type'] }} {{ $item['quantity'] > 0 ? '+' : '' }}{{ $item['quantity'] }}</strong>
                            <span>Lote: {{ $item['lot_code'] }}</span>
                            <p>{{ $item['note'] }}</p>
                            <small>{{ $item['user'] }} - {{ $item['date'] }}</small>
                        </div>
                    @empty
                        <p class="fit-muted-text">Sin movimientos recientes.</p>
                    @endforelse
                </div>
            </div>

            <div class="warehouse-lot-panel">
                <h4>Lectura rapida</h4>
                <div class="warehouse-lot-summary-list">
                    <div>
                        <span>Estado</span>
                        <strong>{{ $isCritical ? 'Requiere atencion' : 'Operativo' }}</strong>
                    </div>
                    <div>
                        <span>Cobertura</span>
                        <strong>{{ $minimumStock > 0 ? number_format(($currentStock / max($minimumStock, 1)) * 100, 0) . '%' : 'N/D' }}</strong>
                    </div>
                    <div>
                        <span>Vencimiento cercano</span>
                        <strong>{{ $product->next_expiry && \Carbon\Carbon::parse($product->next_expiry)->between(now(), now()->addDays(30)) ? 'Si' : 'No' }}</strong>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
