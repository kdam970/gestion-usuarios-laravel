<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    /**
     * Retorna la vista principal de los permisos
     */
    public function index()
    {
        $permissions = Permission::where('is_active', true)->get();
        return view('permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Función encargada de realizar la creación del permiso
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|unique:permissions'
            ], [
                'name.unique' => 'El nombre del permiso ya existe.'
            ]);
            Permission::create($request->all());

            return redirect()->route('permissions.index')->with('success', 'Permiso creado correctamente');
        } catch (\Exception $e) {
            // Manejar el error
            Log::error('Error al crear el permiso: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear el permiso']);
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
     * Retorna vista de edición
     */
    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    /**
     * Actualiza el permiso.
     */
    public function update(Request $request, Permission $permission)
    {
        try {
            $request->validate([
                'name' => 'required|unique:permissions,name,' . $permission->id
            ]);

            $permission->update($request->all());

            return redirect()->route('permissions.index')->with('success', 'Permiso actualizado correctamente');
        } catch (\Exception $e) {
            // Manejar el error
            Log::error('Error al actualizar el permiso: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el permiso']);
        }
    }

    /**
     * Inactiva el permiso
     */
    public function destroy(Permission $permission)
    {
        try {
            $permission->is_active = false;
            $permission->save();

            return redirect()->route('permissions.index')->with('success', 'Permiso eliminado correctamente');
        } catch (\Exception $e) {
            // Manejar el error
            Log::error('Error al eliminar el permiso: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el permiso']);
        }
    }
}
