<?php

namespace App\Http\Controllers;

use App\Models\Patrol;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class PatrolQrController extends Controller
{
    /**
     * Generate QR code token for a new patrol
     */
    public function generateToken(): JsonResponse
    {
        $token = Str::random(32);
        
        return response()->json([
            'token' => $token,
            'scan_url' => route('filament.admin.resources.patrols.scan-qr') . '?token=' . $token,
        ]);
    }

    /**
     * Validate patrol via QR code scan
     */
    public function validateQrScan(string $token): JsonResponse
    {
        // Use eager loading to prevent N+1 queries
        $patrol = Patrol::with(['user', 'location', 'shift'])
            ->where('qr_code_token', $token)
            ->first();

        if (!$patrol) {
            return response()->json([
                'success' => false,
                'message' => 'Token QR code tidak valid atau tidak ditemukan',
            ], 404);
        }

        if ($patrol->isValidated()) {
            return response()->json([
                'success' => false,
                'message' => "Patrol sudah ter-validasi pada {$patrol->qr_scanned_at->format('d/m/Y H:i')}",
                'already_validated' => true,
            ], 409);
        }

        if ($patrol->validateWithQrCode($token, request()->ip())) {
            return response()->json([
                'success' => true,
                'message' => 'Patrol berhasil di-validasi',
                'patrol' => [
                    'id' => $patrol->id,
                    'user_name' => $patrol->user->name,
                    'location_name' => $patrol->location->name,
                    'shift_name' => $patrol->shift->name ?? 'N/A',
                    'patrol_time' => $patrol->patrol_time->format('d/m/Y H:i:s'),
                    'qr_scanned_at' => $patrol->qr_scanned_at->format('d/m/Y H:i:s'),
                    'qr_scanned_ip' => $patrol->qr_scanned_ip,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal memvalidasi patrol. Token tidak sesuai.',
        ], 400);
    }
}
