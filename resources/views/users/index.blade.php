<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
                    Gestión de Usuarios
                </h2>
                <p class="text-gray-500 text-sm mt-1">Administra los accesos y permisos del sistema.</p>
            </div>
            <a href="{{ route('users.create') }}" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-xl shadow-md shadow-blue-500/30 transition-all">
                <i class="ph ph-plus-circle text-lg"></i>
                Nuevo Usuario
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto">
        <!-- Buscador -->
        <div class="mb-6 bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
            <form action="{{ route('users.index') }}" method="GET" class="flex gap-4">
                <div class="flex-1 relative">
                    <i class="ph ph-magnifying-glass absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o correo..." class="w-full pl-11 pr-4 py-2 border border-gray-200 rounded-xl focus:border-blue-500 focus:ring-blue-500 bg-gray-50 transition">
                </div>
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-6 py-2 rounded-xl font-medium transition-colors">
                    Buscar
                </button>
            </form>
        </div>

        <div class="bg-white rounded-b-2xl shadow-sm border border-gray-100 border-t-0 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 text-xs text-gray-500 uppercase tracking-wider border-b border-gray-100">
                            <th class="px-6 py-4 font-semibold">Usuario</th>
                            <th class="px-6 py-4 font-semibold">Rol</th>
                            <th class="px-6 py-4 font-semibold">Permisos</th>
                            <th class="px-6 py-4 font-semibold text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-sm text-gray-700">
                        @forelse($users as $user)
                            <tr class="hover:bg-blue-50/30 transition">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-900">{{ $user->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->isAdmin())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200">
                                            <i class="ph-fill ph-crown"></i> Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <i class="ph-fill ph-user"></i> Usuario
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->isAdmin())
                                        <span class="text-gray-500 text-xs italic">Acceso Total</span>
                                    @else
                                        <span class="text-gray-700 font-medium">{{ is_array($user->permisos) ? count($user->permisos) : 0 }} permisos</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.edit', $user) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Editar">
                                            <i class="ph ph-pencil-simple text-lg"></i>
                                        </a>
                                        @if(auth()->id() !== $user->id)
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Estás seguro de eliminar este usuario?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                                    <i class="ph ph-trash text-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-400">
                                    <i class="ph ph-users text-4xl mb-2 block"></i>
                                    No se encontraron usuarios.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
