{{--
    QR Checkpoint Step — digunakan di Step 3 Wizard Patrol (Create saja).
    Menggunakan Alpine.js + Livewire dispatch untuk komunikasi ke CreatePatrol.
    Sub-state: qr → gps → photo → sign → done
--}}
<div
    x-data="qrCheckpoint()"
    x-init="init()"
    class="w-full"
>
    {{-- ── Library html5-qrcode ────────────────────────────────────────────── --}}
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js" defer></script>

    {{-- ── Container Card ──────────────────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900 space-y-5">

        {{-- Progress stepper --}}
        <div class="flex items-center justify-between text-xs font-medium">
            <template x-for="(s, idx) in steps" :key="idx">
                <div class="flex items-center gap-1">
                    <div
                        class="flex h-7 w-7 items-center justify-center rounded-full text-white text-xs font-bold transition-all"
                        :class="{
                            'bg-primary-600': currentStepIndex === idx,
                            'bg-success-500': currentStepIndex > idx,
                            'bg-gray-200 dark:bg-gray-700 !text-gray-400': currentStepIndex < idx
                        }"
                        x-text="currentStepIndex > idx ? '✓' : (idx + 1)"
                    ></div>
                    <span
                        class="hidden sm:inline transition-colors"
                        :class="{
                            'text-primary-600 dark:text-primary-400': currentStepIndex === idx,
                            'text-success-600 dark:text-success-400': currentStepIndex > idx,
                            'text-gray-400': currentStepIndex < idx
                        }"
                        x-text="s.label"
                    ></span>
                    <div x-show="idx < steps.length - 1" class="h-px w-6 sm:w-10 bg-gray-200 dark:bg-gray-700 mx-1"></div>
                </div>
            </template>
        </div>

        {{-- ── STATE: QR Scanner ────────────────────────────────────────────── --}}
        <div x-show="state === 'qr'" x-cloak>
            <div class="text-center mb-4">
                <div class="text-3xl mb-2">📷</div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Scan QR Code Lokasi</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Arahkan kamera ke QR code yang ada di titik pos patroli</p>
            </div>

            {{-- Camera preview area --}}
            <div id="qr-reader" class="rounded-xl overflow-hidden border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 w-full" style="min-height: 260px;"></div>

            <div x-show="qrError" class="mt-3 rounded-lg bg-red-50 dark:bg-red-950/40 border border-red-200 dark:border-red-800 px-4 py-3 text-sm text-red-700 dark:text-red-300" x-text="qrError"></div>

            <div class="mt-4 flex gap-3">
                <button
                    type="button"
                    @click="startScanner()"
                    x-show="!scannerRunning"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-primary-500 transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Buka Kamera & Scan QR
                </button>
                <button
                    type="button"
                    @click="stopScanner()"
                    x-show="scannerRunning"
                    class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-300 transition-colors"
                >
                    Berhenti Scan
                </button>
            </div>
        </div>

        {{-- ── STATE: GPS Verification ──────────────────────────────────────── --}}
        <div x-show="state === 'gps'" x-cloak class="text-center py-6">
            <div class="text-4xl mb-3" x-text="gpsIcon">📍</div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="gpsTitle">Verifikasi Lokasi</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2 max-w-xs mx-auto" x-text="gpsMsg">Meminta akses GPS…</p>

            {{-- Spinner --}}
            <div x-show="gpsLoading" class="mt-4 flex justify-center">
                <svg class="animate-spin h-8 w-8 text-primary-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                </svg>
            </div>

            <div class="mt-4 flex justify-center gap-3 flex-wrap">
                <button type="button" x-show="gpsRetry" @click="verifyGPS()" class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-500 transition-colors">
                    Coba Lagi
                </button>
                <button type="button" x-show="gpsRetry" @click="state = 'qr'; resetScanner()" class="inline-flex items-center gap-2 rounded-lg bg-gray-200 dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-300 transition-colors">
                    Scan Ulang QR
                </button>
            </div>
        </div>

        {{-- ── STATE: Foto Muka ─────────────────────────────────────────────── --}}
        <div x-show="state === 'photo'" x-cloak>
            <div class="text-center mb-4">
                <div class="text-3xl mb-2">🤳</div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Foto Muka Petugas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Lokasi: <span class="font-semibold text-primary-600 dark:text-primary-400" x-text="locationName"></span>
                </p>
                <p class="text-xs text-gray-400 mt-1">Ambil selfie sebagai bukti kehadiran di pos patroli ini</p>
            </div>

            {{-- File input (camera) --}}
            <input type="file" id="cp-face-input" accept="image/*" capture="user" class="hidden" @change="onFacePhoto($event)">

            <div
                @click="document.getElementById('cp-face-input').click()"
                class="cursor-pointer flex flex-col items-center justify-center gap-3 rounded-xl border-2 border-dashed transition-colors p-6"
                :class="facePhotoPreview ? 'border-success-400 bg-success-50 dark:bg-success-950/20' : 'border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800 hover:border-primary-400'"
            >
                <template x-if="!facePhotoPreview">
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">Tap untuk buka kamera depan</p>
                    </div>
                </template>
                <template x-if="facePhotoPreview">
                    <div class="text-center">
                        <img :src="facePhotoPreview" alt="Foto muka" class="h-36 w-36 rounded-full object-cover border-4 border-success-400 mx-auto shadow-lg">
                        <p class="text-xs text-success-600 dark:text-success-400 mt-2 font-medium">✅ Foto berhasil — tap untuk ganti</p>
                    </div>
                </template>
            </div>

            <div x-show="photoError" class="mt-2 text-sm text-red-600 dark:text-red-400" x-text="photoError"></div>

            <button
                type="button"
                @click="proceedToSign()"
                x-show="facePhotoPreview"
                class="mt-4 w-full inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-primary-500 transition-colors"
            >
                Lanjut ke Tanda Tangan →
            </button>
        </div>

        {{-- ── STATE: Tanda Tangan ──────────────────────────────────────────── --}}
        <div x-show="state === 'sign'" x-cloak>
            <div class="text-center mb-4">
                <div class="text-3xl mb-2">✍️</div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tanda Tangan Petugas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tanda tangani di kotak di bawah ini</p>
            </div>

            <div class="relative rounded-xl border-2 border-gray-300 dark:border-gray-600 overflow-hidden bg-white" style="touch-action: none;">
                <canvas
                    id="cp-sig-canvas"
                    class="w-full block"
                    style="height: 200px; cursor: crosshair; touch-action: none;"
                    @pointerdown="sigStart($event)"
                    @pointermove="sigMove($event)"
                    @pointerup="sigEnd($event)"
                    @pointercancel="sigEnd($event)"
                ></canvas>
                <div x-show="!hasSignature" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <p class="text-gray-300 dark:text-gray-600 text-sm select-none">Tanda tangan di sini…</p>
                </div>
            </div>

            <div class="mt-2 flex justify-between items-center">
                <span x-show="hasSignature" class="text-xs text-success-600 dark:text-success-400 font-medium">✅ Tanda tangan terekam</span>
                <span x-show="!hasSignature" class="text-xs text-gray-400">Belum ada tanda tangan</span>
                <button type="button" @click="clearSignature()" class="text-xs text-gray-500 hover:text-red-500 border border-gray-200 dark:border-gray-700 rounded px-2 py-1 transition-colors">
                    Hapus
                </button>
            </div>

            <div x-show="signError" class="mt-2 text-sm text-red-600 dark:text-red-400" x-text="signError"></div>

            <div class="mt-4 flex gap-3">
                <button type="button" @click="state = 'photo'" class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-gray-100 dark:bg-gray-700 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-200 transition-colors">
                    ← Kembali
                </button>
                <button type="button" @click="saveCheckpoint()" class="flex-1 inline-flex items-center justify-center gap-2 rounded-lg bg-success-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-success-500 transition-colors">
                    <template x-if="!saving">
                        <span>✅ Selesai & Simpan</span>
                    </template>
                    <template x-if="saving">
                        <span class="flex items-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg>
                            Menyimpan…
                        </span>
                    </template>
                </button>
            </div>
        </div>

        {{-- ── STATE: Done ──────────────────────────────────────────────────── --}}
        <div x-show="state === 'done'" x-cloak class="text-center py-6">
            <div class="text-5xl mb-4">✅</div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Checkpoint Berhasil!</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                Lokasi <span class="font-semibold text-success-600 dark:text-success-400" x-text="locationName"></span> tercatat.
            </p>
            <p class="text-xs text-gray-400 mt-1">Klik <strong>Simpan Laporan Patroli</strong> di bawah untuk menyelesaikan.</p>

            <div class="mt-4 inline-flex items-center gap-2 rounded-lg bg-success-50 dark:bg-success-950/30 border border-success-200 dark:border-success-800 px-4 py-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-success-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium text-success-700 dark:text-success-300">Absensi foto & tanda tangan tersimpan</span>
            </div>
        </div>

    </div>
