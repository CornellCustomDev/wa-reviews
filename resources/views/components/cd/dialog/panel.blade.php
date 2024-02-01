<div
    x-dialog
    x-model="dialogOpen"
    style="display: none; 
        position:fixed; 
        top: 0; right: 0; bottom: 0; left: 0;
        overflow-y:auto;
        z-index: 10"
    {{-- tailwindclass="fixed inset-0 overflow-y-auto z-10" --}}
>
    <div>In panel, open is <span x-text="open"></span></div>
    <!-- Overlay -->
    <div x-dialog:overlay x-transition.opacity 
    style="position:fixed; 
       top: 0; right: 0; bottom: 0; left: 0;
       --bg-opacity: 1;
       background-color: #000;
       background-color: rgba(0, 0, 0, .25);"
    {{-- tailwindclass="fixed inset-0 bg-black/25" --}}
    
    ></div>

    <!-- Panel -->
    <div 
    {{-- tailwindclass="relative min-h-screen flex items-center justify-center p-4" --}}
    style="position:relative; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1rem;"
    >
        <div
            x-dialog:panel
            x-transition.in x-transition.out.opacity
            {{-- tailwindclass="relative max-w-2xl w-full bg-white rounded-xl shadow-lg overflow-y-auto" --}}
            style="position: relative; max-width: 42rem; width: 100%; 
                --bg-opacity: 1; background-color: #fff; background-color: rgba(255,255,255,var(--bg-opacity));
                border-radius: 0.75rem; 
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                overflow-y: auto;"
        >
            <!-- Close Button -->
            <div 
                {{-- tailwindclass="absolute top-0 right-0 pt-4 pr-4" --}}
                style="position:absolute; top: 0; right: 0; padding-top: 1rem; padding-right: 1rem;"
            >
                <button type="button" @click="$dialog.close()" 
                    {{-- tailwindclass="bg-gray-50 rounded-lg p-2 text-gray-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2" --}}
                    style="--bg-opacity: 1;
                            background-color: #a0aec0;
                            background-color: rgba(160, 174, 192, var(--bg-opacity));
                            border-radius: 0.5rem;
                            padding: 0.5rem;
                            --text-opacity: 1;
                            color: #718096;
                            color: rgba(113, 128, 150, var(--text-opacity));"
                    >
                    <span class="sr-only">Close modal</span>
                    <svg xmlns="http://www.w3.org/2000/svg" tailwindclass="h-4 w-4" style="height: 1rem; width: 1 rem;" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Panel -->
            <div 
                {{-- tailwindclass="p-8" --}}
                style="padding: 2rem;"
            >
                {{ $slot }}
            </div>
        </div>
    </div>
</div>