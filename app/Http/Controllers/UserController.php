<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Filtrar por roles y usuarios inactivos
        /*$query = User::query();

        if ($request->has('role')) {
            $query->whereHas('roles', function ($query) use ($request) {
                $query->where('roles.id', $request->input('role'));
            });
        }

        if ($request->has('status') && $request->input('status') != 'all') {
            $query->where('status', $request->input('status'));
        }
        $users = $query->get();
        $roles = Role::all(); // Todos los roles para el filtro
        */

        if(Gate::allows('Crear')){
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
        $users = User::where('is_active', true)->get();
        $roles = Role::where('is_active', true)->get();
        return view('users.edit', compact('roles', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
