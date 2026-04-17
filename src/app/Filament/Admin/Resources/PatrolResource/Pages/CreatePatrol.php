<?php

namespace App\Filament\Admin\Resources\PatrolResource\Pages;

use App\Filament\Admin\Resources\PatrolResource;
use App\Models\Location;
use App\Models\PatrolCheckpoint;
use App\Models\Shift;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;

class CreatePatrol extends CreateRecord
{
    protected static string $resource = PatrolResource::class;

    protected ?string $scannedLocationId = null;

    public bool $viaScan = false;

    // ── Checkpoint data — semua nullable string ────────────────────────────
    public ?int    $checkpointLocationId   = null;
    public ?string $checkpointUuid         = null;
    public ?string $checkpointFacePhotoB64 = null;
    public ?string $checkpointSignature    = null;
    public bool    $checkpointCompleted    = false;

    // ── Livewire v3: gunakan #[On] attribute, bukan getListeners() ──────────
    #[On('checkpointLocationSet')]
    public function onCheckpointLocationSet(
        ?int    $locationId = null,
        ?string $uuid       = null,
        ?string $locationName = null,
    ): void {
        $this->checkpointLocationId = $locationId;
        $this->checkpointUuid       = $uuid;

        if ($this->checkpointLocationId) {
            try {
                $this->form->fill([
                    ...$this->form->getState(),
                    'location_id' => $this->checkpointLocationId,
                ]);
            } catch (\Throwable) {}
        }
    }

    #[On('checkpointDataCollected')]
    public function onCheckpointDataCollected(
        ?int    $locationId       = null,
        ?string $uuid             = null,
        ?string $locationName     = null,
        ?string $facePhotoBase64  = null,
        ?string $signatureDataUrl = null,
    ): void {
        $this->checkpointLocationId   = $locationId   ?? $this->checkpointLocationId;
        $this->checkpointUuid         = $uuid         ?? $this->checkpointUuid;
        $this->checkpointFacePhotoB64 = $facePhotoBase64;
        $this->checkpointSignature    = $signatureDataUrl;
        $this->checkpointCompleted    = true;

        Notification::make()
            ->title('Checkpoint Terekam')
            ->body('📍 Foto muka & tanda tangan berhasil disimpan. Klik "Simpan Laporan" untuk menyelesaikan.')
            ->success()
            ->send();
    }

    public function authorizeAccess(): void
    {
        if ($this->viaScan) {
            abort_unless(auth()->check(), 403);
            return;
        }

        $uuid = request()->query('loc');

        if ($uuid && Location::where('uuid', $uuid)->exists()) {
            abort_unless(auth()->check(), 403);
            $this->viaScan = true;
            return;
        }

        parent::authorizeAccess();
    }

    public function mount(): void
    {
        parent::mount();

        $uuid = request()->query('loc');

        if ($uuid) {
            $location = Location::where('uuid', $uuid)->first();

            if ($location) {
                if ($location->latitude !== null && $location->longitude !== null) {
                    $tokenKey   = 'geo_verified_' . $uuid;
                    $verifiedAt = session($tokenKey);
                    $expiry     = now()->subMinutes(5)->timestamp;

                    if (! $verifiedAt || $verifiedAt < $expiry) {
                        session()->forget($tokenKey);
                        $this->redirect('/admin/patrols/scan/' . urlencode($uuid));
                        return;
                    }

                    session()->forget($tokenKey);
                }

                $this->scannedLocationId = $location->id;

                $this->form->fill(['location_id' => $location->id]);

                Notification::make()
                    ->title('Lokasi Terdeteksi dari QR Code')
                    ->body("📍 {$location->name} — Lokasi sudah terisi otomatis.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('QR Code Tidak Valid')
                    ->body('Lokasi dengan kode QR tersebut tidak ditemukan.')
                    ->danger()
                    ->send();
            }
        }
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['patrol_time'] = now();
        $data['user_id']     = auth()->id();
        $data['shift_id']    = $this->resolveShiftId();

        if ($this->checkpointLocationId && empty($data['location_id'])) {
            $data['location_id'] = $this->checkpointLocationId;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->checkpointCompleted || ! $this->checkpointLocationId) {
            return;
        }

        $facePhotoPath = null;
        $signaturePath = null;

        if ($this->checkpointFacePhotoB64 && str_starts_with($this->checkpointFacePhotoB64, 'data:')) {
            try {
                [$meta, $b64] = explode(',', $this->checkpointFacePhotoB64, 2);
                $ext  = str_contains($meta, 'jpeg') ? 'jpg' : 'png';
                $path = 'checkpoint-face-photos/' . uniqid('face_') . '.' . $ext;
                Storage::disk('public')->put($path, base64_decode($b64));
                $facePhotoPath = $path;
            } catch (\Throwable) {}
        }

        if ($this->checkpointSignature && str_starts_with($this->checkpointSignature, 'data:')) {
            try {
                [, $b64] = explode(',', $this->checkpointSignature, 2);
                $path = 'checkpoint-signatures/' . uniqid('sig_') . '.png';
                Storage::disk('public')->put($path, base64_decode($b64));
                $signaturePath = $path;
            } catch (\Throwable) {}
        }

        PatrolCheckpoint::create([
            'patrol_id'   => $this->record->id,
            'location_id' => $this->checkpointLocationId,
            'user_id'     => auth()->id(),
            'face_photo'  => $facePhotoPath,
            'signature'   => $signaturePath,
            'scanned_at'  => now(),
        ]);

        Notification::make()
            ->title('Patroli & Checkpoint Tersimpan!')
            ->body('✅ Laporan patroli dan checkpoint berhasil dicatat.')
            ->success()
            ->send();
    }

    private function resolveShiftId(): int
    {
        $hour = (int) now()->format('G');

        $number = match(true) {
            $hour >= 7  && $hour < 15 => 1,
            $hour >= 15 && $hour < 23 => 2,
            default                   => 3,
        };

        $shift = Shift::where('name', 'like', "Shift {$number}%")->first()
            ?? Shift::orderBy('id')->skip($number - 1)->first()
            ?? Shift::orderBy('id')->first();

        return $shift->id;
    }
}