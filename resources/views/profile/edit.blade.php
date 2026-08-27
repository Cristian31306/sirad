<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perfil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            @can('auditoria.ver')
            <div class="p-4 sm:p-8 bg-gray-50 border border-gray-200 shadow-sm sm:rounded-lg">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                            <i class="ph ph-shield-check text-xl text-blue-600"></i>
                            {{ __('Auditoría del Sistema') }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Accede al registro técnico de actividades, movimientos y transacciones que han ocurrido en la plataforma.') }}
                        </p>
                    </header>
                    <div class="mt-6">
                        <a href="{{ route('auditoria.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 gap-2 shadow-lg shadow-blue-500/30">
                            <i class="ph ph-terminal-window text-base"></i> Ver Registros de Auditoría
                        </a>
                    </div>
                </div>
            </div>
            @endcan
        </div>
    </div>
</x-app-layout>
