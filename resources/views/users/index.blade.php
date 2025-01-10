<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Usuarios') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if ($errors->any())
                        <div class="bg-red-500 text-white font-bold rounded-lg p-4 my-4">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="bg-green-500 text-white font-bold rounded-lg p-4 my-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @can('Crear', $users)
                        <div class="p-6 text-gray-900">
                            <a href="{{ route('users.create') }}" class="text-blue-600 hover:text-blue-900">CREAR NUEVO</a>
                        </div>
                    @endcan
                    <div class="flex items-center mb-4">
                        <select name="roles" id="roles" class="mr-4" onchange="filterRoles()">
                            <option value="">-- Todos los Roles --</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @if ($selectedRole == $role->id) selected @endif>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="showAllUsers" name="show_all" class="mr-2"
                            @if ($showAll) checked @endif onchange="toggleShowAll()" />
                        <label for="showAllUsers">Mostrar todos los usuarios</label>
                    </div>

                    <div class="relative overflow-x-auto" id="tableUsers">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead
                                class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        Id
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Nombre
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Correo
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Fecha creación
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Roles
                                    </th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-4">
                                            {{ $user->id }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $user->name }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $user->created_at }}
                                        </td>
                                        <td class="px-6 py-4">
                                            {{ $user->roles->pluck('name')->join(', ') }}
                                        </td>
                                        <td class="px-6 py-4">
                                            @can('Editar', $user)
                                                <a href="{{ route('users.edit', $user->id) }}"
                                                    class="text-blue-600 hover:text-blue-900">Editar</a>
                                            @endcan
                                            @can('Eliminar', $user)
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                    class="inline"
                                                    onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="text-red-600 hover:text-red-900">Eliminar</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $users->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        /*
         * Se encarga de realizar el filtro por roles.
         * */
        function filterRoles() {
            const roleId = document.getElementById('roles').value;
            // Se valida si tiene el filtro de mostrar todos los usuarios.
            const showAll = document.getElementById('showAllUsers')?.checked || false;
            const url = new URL(window.location.href);

            if (roleId) {
                url.searchParams.set('role', roleId);
            } else {
                url.searchParams.delete('role');
            }
            if (showAll) {
                url.searchParams.set('show_all', '1');
            }

            window.location.href = url.toString();
        }

        /**
         * Evento encargado de traer todos los usuarios (activos e inactivos)
         * */
        function toggleShowAll() {
            const showAll = document.getElementById('showAllUsers').checked;
            const url = new URL(window.location.href);
            if (showAll) {
                url.searchParams.set('show_all', '1');
            } else {
                url.searchParams.delete('show_all');
            }
            window.location.href = url.toString();
        }
    </script>
</x-app-layout>
