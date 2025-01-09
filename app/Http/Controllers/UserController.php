<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Si tiene rol de lectura puede ver el listado de usuarios
        if(Gate::allows('Lectura')){
            $users = User::where('is_active', true)->get();
            $roles = Role::where('is_active', true)->get();
            return view('users.index', compact('users', 'roles'));
        }else{
            abort(403);
        }
    }

    /**
     * Mostrar el formulario de creación
     */
    public function create()
    {
        $roles = Role::where('is_active', true)->get();
        return view('users.create', compact('roles'));
    }

    /**
     * Realizar el guardado del usuario
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->roles()->sync($request->input('roles')); // Asignar roles

            return redirect()->route('users.index')->with('success', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            // Manejar el error
            return back()->withErrors(['error' => 'Ocurrió un error al crear el usuario.']);
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $users = User::findOrFail($id);
        $roles = Role::where('is_active', true)->get();
        return view('users.edit', compact('roles', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validar los datos
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255']
            ]);

            // Obtener el usuario y actualizar
            $user = User::findOrFail($id);
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->filled('password') ? Hash::make($request->password) : $user->password,
            ]);

            // Sincronizar los roles seleccionados
            if ($request->has('roles')) {
                $user->roles()->sync($request->roles);
            } else {
                // Si no se seleccionó ningún rol, elimina todos los roles relacionados
                $user->roles()->detach();
            }

            return redirect()->route('users.index')->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al actualizar el usuario: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al actualizar el usuario']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            $user->is_active = false;
            $user->save();
            // eliminar roles asociados
            $user->roles()->detach();

            return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente');
        } catch (\Exception $e) {
            Log::error('Error al eliminar el usuario: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al eliminar el usuario']);
        }
    }
}
