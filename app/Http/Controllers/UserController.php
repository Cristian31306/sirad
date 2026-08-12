<?php

namespace App\Http\Controllers;

use App\Mail\NuevoUsuarioMail;
use App\Models\Auditoria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $query = User::query()->where('email', '!=', 'durancristian31306@gmail.com');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(10);

        return view('users.index', compact('users', 'search'));
    }

    public function create()
    {
        $permisosAgrupados = User::PERMISOS;

        return view('users.create', compact('permisosAgrupados'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'in:admin,usuario'],
            'permisos' => ['nullable', 'array'],
        ]);

        $randomPassword = Str::password(12, true, true, false, false); // 12 chars, letters, numbers, no symbols

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($randomPassword),
            'role' => $request->role,
            'permisos' => $request->role === 'admin' ? [] : ($request->permisos ?? []),
            'must_change_password' => true,
        ]);

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Creó un usuario',
            'modelo' => 'User',
            'modelo_id' => $user->id,
            'detalles' => ['email' => $user->email, 'role' => $user->role],
        ]);

        try {
            Mail::to($user->email)->queue(new NuevoUsuarioMail($user, $randomPassword));
        } catch (\Exception $e) {
            \Log::error('Mail Error New User: '.$e->getMessage());
        }

        return redirect()->route('users.index')->with('success', 'Usuario creado correctamente y credenciales enviadas por correo.');
    }

    public function edit(User $user)
    {
        $permisosAgrupados = User::PERMISOS;

        return view('users.edit', compact('user', 'permisosAgrupados'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,usuario'],
            'permisos' => ['nullable', 'array'],
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->filled('password')) {
            // Un admin no puede cambiar la contraseña de otro admin
            if ($user->isAdmin() && auth()->id() !== $user->id) {
                return back()->withErrors(['password' => 'No tienes permiso para cambiar la contraseña de otro administrador.'])->withInput();
            }
            $user->password = Hash::make($request->password);
        }

        $user->permisos = $request->role === 'admin' ? [] : ($request->permisos ?? []);
        $user->save();

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Editó un usuario',
            'modelo' => 'User',
            'modelo_id' => $user->id,
            'detalles' => ['email' => $user->email, 'role' => $user->role],
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $detalles = ['email' => $user->email, 'role' => $user->role];
        $user->delete();

        Auditoria::create([
            'user_id' => Auth::id(),
            'accion' => 'Eliminó un usuario',
            'modelo' => 'User',
            'modelo_id' => $user->id,
            'detalles' => $detalles,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
