<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * Mostrar los roles
     */
    public function index()
    {
        // Solo mostrar roles activos
        $roles = Role::where('is_active', 1)->with('permissions')->get();
        return view('roles.index', compact('roles'));
    }

    /**
     * Mostrar el formulario de creación.
     */
    public function create()
    {
        $permissions = Permission::where('is_active', true)->get();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Función encargada de realizar la creación del rol
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ], [
                'name.unique' => 'El nombre del rol ya existe.',
                'name.required' => 'El nombre es obligatorio'
            ]);

            // Crear el rol
            $role = Role::create([
                'name' => $request->name,
            ]);

            // Asignar los permisos al rol
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            }
            return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
        } catch (\Exception $e) {
            // Manejar el error
            Log::error('Error al crear el rol: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Muestra el formulario de edición
     */
    public function edit(Role $role)
    {
        $role = Role::findOrFail($role->id);
        $permissions = Permission::where('is_active', 1)->get();
        return view('roles.edit', compact('role', 'permissions'));
    }

    /**
     * Se encarga de actualizar el registro
     */
    public function update(Request $request, $id)
    {
        try {
            // Validar los datos
            $request->validate([
                'name' => 'required|string|max:255',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ],[
                'name.required' => 'El nombre es obligatorio'
            ]);

            // Obtener el rol
            $role = Role::findOrFail($id);
            $role->update($request->all());

            // Sincronizar los permisos seleccionados
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            } else {
                // Si no se seleccionó ningún permiso, elimina todos los permisos relacionados
                $role->permissions()->detach();
            }

            return redirect()->route('roles.index')->with('success', 'Rol actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el rol: ' . $e->getMessage());
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Inactivar el rol
     */
    public function destroy(Role $role)
    {
        try {
            $role->is_active = false;
            $role->save();
            // eliminar permisos asociados al rol
            $role->permissions()->detach();

            return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el rol: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el rol']);
        }
    }
}
