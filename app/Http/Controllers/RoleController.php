<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar los roles y sus permisos
 */
class RoleController extends Controller
{
    /**
     * Número de registros por página para la paginación
     *
     * @var int
     */
    public $totalRecord = 5;

    /**
     * Muestra la lista de roles activos
     *
     * Obtiene todos los roles activos y los permisos asociados, paginados.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtiene roles activos y sus permisos asociados
        $roles = Role::where('is_active', 1)->with('permissions')->paginate($this->totalRecord);

        // Devuelve la vista con los roles
        return view('roles.index', compact('roles'));
    }

    /**
     * Muestra el formulario para crear un nuevo rol
     *
     * Obtiene los permisos activos disponibles para asignar al nuevo rol.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Obtiene permisos activos
        $permissions = Permission::where('is_active', true)->get();

        // Devuelve la vista de creación de rol con los permisos
        return view('roles.create', compact('permissions'));
    }

    /**
     * Crea un nuevo rol en la base de datos
     *
     * Valida los datos del formulario y asigna permisos al rol si es necesario.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Valida los datos del formulario
            $request->validate([
                'name' => 'required|string|max:255|unique:roles',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ], [
                'name.unique' => 'El nombre del rol ya existe.',
                'name.required' => 'El nombre es obligatorio'
            ]);

            // Crea un nuevo rol
            $role = Role::create([
                'name' => $request->name,
            ]);

            // Asigna los permisos seleccionados al rol
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            }

            // Redirige a la lista de roles con mensaje de éxito
            return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
        } catch (\Exception $e) {
            // Registra cualquier error
            Log::error('Error al crear el rol: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Muestra los detalles de un rol específico
     *
     * (Este método no está implementado en este caso, pero se deja la estructura)
     *
     * @param string $id
     * @return void
     */
    public function show(string $id)
    {
        // Este método no está implementado en el código actual
    }

    /**
     * Muestra el formulario de edición para un rol existente
     *
     * Obtiene los datos del rol y los permisos disponibles para su asignación.
     *
     * @param \App\Models\Role $role
     * @return \Illuminate\View\View
     */
    public function edit(Role $role)
    {
        // Encuentra el rol por su ID
        $role = Role::findOrFail($role->id);

        // Obtiene los permisos activos
        $permissions = Permission::where('is_active', 1)->get();

        // Devuelve la vista de edición con el rol y los permisos
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Actualiza un rol en la base de datos
     *
     * Valida los datos de entrada, actualiza el rol y sincroniza los permisos seleccionados.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        try {
            // Valida los datos del formulario
            $request->validate([
                'name' => 'required|string|max:255',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ], [
                'name.required' => 'El nombre es obligatorio'
            ]);

            // Obtiene el rol y actualiza sus datos
            $role = Role::findOrFail($id);
            $role->update($request->all());

            // Sincroniza los permisos seleccionados
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            } else {
                // Si no se seleccionan permisos, elimina todos los permisos asociados
                $role->permissions()->detach();
            }

            // Redirige con mensaje de éxito
            return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
        } catch (\Exception $e) {
            // Registra el error y redirige con mensaje de error
            Log::error('Error al actualizar el rol: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Elimina un rol (lo inactiva) y sus permisos asociados
     *
     * Marca el rol como inactivo y elimina todos los permisos relacionados.
     *
     * @param \App\Models\Role $role
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Role $role)
    {
        try {
            // Marca al rol como inactivo
            $role->is_active = false;
            $role->save();

            // Elimina los permisos asociados al rol
            $role->permissions()->detach();

            // Redirige con mensaje de éxito
            return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente');
        } catch (\Exception $e) {
            // Registra el error y redirige con mensaje de error
            Log::error('Error al eliminar el rol: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el rol']);
        }
    }
}
