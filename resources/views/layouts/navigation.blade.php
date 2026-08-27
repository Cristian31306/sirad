<aside class="w-64 bg-sirad-dark text-white flex flex-col justify-between hidden md:flex transition-all duration-300">
    <div>
        <!-- Logo Area -->
        <div class="h-20 flex items-center px-6 border-b border-gray-700/50">
            <div class="flex items-center gap-3">
                <!-- Icono representativo de SIRAD -->
                <div class="bg-blue-600 p-2 rounded-lg text-white shadow-lg shadow-blue-500/30">
                    <i class="ph ph-envelope-open text-xl"></i>
                </div>
                <span class="text-2xl font-bold tracking-wider">SIRAD</span>
            </div>
        </div>

        <!-- Navigation Links -->
        <nav class="mt-8 px-4 space-y-2">
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                <i class="ph {{ request()->routeIs('dashboard') ? 'ph-squares-four' : 'ph-squares-four' }} text-xl"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('radicados.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('radicados.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                <i class="ph ph-files text-xl"></i>
                <span>Radicados</span>
            </a>



            @if(\Illuminate\Support\Facades\Gate::any(['admin', 'usuarios.gestionar', 'responsables.gestionar', 'tipos_tramites.gestionar', 'solicitudes.gestionar', 'auditoria.ver']))
                <div class="pt-4 pb-2">
                    <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Administración</p>
                </div>
                
                @can('usuarios.gestionar')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                    <i class="ph ph-user-gear text-xl"></i>
                    <span>Usuarios</span>
                </a>
                @endcan

                @can('responsables.gestionar')
                <a href="{{ route('responsables.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('responsables.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                    <i class="ph ph-users text-xl"></i>
                    <span>Responsables</span>
                </a>
                @endcan

                @can('tipos_tramites.gestionar')
                <a href="{{ route('tipos-tramites.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('tipos-tramites.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                    <i class="ph ph-file-text text-xl"></i>
                    <span>Tipos de Trámite</span>
                </a>
                @endcan

                @can('solicitudes.gestionar')
                <a href="{{ route('solicitudes.index') }}" class="flex items-center justify-between px-4 py-3 rounded-xl transition-all duration-200 {{ request()->routeIs('solicitudes.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30 font-semibold' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
                    <div class="flex items-center gap-3">
                        <i class="ph ph-git-pull-request text-xl"></i>
                        <span>Solicitudes</span>
                    </div>
                    @php
                        $pendingSolicitudes = \App\Models\SolicitudEdicion::where('estado', 'pendiente')->count();
                    @endphp
                    @if($pendingSolicitudes > 0)
                        <span class="bg-blue-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingSolicitudes }}</span>
                    @endif
                </a>
                @endcan

            @endif
        </nav>
    </div>

    <!-- User Profile Bottom -->
    <div class="p-4 border-t border-gray-700/50">
        <div class="flex items-center justify-between px-2 py-2 rounded-xl bg-gray-800/50">
            <div class="flex items-center gap-3 overflow-hidden">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0D8ABC&color=fff" class="w-10 h-10 rounded-full border-2 border-gray-600" alt="Avatar">
                <div class="truncate">
                    <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ Auth::user()->role }}</p>
                </div>
            </div>
            <!-- Logout Icon -->
            <form method="POST" action="{{ route('logout') }}" class="flex items-center">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-red-400 transition" title="Cerrar sesión">
                    <i class="ph ph-sign-out text-xl"></i>
                </button>
            </form>
        </div>
    </div>
</aside>

<!-- Mobile Navigation Button (visible only on small screens) -->
<div class="md:hidden fixed bottom-4 right-4 z-50">
    <button class="bg-blue-600 text-white p-4 rounded-full shadow-lg">
        <i class="ph ph-list text-2xl"></i>
    </button>
</div>
