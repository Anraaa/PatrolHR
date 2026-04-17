<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @php $statePath = $getStatePath(); $state = $getState(); @endphp

    <div
        x-data="{
            value: @entangle($statePath),
            drawing: false,
            ctx: null,
            canvas: null,

            init() {
                this.canvas = this.$refs.canvas;
                this.ctx    = this.canvas.getContext('2d');
                this.resizeCanvas();

                if (this.value && this.value.startsWith('data:image')) {
                    const img = new Image();
                    img.onload = () => {
                        this.ctx.drawImage(img, 0, 0, this.canvas.width, this.canvas.height);
                    };
                    img.src = this.value;
                }

                window.addEventListener('resize', () => this.resizeCanvas());
            },

            resizeCanvas() {
                const dpr  = window.devicePixelRatio || 1;
                const rect = this.canvas.getBoundingClientRect();

                this.canvas.width  = rect.width  * dpr;
                this.canvas.height = rect.height * dpr;

                this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                this.setupCtx();
            },

            setupCtx() {
                this.ctx.strokeStyle = '#1e293b';
                this.ctx.lineWidth   = 2.5;
                this.ctx.lineCap     = 'round';
                this.ctx.lineJoin    = 'round';
            },

            getPos(e) {
    const rect = this.canvas.getBoundingClientRect();
    const src  = e.touches ? e.touches[0] : e;
    
    const scaleX = this.canvas.width  / rect.width;
    const scaleY = this.canvas.height / rect.height;
    
    return {
        x: (src.clientX - rect.left) * scaleX / (window.devicePixelRatio || 1),
        y: (src.clientY - rect.top)  * scaleY / (window.devicePixelRatio || 1)
    };
},

            startDraw(e) {
                e.preventDefault();
                this.drawing = true;
                const p = this.getPos(e);
                this.ctx.beginPath();
                this.ctx.moveTo(p.x, p.y);
            },

            draw(e) {
                if (!this.drawing) return;
                e.preventDefault();
                const p = this.getPos(e);
                this.ctx.lineTo(p.x, p.y);
                this.ctx.stroke();
            },

            endDraw() {
                if (!this.drawing) return;
                this.drawing = false;
                this.ctx.closePath();
                this.value = this.canvas.toDataURL('image/png');
            },

            clear() {
                const dpr  = window.devicePixelRatio || 1;
                this.ctx.clearRect(0, 0, this.canvas.width / dpr, this.canvas.height / dpr);
                this.value = null;
            }
        }"
        class="space-y-2"
    >
        <div class="relative rounded-xl border-2 border-dashed border-gray-300 bg-white dark:border-gray-600 dark:bg-gray-950"
             style="height: 180px; touch-action: none; cursor: crosshair;">

            <canvas
                x-ref="canvas"
                style="width: 100%; height: 100%; border-radius: 0.75rem; display: block;"
                @pointerdown="startDraw($event)"
                @pointermove="draw($event)"
                @pointerup="endDraw()"
                @pointerleave="endDraw()"
                @touchstart.prevent="startDraw($event)"
                @touchmove.prevent="draw($event)"
                @touchend.prevent="endDraw()"
            ></canvas>

            <p x-show="!value"
               x-cloak
               class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center gap-1 text-sm text-gray-400 dark:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>
                <span>✍️ Tanda tangan di sini</span>
            </p>
        </div>

        <div class="flex items-center gap-3">
            <button type="button"
                x-on:click="clear()"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-red-300 hover:bg-red-50 hover:text-red-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:text-red-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a2 2 0 012-2h4a2 2 0 012 2v2"/>
                </svg>
                Hapus
            </button>
            <span x-show="value" x-cloak class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Tanda tangan tersimpan
            </span>
        </div>
    </div>
</x-dynamic-component>