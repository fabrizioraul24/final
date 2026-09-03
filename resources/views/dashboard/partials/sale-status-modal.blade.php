<div class="modal" id="saleStatusModal" style="display:none;">
    <div class="modal-content" style="max-width:520px;">
        <div class="modal-header">
            <h3>Actualizar venta</h3>
            <button type="button" class="close-button close-modal">&times;</button>
        </div>
        <form method="POST" id="saleStatusForm">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group">
                    <label for="modal_status">Estado</label>
                    <select id="modal_status" name="status" class="select-light" required>
                        @foreach($statusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1rem;">
                <button type="button" class="btn-secondary close-modal">Cancelar</button>
                <button type="submit" class="pill-button">Guardar</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const modal = document.getElementById('saleStatusModal');
    if (!modal) return;

    const form = document.getElementById('saleStatusForm');
    const statusField = document.getElementById('modal_status');

    document.querySelectorAll('.btn-sale-update').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (form) {
                form.action = btn.dataset.updateUrl || '#';
            }
            if (statusField) statusField.value = btn.dataset.status || '';
            modal.style.display = 'flex';
        });
    });

    modal.querySelectorAll('.close-modal').forEach((btn) => {
        btn.addEventListener('click', () => modal.style.display = 'none');
    });

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

})();
</script>
@endpush
