<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Controlador para gestionar las acciones relacionadas con los permisos.
 */
class PermissionController extends Controller
{
    /**
     * Número de registros por página en la paginación.
     *
     * @var int
     */
    public $totalRecord = 5;

    /**
     * Retorna la vista principal de los permisos.
     *
     * Recupera los permisos activos de la base de datos y los muestra en una vista paginada.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtiene los permisos activos y los pagina
        $permissions = Permission::where('is_active', true)->paginate($this->totalRecord);

        // Retorna la vista con los permisos
        return view('permissions.index', compact('permissions'));
    }

    /**
     * Muestra el formulario para crear un nuevo permiso.
     *
     * Esta función está vacía porque no se necesita lógica adicional para mostrar la vista.
     */
    public function create()
    {
        //
    }

    /**
     * Realiza la creación de un permiso en la base de datos.
     *
     * Valida los datos del permiso y lo guarda en la base de datos. Si ocurre un error, se registra en los logs.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Valida el nombre del permiso
            $request->validate([
                'name' => 'required|unique:permissions'
            ], [
                'name.unique' => 'El nombre del permiso ya existe.'
            ]);

            // Crea el nuevo permiso
            Permission::create($request->all());

            // Redirige con éxito
            return redirect()->route('permissions.index')->with('success', 'Permiso creado correctamente');
        } catch (\Exception $e) {
            // Maneja el error y lo guarda en los logs
            Log::error('Error al crear el permiso: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al crear el permiso']);
        }
    }

    /**
     * Muestra los detalles de un permiso específico.
     *
     * Esta función está vacía porque no se necesita lógica adicional para mostrar los detalles.
     *
     * @param string $id
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Muestra el formulario para editar un permiso existente.
     *
     * Recupera un permiso específico de la base de datos y lo pasa a la vista para su edición.
     *
     * @param \App\Models\Permission $permission
     * @return \Illuminate\View\View
     */
    public function edit(Permission $permission)
    {
        // Devuelve la vista de edición con el permiso
        return view('permissions.edit', compact('permission'));
    }

    /**
     * Actualiza los datos de un permiso existente.
     *
     * Valida los datos del permiso y actualiza los registros correspondientes en la base de datos. 
     * Si ocurre un error, se registra en los logs.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Permission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Permission $permission)
    {
        try {
            // Valida el nombre del permiso, asegurándose de que no sea duplicado, exceptuando el permiso actual
            $request->validate([
                'name' => 'required|unique:permissions,name,' . $permission->id
            ]);

            // Actualiza el permiso con los nuevos datos
            $permission->update($request->all());
            return redirect()->route('permissions.index')->with('success', 'Permiso actualizado correctamente');
        } catch (\Exception $e) {
            // Maneja el error y lo guarda en los logs
            Log::error('Error al actualizar el permiso: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el permiso']);
        }
    }

    /**
     * Inactiva un permiso, marcándolo como no activo.
     *
     * Cambia el estado de "is_active" del permiso a falso y lo guarda en la base de datos.
     *
     * @param \App\Models\Permission $permission
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Permission $permission)
    {
        try {
            // Marca el permiso como inactivo
            $permission->is_active = false;
            $permission->save();

            // Redirige con éxito
            return redirect()->route('permissions.index')->with('success', 'Permiso eliminado correctamente');
        } catch (\Exception $e) {
            // Maneja el error y lo guarda en los logs
            Log::error('Error al eliminar el permiso: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el permiso']);
        }
    }
}
