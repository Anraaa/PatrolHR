<?php

namespace App\Filament\Admin\Resources\PatrolResource\Pages;

use App\Events\PatrolQrScanned;
use App\Filament\Admin\Resources\PatrolResource;
use App\Models\Patrol;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

class ScanQrCode extends Page
{
    protected static string $resource = PatrolResource::class;
    protected static string $view = 'filament.admin.resources.patrol-resource.pages.scan-qr-code';
    protected static ?string $title = 'Scan QR Code Patrol';
    protected static ?string $navigationIcon = 'heroicon-o-qrcode';

    public ?string $qrToken = null;
    public ?Patrol $scannedPatrol = null;
    public ?string $statusMessage = null;
    public ?string $statusType = null;

    public function mount(): void
    {
        // Get token dari URL if available
        $token = request()->query('token');
        if ($token) {
            $this->qrToken = $token;
            $this->handleQrScan();
        }
    }

    public function handleQrScan(): void
    {
        if (empty($this->qrToken)) {
            $this->statusType = 'error';
            $this->statusMessage = 'Token QR code tidak ditemukan';
            return;
        }

        // Find patrol dengan qr_code_token yang sesuai
        $patrol = Patrol::where('qr_code_token', $this->qrToken)->first();

        if (!$patrol) {
            $this->statusType = 'error';
            $this->statusMessage = 'Token QR code tidak valid atau tidak ditemukan';
            $this->qrToken = null;
            return;
        }

        // Cek apakah sudah di-scan sebelumnya
        if ($patrol->isValidated()) {
            $this->statusType = 'warning';
            $this->statusMessage = "Patrol sudah ter-validasi pada {$patrol->qr_scanned_at->format('d/m/Y H:i')}";
            $this->scannedPatrol = $patrol;
            return;
        }

        // Validate with QR code
        if ($patrol->validateWithQrCode($this->qrToken, request()->ip())) {
            $this->statusType = 'success';
            $this->statusMessage = "✓ Patrol berhasil di-validasi! User: {$patrol->user->name}, Lokasi: {$patrol->location->name}";
            $this->scannedPatrol = $patrol;

            // Emit event untuk dashboard update
            PatrolQrScanned::dispatch($patrol);
            $this->dispatch('patrolQrScanned', patrolId: $patrol->id);

            Notification::make()
                ->title('Validasi QR Code Berhasil')
                ->body("Patrol oleh {$patrol->user->name} di {$patrol->location->name} telah ter-validasi")
                ->success()
                ->send();
        } else {
            $this->statusType = 'error';
            $this->statusMessage = 'Gagal memvalidasi patrol. Token tidak sesuai.';
        }

        $this->qrToken = null;
    }

    protected function getFormActions(): array
    {
        return [];
    }
}
