<div x-data="{
        show: false,
        message: '',
        type: 'success',
        showToast(msg, t) {
            this.message = msg;
            this.type = t;
            this.show = true;
            setTimeout(() => {
                this.show = false;
            }, 5000);
        }
    }"
    @notify.window="showToast($event.detail.message, $event.detail.type)"
    x-show="show"
    x-cloak
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
    x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed top-4 right-4 z-50 flex max-w-sm w-full bg-white shadow-lg rounded-xl pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden"
>
    <div class="p-4 w-full flex items-start gap-3">
        <div class="flex-shrink-0">
            <template x-if="type === 'success'">
                <i class="ph-fill ph-check-circle text-2xl text-green-500"></i>
            </template>
            <template x-if="type === 'error'">
                <i class="ph-fill ph-warning-circle text-2xl text-red-500"></i>
            </template>
        </div>
        <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium text-gray-900" x-text="type === 'success' ? 'Éxito' : 'Atención'"></p>
            <p class="mt-1 text-sm text-gray-500" x-text="message"></p>
        </div>
        <div class="ml-4 flex-shrink-0 flex">
            <button @click="show = false" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                <span class="sr-only">Cerrar</span>
                <i class="ph ph-x text-lg"></i>
            </button>
        </div>
    </div>
    <!-- Progress bar -->
    <div class="absolute bottom-0 left-0 h-1 bg-green-500 animate-[shrink_5s_linear_forwards]" :class="type === 'success' ? 'bg-green-500' : 'bg-red-500'"></div>
</div>

@if(session('success'))
    <script>
        document.addEventListener('alpine:init', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: @json(session('success')), type: 'success' } }));
            }, 100);
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('alpine:init', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: @json(session('error')), type: 'error' } }));
            }, 100);
        });
    </script>
@endif

@if(session('status'))
    <script>
        document.addEventListener('alpine:init', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: @json(session('status')), type: 'success' } }));
            }, 100);
        });
    </script>
@endif

@if($errors->any())
    <script>
        document.addEventListener('alpine:init', () => {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('notify', { detail: { message: @json($errors->first()), type: 'error' } }));
            }, 100);
        });
    </script>
@endif

<style>
    @keyframes shrink {
        from { width: 100%; }
        to { width: 0%; }
    }
</style>