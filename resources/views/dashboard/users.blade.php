@extends('layouts.sidebar')

@section('title', 'Gestion de Usuarios | Pil Andina')
@section('page-title', 'Gestion de Usuarios')

@section('content')
    @if(session('status'))
        <div class="card">
            <span class="chip text-white/90">{{ session('status') }}</span>
        </div>
    @endif

    <div class="card">
        <div class="chart-head">
            <h4>Crear nuevo usuario</h4>
        </div>
        <form method="POST" action="{{ route('dashboard.users.store') }}" class="form-grid">
            @csrf
            <div class="form-group">
                <label for="name">Nombre completo</label>
                <input type="text" id="name" name="name" class="input-ghost" value="" required>
                @error('name')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="email">Correo electronico</label>
                <input type="email" id="email" name="email" class="input-ghost" value="" required>
                @error('email')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="username">Nombre de usuario</label>
                <input type="text" id="username" name="username" class="input-ghost" value="" required>
                @error('username')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="password">contrasena</label>
                <input type="password" id="password" name="password" class="input-ghost" required>
                @error('password')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group">
                <label for="role_create">Rol</label>
                <select id="role_create" name="role_id" class="select-light" required>
                    <option value="">Selecciona un rol</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" >{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id')<small style="color:#f87171">{{ $message }}</small>@enderror
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <button type="submit" class="pill-button">Guardar usuario</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Filtros inteligentes</h4>
            <a class="pill-button" target="_blank" rel="noopener" data-live-report-link href="{{ route('dashboard.users.report', ['search' => $search, 'role_id' => $roleFilter]) }}">
                <i class="ri-file-chart-line mr-1"></i> Generar reporte PDF
            </a>
        </div>
        <form method="GET" class="form-grid" action="{{ route('dashboard.users') }}" data-live-search-form>
            <div class="form-group">
                <label for="search">Buscar por nombre, email o usuario</label>
                <input type="text" id="search" name="search" class="input-ghost" placeholder="Ej. camila o camila@pil.com" value="{{ $search }}" data-live-search-input>
            </div>
            <div class="form-group">
                <label for="role_id">Filtrar por rol</label>
                <select id="role_id" name="role_id" class="select-light">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" @selected($roleFilter == $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="align-self:flex-end;">
                <a href="{{ route('dashboard.users') }}" class="clean-link">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Usuarios activos</h4>
            <span class="chip">{{ $activeUsers->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Creado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->role->name ?? 'Sin rol' }}</td>
                            <td><span class="status-pill active">Activo</span></td>
                            <td>{{ $user->created_at?->format('d/m/Y') }}</td>
                            <td>
                                <div class="actions">
                                    <button class="btn-secondary btn-edit-user"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-username="{{ $user->username }}"
                                        data-user-role="{{ $user->role_id }}">
                                        Editar
                                    </button>
                                    <form method="POST" action="{{ route('dashboard.users.toggle', $user->id) }}">
                                        @csrf
                                        @method('PaTCH')
                                        <button type="submit" class="btn-danger">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:1.5rem;">No hay usuarios activos para los filtros aplicados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $activeUsers->appends(['search' => $search, 'role_id' => $roleFilter])->links() }}
        </div>
    </div>

    <div class="card">
        <div class="chart-head">
            <h4>Usuarios inactivos</h4>
            <span class="chip">{{ $inactiveUsers->total() }} registros</span>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Actualizado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inactiveUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->role->name ?? 'Sin rol' }}</td>
                            <td><span class="status-pill inactive">Inactivo</span></td>
                            <td>{{ $user->updated_at?->format('d/m/Y') }}</td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="{{ route('dashboard.users.toggle', $user->id) }}">
                                        @csrf
                                        @method('PaTCH')
                                        <button type="submit" class="btn-secondary">Reactivar</button>
                                    </form>
                                    <form method="POST" action="{{ route('dashboard.users.destroy', $user->id) }}" onsubmit="return confirm('AEliminar definitivamente a {{ $user->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:1.5rem;">Sin usuarios inactivos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;">
            {{ $inactiveUsers->appends(['search' => $search, 'role_id' => $roleFilter])->links() }}
        </div>
    </div>

    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar usuario</h3>
                <button class="close-button" type="button" id="closeEditModal">&times;</button>
            </div>
            <form method="POST" id="userEditForm">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label for="edit_name">Nombre completo</label>
                        <input type="text" id="edit_name" name="name" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Correo electronico</label>
                        <input type="email" id="edit_email" name="email" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_username">Nombre de usuario</label>
                        <input type="text" id="edit_username" name="username" class="input-ghost" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_password">Nueva contrasena (opcional)</label>
                        <input type="password" id="edit_password" name="password" class="input-ghost">
                    </div>
                    <div class="form-group">
                        <label for="edit_role_id">Rol</label>
                        <select id="edit_role_id" name="role_id" class="select-light" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div style="margin-top:1.2rem; display:flex; justify-content:flex-end; gap:0.8rem;">
                    <button type="button" class="btn-secondary" id="cancelEditModal">cancelar</button>
                    <button type="submit" class="pill-button">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('userEditForm');
    const editName = document.getElementById('edit_name');
    const editEmail = document.getElementById('edit_email');
    const editUsername = document.getElementById('edit_username');
    const editPassword = document.getElementById('edit_password');
    const editRole = document.getElementById('edit_role_id');
    const baseUpdateUrl = "{{ route('dashboard.users.update', ['user' => '__user__']) }}";

    document.querySelectorAll('.btn-edit-user').forEach((button) => {
        button.addEventListener('click', () => {
            editForm.action = baseUpdateUrl.replace('__user__', button.dataset.userId);
            editName.value = button.dataset.userName;
            editEmail.value = button.dataset.userEmail;
            editUsername.value = button.dataset.userUsername;
            editPassword.value = '';
            editRole.value = button.dataset.userRole;
            editModal.classList.add('active');
        });
    });

    document.getElementById('closeEditModal').addEventListener('click', () => {
        editModal.classList.remove('active');
    });

    document.getElementById('cancelEditModal').addEventListener('click', () => {
        editModal.classList.remove('active');
    });

    window.addEventListener('click', (event) => {
        if (event.target === editModal) {
            editModal.classList.remove('active');
        }
    });
</script>
@endpush



