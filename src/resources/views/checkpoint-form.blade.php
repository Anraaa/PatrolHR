<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkpoint Patroli</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.1);
            padding: 1.75rem 1.5rem;
            max-width: 420px;
            margin: 0 auto;
        }
        .header { text-align: center; margin-bottom: 1.5rem; }
        .icon { font-size: 2.5rem; }
        .location-name { font-size: 1.1rem; font-weight: 700; color: #111827; margin-top: .5rem; }
        .patrol-info { font-size: .8rem; color: #6b7280; margin-top: .25rem; }
        .badge {
            display: inline-block; background: #ede9fe; color: #4f46e5;
            font-size: .75rem; font-weight: 600; padding: .2rem .75rem;
            border-radius: 9999px; margin-top: .5rem;
        }

        .field { margin-bottom: 1.25rem; }
        label { display: block; font-size: .85rem; font-weight: 600; color: #374151; margin-bottom: .5rem; }
        .field-hint { font-size: .75rem; color: #9ca3af; margin-bottom: .5rem; }

        /* File input styling */
        .file-label {
            display: block; border: 2px dashed #d1d5db; border-radius: .75rem;
            padding: 1.5rem; text-align: center; cursor: pointer; color: #6b7280;
            font-size: .85rem; transition: border-color .2s;
        }
        .file-label:hover { border-color: #4f46e5; color: #4f46e5; }
        .file-label .file-icon { font-size: 2rem; display: block; margin-bottom: .5rem; }
        input[type=file] { display: none; }
        #preview-img {
            max-width: 100%; border-radius: .75rem; margin-top: .75rem;
            display: none; border: 2px solid #e5e7eb;
        }

        /* Signature canvas */
        #sig-canvas {
            width: 100%; height: 180px; border: 2px solid #d1d5db;
            border-radius: .75rem; touch-action: none; cursor: crosshair;
            background: #fff;
        }
        #sig-canvas.has-drawing { border-color: #4f46e5; }
        .sig-actions { display: flex; justify-content: flex-end; margin-top: .5rem; }
        .btn-clear {
            background: none; border: 1px solid #d1d5db; color: #6b7280;
            padding: .3rem .75rem; border-radius: .5rem; font-size: .8rem; cursor: pointer;
        }
        .btn-clear:hover { border-color: #ef4444; color: #ef4444; }

        /* Submit */
        .btn-submit {
            width: 100%; background: #4f46e5; color: #fff; border: none;
            padding: .875rem; border-radius: .75rem; font-size: 1rem;
            font-weight: 600; cursor: pointer; margin-top: .5rem;
            transition: background .2s;
        }
        .btn-submit:hover { background: #4338ca; }
        .btn-submit:disabled { background: #a5b4fc; cursor: wait; }

        .error { color: #ef4444; font-size: .8rem; margin-top: .4rem; display: none; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <div class="icon">📍</div>
            <div class="location-name">{{ $location->name }}</div>
            <div class="patrol-info">Patroli: {{ $patrol->patrol_time->format('d/m/Y H:i') }}</div>
            <span class="badge">{{ auth()->user()->name }}</span>
        </div>

        <form method="POST" action="{{ url('/admin/patrols/checkpoint/' . $uuid) }}" enctype="multipart/form-data" id="cpForm">
            @csrf

            <!-- Foto Muka -->
            <div class="field">
                <label>📷 Foto Muka Petugas</label>
                <div class="field-hint">Ambil selfie sebagai bukti kehadiran di pos ini</div>
                <label class="file-label" for="face_photo" id="fileLbl">
                    <span class="file-icon">🤳</span>
                    <span id="fileTxt">Tap untuk buka kamera depan</span>
                </label>
                <input type="file" name="face_photo" id="face_photo" accept="image/*" capture="user" required>
                <img id="preview-img" src="" alt="Preview">
                <div class="error" id="err-photo">Foto muka wajib diambil.</div>
            </div>

            <!-- Tanda Tangan -->
            <div class="field">
                <label>✍️ Tanda Tangan Petugas</label>
                <div class="field-hint">Tanda tangani di kotak di bawah ini</div>
                <canvas id="sig-canvas" width="800" height="360"></canvas>
                <div class="sig-actions">
                    <button type="button" class="btn-clear" onclick="clearSig()">Hapus</button>
                </div>
                <input type="hidden" name="signature" id="sig-input">
                <div class="error" id="err-sig">Tanda tangan wajib diisi.</div>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">✅ Simpan Checkpoint</button>
        </form>
    </div>

    <script>
        // ── Foto preview ────────────────────────────────────────────────────
        const fileInput = document.getElementById('face_photo');
        fileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.getElementById('preview-img');
                    img.src = e.target.result;
                    img.style.display = 'block';
                    document.getElementById('fileTxt').textContent = '✅ Foto berhasil diambil — tap untuk ganti';
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // ── Signature canvas ────────────────────────────────────────────────
        const canvas = document.getElementById('sig-canvas');
        const ctx    = canvas.getContext('2d');
        let drawing  = false;
        let hasSig   = false;

        ctx.strokeStyle = '#1e1b4b';
        ctx.lineWidth   = 2.5;
        ctx.lineCap     = 'round';
        ctx.lineJoin    = 'round';

        function getPos(e) {
            const r = canvas.getBoundingClientRect();
            const scaleX = canvas.width  / r.width;
            const scaleY = canvas.height / r.height;
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return [(clientX - r.left) * scaleX, (clientY - r.top) * scaleY];
        }

        canvas.addEventListener('pointerdown', (e) => {
            drawing = true;
            ctx.beginPath();
            ctx.moveTo(...getPos(e));
            canvas.setPointerCapture(e.pointerId);
        });
        canvas.addEventListener('pointermove', (e) => {
            if (!drawing) return;
            ctx.lineTo(...getPos(e));
            ctx.stroke();
        });
        canvas.addEventListener('pointerup', () => {
            drawing = false;
            hasSig = true;
            canvas.classList.add('has-drawing');
            document.getElementById('sig-input').value = canvas.toDataURL('image/png');
        });

        function clearSig() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            hasSig = false;
            canvas.classList.remove('has-drawing');
            document.getElementById('sig-input').value = '';
        }

        // ── Form validation ─────────────────────────────────────────────────
        document.getElementById('cpForm').addEventListener('submit', function (e) {
            let valid = true;

            if (!fileInput.files || !fileInput.files[0]) {
                document.getElementById('err-photo').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('err-photo').style.display = 'none';
            }

            if (!hasSig) {
                document.getElementById('err-sig').style.display = 'block';
                valid = false;
            } else {
                document.getElementById('err-sig').style.display = 'none';
            }

            if (!valid) { e.preventDefault(); return; }

            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').textContent = 'Menyimpan…';
        });
    </script>
</body>
</html>
