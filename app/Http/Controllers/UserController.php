<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

/**
 * Controlador para gestionar las operaciones de los usuarios
 */
class UserController extends Controller
{
    /**
     * Número de registros por página para la paginación
     *
     * @var int
     */
    public $totalRecord = 5;

    /**
     * Muestra una lista de los usuarios
     *
     * Permite mostrar todos los usuarios o filtrar por rol y estado activo.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Verifica si el usuario tiene permiso para leer
        if (!Gate::allows('Leer')) {
            abort(403);
        }

        $showAll = $request->input('show_all', false);  // Parámetro para mostrar todos los usuarios
        $selectedRole = $request->input('role', null);  // Filtro por rol seleccionado

        // Define la consulta base para los usuarios, con o sin filtro de estado activo
        $usersQuery = $showAll ? User::query() : User::where('is_active', true);
   
        // Aplica filtro por rol si se selecciona uno
        if ($selectedRole) {
            $usersQuery->whereHas('roles', function ($query) use ($selectedRole) {
                $query->where('roles.id', $selectedRole);
            });
        }

        // Realiza la paginación de los usuarios
        $users = $usersQuery->paginate($this->totalRecord)->appends($request->query());

        // Obtiene los roles activos para mostrarlos en la vista
        $roles = Role::where('is_active', true)->get();

        // Devuelve la vista con los usuarios y roles
        return view('users.index', compact('users', 'roles', 'showAll', 'selectedRole'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario
     *
     * Obtiene los roles activos para asignar al nuevo usuario.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Obtiene los roles activos
        $roles = Role::where('is_active', true)->get();

        // Devuelve la vista de creación de usuario
        return view('users.create', compact('roles'));
    }

    /**
     * Guarda un nuevo usuario en la base de datos
     *
     * Valida los datos de entrada y crea el usuario con los roles asignados.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Valida los datos del formulario
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            // Crea un nuevo usuario
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Asigna los roles seleccionados al usuario
            $user->roles()->sync($request->input('roles'));

            // Redirige a la lista de usuarios con mensaje de éxito
            return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            // Maneja errores de creación
            Log::error('Error al crear el usuario: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ocurrió un error al crear el usuario.']);
        }
    }

    /**
     * Muestra el formulario para editar un usuario existente
     *
     * Obtiene los datos del usuario y los roles disponibles para asignar.
     *
     * @param string $id
     * @return \Illuminate\View\View
     */
    public function edit(string $id)
    {
        // Encuentra el usuario por su ID
        $users = User::findOrFail($id);

        // Obtiene los roles activos
        $roles = Role::where('is_active', true)->get();

        // Devuelve la vista de edición con el usuario y los roles
        return view('users.edit', compact('roles', 'users'));
    }

    /**
     * Actualiza los datos de un usuario existente
     *
     * Valida los datos de entrada, actualiza el usuario y asigna los nuevos roles si es necesario.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, string $id)
    {
        try {
            // Valida los datos de entrada
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255']
            ]);

            // Encuentra el usuario y actualiza sus datos
            $user = User::findOrFail($id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            // Sincroniza los roles seleccionados
            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            } else {
                // Elimina todos los roles si no se seleccionan
                $user->roles()->detach();
            }

            // Redirige con mensaje de éxito
            return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            // Maneja errores de actualización y registra el error
            Log::error('Error al actualizar el usuario: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el usuario']);
        }
    }

    /**
     * Elimina un usuario de la base de datos (lo desactiva)
     *
     * Cambia el estado del usuario a inactivo y elimina sus roles asignados.
     *
     * @param \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(User $user)
    {
        try {
            // Marca al usuario como inactivo
            $user->is_active = false;
            $user->save();

            // Elimina los roles asociados
            $user->roles()->detach();

            // Redirige con mensaje de éxito
            return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            // Maneja errores de eliminación y registra el error
            Log::error('Error al eliminar el usuario: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el usuario']);
        }
    }
}