</div>

<script>
function qrCheckpoint() {
    return {
        // ── State machine ─────────────────────────────
        state: 'qr',   // qr | gps | photo | sign | done
        steps: [
            { label: 'Scan QR' },
            { label: 'Verifikasi GPS' },
            { label: 'Foto Muka' },
            { label: 'Tanda Tangan' },
        ],
        get currentStepIndex() {
            return { qr: 0, gps: 1, photo: 2, sign: 3, done: 4 }[this.state] ?? 0;
        },

        // ── QR Scanner ────────────────────────────────
        scanner: null,
        scannerRunning: false,
        qrError: '',
        scannedUuid: '',

        // ── GPS ───────────────────────────────────────
        gpsIcon: '📍',
        gpsTitle: 'Verifikasi Lokasi',
        gpsMsg: 'Meminta akses GPS…',
        gpsLoading: true,
        gpsRetry: false,
        locationName: '',
        locationId: null,

        // ── Photo ─────────────────────────────────────
        facePhotoBase64: '',
        facePhotoPreview: '',
        faceFileObject: null,
        photoError: '',

        // ── Signature ─────────────────────────────────
        sigCanvas: null,
        sigCtx: null,
        sigDrawing: false,
        hasSignature: false,
        signatureDataUrl: '',
        signError: '',
        saving: false,

        // ─────────────────────────────────────────────
        init() {
            this.$nextTick(() => {
                this.initSignatureCanvas();
            });
        },

        // ── QR methods ────────────────────────────────
        startScanner() {
            this.qrError = '';
            if (!window.Html5Qrcode) {
                this.qrError = 'Library QR belum termuat. Refresh halaman dan coba lagi.';
                return;
            }
            this.scanner = new Html5Qrcode('qr-reader');
            const config = { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };

            this.scanner.start(
                { facingMode: 'environment' },
                config,
                (decodedText) => this.onQrSuccess(decodedText),
                () => {}  // suppress per-frame errors
            ).then(() => {
                this.scannerRunning = true;
            }).catch(err => {
                this.qrError = 'Tidak dapat mengakses kamera: ' + err;
            });
        },

        stopScanner() {
            if (this.scanner && this.scannerRunning) {
                this.scanner.stop().then(() => {
                    this.scannerRunning = false;
                }).catch(() => {});
            }
        },

        resetScanner() {
            this.stopScanner();
            this.scanner = null;
            this.scannedUuid = '';
            this.qrError = '';
        },

        onQrSuccess(text) {
            // Extract UUID from URL or use directly
            let uuid = text.trim();
            const match = text.match(/\/scan\/([a-f0-9\-]{36})/i)
                        || text.match(/loc=([a-f0-9\-]{36})/i)
                        || text.match(/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/i);
            if (match) uuid = match[1];

            if (!uuid || !uuid.match(/^[a-f0-9\-]{36}$/i)) {
                this.qrError = 'QR code tidak valid. Pastikan scan QR code lokasi patroli yang benar.';
                return;
            }

            this.scannedUuid = uuid;
            this.stopScanner();
            this.state = 'gps';
            this.$nextTick(() => this.verifyGPS());
        },

        // ── GPS methods ───────────────────────────────
        async verifyGPS() {
            this.gpsLoading = true;
            this.gpsRetry = false;
            this.gpsIcon = '📍';
            this.gpsTitle = 'Verifikasi Lokasi';
            this.gpsMsg = 'Memeriksa konfigurasi lokasi…';

            try {
                // Step 1: check if GPS is configured for this location
                const cfgResp = await fetch('/admin/patrols/geo-verify/' + encodeURIComponent(this.scannedUuid), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!cfgResp.ok) {
                    this.gpsBlocked('Server error (' + cfgResp.status + '). Hubungi administrator.');
                    return;
                }

                const cfgData = await cfgResp.json();

                // Set location info
                this.locationName = cfgData.location ?? '';
                this.locationId = cfgData.location_id ?? null;

                if (!cfgData.geo_configured) {
                    // No GPS required — skip to photo
                    this.gpsIcon = '✅';
                    this.gpsTitle = 'Lokasi Terdeteksi';
                    this.gpsMsg = 'Lokasi: ' + this.locationName + '. Lanjut ke foto muka…';
                    this.gpsLoading = false;
                    setTimeout(() => this.state = 'photo', 1000);
                    // Notify Livewire
                    this.notifyLocationScanned();
                    return;
                }

                // Step 2: GPS required — request position
                this.gpsMsg = 'Meminta akses GPS, mohon tunggu…';
                if (!navigator.geolocation) {
                    this.gpsBlocked('Browser tidak mendukung GPS.');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        this.gpsMsg = 'Menghitung jarak ke titik patroli…';
                        try {
                            const resp = await fetch(
                                '/admin/patrols/geo-verify/' + encodeURIComponent(this.scannedUuid)
                                + '?lat=' + pos.coords.latitude + '&lng=' + pos.coords.longitude,
                                { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                            );
                            const data = await resp.json();
                            this.locationName = data.location ?? this.locationName;
                            this.locationId = data.location_id ?? this.locationId;

                            if (data.allowed) {
                                this.gpsIcon = '✅';
                                this.gpsTitle = 'Lokasi Terverifikasi';
                                this.gpsMsg = this.locationName + ' — Berhasil!';
                                this.gpsLoading = false;
                                this.notifyLocationScanned();
                                setTimeout(() => this.state = 'photo', 1000);
                            } else {
                                this.gpsBlocked(
                                    'Anda berada ' + data.distance + ' m dari "' + data.location
                                    + '". Harus dalam radius ' + data.radius + ' m.'
                                );
                            }
                        } catch(e) {
                            this.gpsBlocked('Koneksi terputus. Coba lagi.');
                        }
                    },
                    (err) => {
                        const msgs = {1:'Izinkan akses lokasi di browser lalu tekan Coba Lagi.', 2:'GPS tidak tersedia.', 3:'GPS timeout.'};
                        this.gpsBlocked(msgs[err.code] ?? 'GPS error: ' + err.message);
                    },
                    { timeout: 15000, maximumAge: 0, enableHighAccuracy: true }
                );
            } catch(e) {
                this.gpsBlocked('Tidak dapat menghubungi server. Periksa koneksi internet.');
            }
        },

        gpsBlocked(msg) {
            this.gpsIcon = '❌';
            this.gpsTitle = 'Gagal Verifikasi';
            this.gpsMsg = msg;
            this.gpsLoading = false;
            this.gpsRetry = true;
        },

        notifyLocationScanned() {
            // Kirim ke Livewire (CreatePatrol) via dispatch
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('checkpointLocationSet', {
                    uuid: this.scannedUuid,
                    locationId: this.locationId,
                    locationName: this.locationName,
                });
            }
        },

        // ── Photo methods ─────────────────────────────
        onFacePhoto(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.photoError = '';
            this.faceFileObject = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.facePhotoPreview = e.target.result;
                this.facePhotoBase64 = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        proceedToSign() {
            if (!this.facePhotoBase64) {
                this.photoError = 'Foto muka wajib diambil terlebih dahulu.';
                return;
            }
            this.state = 'sign';
            this.$nextTick(() => this.initSignatureCanvas());
        },

        // ── Signature methods ─────────────────────────
        initSignatureCanvas() {
            const el = document.getElementById('cp-sig-canvas');
            if (!el) return;
            this.sigCanvas = el;
            this.sigCtx = el.getContext('2d');
            // Set canvas resolution to element's display size
            const rect = el.getBoundingClientRect();
            el.width  = rect.width  || 600;
            el.height = rect.height || 200;
            this.sigCtx.strokeStyle = '#1e1b4b';
            this.sigCtx.lineWidth   = 2.5;
            this.sigCtx.lineCap     = 'round';
            this.sigCtx.lineJoin    = 'round';
        },

        getCanvasPos(e) {
            const r = this.sigCanvas.getBoundingClientRect();
            const sx = this.sigCanvas.width  / r.width;
            const sy = this.sigCanvas.height / r.height;
            const cx = e.touches ? e.touches[0].clientX : e.clientX;
            const cy = e.touches ? e.touches[0].clientY : e.clientY;
            return [(cx - r.left) * sx, (cy - r.top) * sy];
        },

        sigStart(e) {
            if (!this.sigCanvas) this.initSignatureCanvas();
            this.sigDrawing = true;
            this.sigCtx.beginPath();
            this.sigCtx.moveTo(...this.getCanvasPos(e));
            this.sigCanvas.setPointerCapture(e.pointerId);
        },

        sigMove(e) {
            if (!this.sigDrawing) return;
            this.sigCtx.lineTo(...this.getCanvasPos(e));
            this.sigCtx.stroke();
        },

        sigEnd(e) {
            this.sigDrawing = false;
            this.hasSignature = true;
            this.signatureDataUrl = this.sigCanvas.toDataURL('image/png');
        },

        clearSignature() {
            if (!this.sigCanvas) return;
            this.sigCtx.clearRect(0, 0, this.sigCanvas.width, this.sigCanvas.height);
            this.hasSignature = false;
            this.signatureDataUrl = '';
        },

        // ── Save checkpoint ───────────────────────────
        async saveCheckpoint() {
            if (!this.hasSignature || !this.signatureDataUrl) {
                this.signError = 'Tanda tangan wajib diisi sebelum melanjutkan.';
                return;
            }
            this.signError = '';
            this.saving = true;

            // Notify Livewire with all checkpoint data
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('checkpointDataCollected', {
                    uuid: this.scannedUuid,
                    locationId: this.locationId,
                    locationName: this.locationName,
                    facePhotoBase64: this.facePhotoBase64,
                    signatureDataUrl: this.signatureDataUrl,
                });
            }

            // Also set hidden Filament form fields if they exist
            ['checkpoint_location_id', 'checkpoint_uuid', 'checkpoint_face_photo_b64', 'checkpoint_signature'].forEach(field => {
                const el = document.querySelector('[wire\\:model*="' + field + '"], [name*="' + field + '"]');
                if (el) el.value = this[field] ?? '';
            });

            this.saving = false;
            this.state = 'done';
        },
    };
}
</script>
