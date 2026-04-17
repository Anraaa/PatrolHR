<?php

use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

/* NOTE: Do Not Remove
/ Livewire asset handling if using sub folder in domain
*/
Livewire::setUpdateRoute(function ($handle) {
    return Route::post(config('app.asset_prefix') . '/livewire/update', $handle);
});

Livewire::setScriptRoute(function ($handle) {
    return Route::get(config('app.asset_prefix') . '/livewire/livewire.js', $handle);
});
/*
/ END
*/

// QR Code scan — checkpoint mode: petugas isi form dulu, lalu scan QR di setiap pos
Route::get('/admin/patrols/scan/{uuid}', function (string $uuid) {
    if (! auth()->check()) {
        session()->put('url.intended', url('/admin/patrols/scan/' . $uuid));
        return redirect()->to('/admin/login');
    }

    $location = \App\Models\Location::where('uuid', $uuid)->first();

    if (! $location) {
        return view('checkpoint-result', [
            'success' => false,
            'icon'    => '❌',
            'title'   => 'QR Code Tidak Valid',
            'message' => 'Lokasi dengan QR code ini tidak ditemukan.',
        ]);
    }

    // Cek apakah petugas sudah mengisi laporan patroli hari ini
    $patrol = \App\Models\Patrol::where('user_id', auth()->id())
        ->whereDate('patrol_time', today())
        ->latest('patrol_time')
        ->first();

    if (! $patrol) {
        return view('checkpoint-result', [
            'success'     => false,
            'icon'        => '🚫',
            'title'       => 'Belum Ada Laporan Patroli',
            'message'     => 'Anda harus mengisi laporan patroli terlebih dahulu sebelum scan checkpoint.',
            'actionUrl'   => url('/admin/patrols/create'),
            'actionLabel' => '📋 Buat Laporan Sekarang',
        ]);
    }

    // Arahkan ke GPS verify, dengan successUrl ke form checkpoint
    $successUrl = url('/admin/patrols/checkpoint/' . $uuid);
    return view('geo-verify', compact('uuid', 'successUrl'));
})->name('patrol.scan');

// Form checkpoint: foto muka + tanda tangan (GET = tampilkan form)
Route::get('/admin/patrols/checkpoint/{uuid}', function (string $uuid) {
    if (! auth()->check()) {
        session()->put('url.intended', url('/admin/patrols/checkpoint/' . $uuid));
        return redirect()->to('/admin/login');
    }

    $location = \App\Models\Location::where('uuid', $uuid)->first();
    if (! $location) abort(404);

    // Verifikasi GPS session token jika lokasi dikonfigurasi GPS
    if ($location->latitude !== null && $location->longitude !== null) {
        $tokenKey = 'geo_verified_' . $uuid;
        if (! session($tokenKey)) {
            return view('checkpoint-result', [
                'success' => false,
                'icon'    => '📍',
                'title'   => 'Verifikasi GPS Diperlukan',
                'message' => 'Silahkan scan ulang QR code untuk memverifikasi lokasi GPS Anda.',
            ]);
        }
        // Token dikonsumsi saat submit (POST), bukan di sini
    }

    $patrol = \App\Models\Patrol::where('user_id', auth()->id())
        ->whereDate('patrol_time', today())
        ->latest('patrol_time')
        ->first();

    if (! $patrol) {
        return view('checkpoint-result', [
            'success'     => false,
            'icon'        => '🚫',
            'title'       => 'Patroli Tidak Ditemukan',
            'message'     => 'Tidak ada laporan patroli hari ini.',
            'actionUrl'   => url('/admin/patrols/create'),
            'actionLabel' => '📋 Buat Laporan',
        ]);
    }

    return view('checkpoint-form', compact('uuid', 'location', 'patrol'));
})->middleware('auth');

// Simpan checkpoint (POST = proses form)
Route::post('/admin/patrols/checkpoint/{uuid}', function (string $uuid) {
    $location = \App\Models\Location::where('uuid', $uuid)->first();
    if (! $location) abort(404);

    // Konsumsi GPS session token
    if ($location->latitude !== null && $location->longitude !== null) {
        session()->forget('geo_verified_' . $uuid);
    }

    $patrol = \App\Models\Patrol::where('user_id', auth()->id())
        ->whereDate('patrol_time', today())
        ->latest('patrol_time')
        ->first();

    if (! $patrol) abort(403, 'Tidak ada patroli aktif hari ini.');

    // Simpan foto muka
    $facePath = null;
    if (request()->hasFile('face_photo') && request()->file('face_photo')->isValid()) {
        $facePath = request()->file('face_photo')
            ->store('checkpoint-face-photos', 'public');
    }

    \App\Models\PatrolCheckpoint::create([
        'patrol_id'   => $patrol->id,
        'location_id' => $location->id,
        'user_id'     => auth()->id(),
        'face_photo'  => $facePath,
        'signature'   => request('signature') ?: null,
        'scanned_at'  => now(),
    ]);

    $checkpoints = \App\Models\PatrolCheckpoint::with('location')
        ->where('patrol_id', $patrol->id)
        ->orderBy('scanned_at')
        ->get();

    return view('checkpoint-result', [
        'success'     => true,
        'icon'        => '✅',
        'title'       => 'Checkpoint Berhasil!',
        'message'     => '📍 ' . $location->name . ' — ' . now()->format('H:i:s'),
        'subMessage'  => 'Total pos tercatat hari ini: ' . $checkpoints->count() . ' titik',
        'checkpoints' => $checkpoints,
        'actionUrl'   => url('/admin/patrols'),
        'actionLabel' => 'Lihat Laporan',
    ]);
})->middleware(['auth']);

// Geo-verification: returns JSON whether user is within location radius
Route::get('/admin/patrols/geo-verify/{uuid}', function (string $uuid) {
    $location = \App\Models\Location::where('uuid', $uuid)->first();

    if (! $location) {
        return response()->json(['allowed' => false, 'message' => 'Lokasi tidak ditemukan.'], 404);
    }

    // Koordinat belum dikonfigurasi → loloskan
    if ($location->latitude === null || $location->longitude === null) {
        return response()->json(['allowed' => true, 'location' => $location->name, 'location_id' => $location->id, 'geo_configured' => false]);
    }

    $lat = (float) request('lat');
    $lng = (float) request('lng');

    if (! $lat && ! $lng) {
        return response()->json([
            'allowed'        => false,
            'geo_configured' => true,
            'message'        => 'Koordinat GPS tidak dikirim.',
        ]);
    }

    $distance = (int) round($location->distanceTo($lat, $lng));
    $allowed  = $distance <= $location->radius_meters;

    if ($allowed) {
        session(['geo_verified_' . $uuid => now()->timestamp]);
    }

    return response()->json([
        'allowed'        => $allowed,
        'distance'       => $distance,
        'radius'         => $location->radius_meters,
        'location'       => $location->name,
        'location_id'    => $location->id,
        'geo_configured' => true,
    ]);
})->middleware('auth:web');

// Rekap Temuan Patrol — export PDF GET endpoint (dipanggil dari custom page via JS redirect)
Route::get('/admin/patrols/rekap-temuan/export-pdf', function () {
    $dateFrom       = request('date_from');
    $dateUntil      = request('date_until');
    $shiftId        = request('shift_id');
    $deptId         = request('dept_id');
    $onlyViolations = (bool) request('only_violations', false);

    $query = \App\Models\Patrol::with(['employee.department', 'shift', 'location', 'violation', 'action', 'user'])
        ->orderByDesc('patrol_time');

    if ($dateFrom)       $query->whereDate('patrol_time', '>=', $dateFrom);
    if ($dateUntil)      $query->whereDate('patrol_time', '<=', $dateUntil);
    if ($shiftId)        $query->where('shift_id', $shiftId);
    if ($deptId)         $query->whereHas('employee', fn ($q) => $q->where('dept_id', $deptId));
    if ($onlyViolations) $query->whereNotNull('employee_id');

    $patrols = $query->get();

    $patrols->each(function ($patrol) {
        $patrol->photo_base64_list = collect($patrol->photos ?? [])
            ->filter()
            ->map(function ($path) {
                try {
                    $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
                    if (file_exists($fullPath)) {
                        $mime = mime_content_type($fullPath) ?: 'image/jpeg';
                        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
                    }
                } catch (\Throwable $e) {}
                return null;
            })
            ->filter()->take(2)->values()->toArray();
    });

    $filterParts = [];
    if ($dateFrom && $dateUntil) {
        $filterParts[] = 'Tgl: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y')
            . ' – ' . \Carbon\Carbon::parse($dateUntil)->format('d/m/Y');
    } elseif ($dateFrom) {
        $filterParts[] = 'Dari: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y');
    } elseif ($dateUntil) {
        $filterParts[] = 'Sampai: ' . \Carbon\Carbon::parse($dateUntil)->format('d/m/Y');
    }
    if ($shiftId) $filterParts[] = 'Shift: ' . (\App\Models\Shift::find($shiftId)?->name ?? '-');
    if ($deptId)  $filterParts[] = 'Dept: '  . (\App\Models\Department::find($deptId)?->name  ?? '-');
    if ($onlyViolations) $filterParts[] = 'Hanya pelanggaran';

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.rekap-temuan-patrol', [
        'patrols'      => $patrols,
        'generated_at' => now()->format('d/m/Y H:i'),
        'filter_label' => implode(' | ', $filterParts) ?: null,
    ])->setPaper('a4', 'landscape');

    return response()->streamDownload(
        // export PDF
        fn () => print($pdf->output()),
        'rekap-temuan-patrol-' . now()->format('Y-m-d') . '.pdf'
    );
})->middleware('auth:web')->missing(fn () => redirect('/admin/login'));

// Rekap Temuan Patrol — export Excel (.xlsx) GET endpoint
Route::get('/admin/patrols/rekap-temuan/export-excel', function () {
    $dateFrom       = request('date_from');
    $dateUntil      = request('date_until');
    $shiftId        = request('shift_id');
    $deptId         = request('dept_id');
    $onlyViolations = (bool) request('only_violations', false);

    $query = \App\Models\Patrol::with(['employee.department', 'shift', 'location', 'violation', 'action', 'user'])
        ->orderByDesc('patrol_time');

    if ($dateFrom)       $query->whereDate('patrol_time', '>=', $dateFrom);
    if ($dateUntil)      $query->whereDate('patrol_time', '<=', $dateUntil);
    if ($shiftId)        $query->where('shift_id', $shiftId);
    if ($deptId)         $query->whereHas('employee', fn ($q) => $q->where('dept_id', $deptId));
    if ($onlyViolations) $query->whereNotNull('employee_id');

    $patrols = $query->get();

    // ── Build XLSX with PhpSpreadsheet ──────────────────────────────────
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet       = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Rekap Temuan Patrol');

    // ── Title Row ───────────────────────────────────────────────────────
    $sheet->mergeCells('A1:J1');
    $sheet->setCellValue('A1', 'REKAP TEMUAN PATROL');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // ── Filter info row ─────────────────────────────────────────────────
    $filterParts = [];
    if ($dateFrom && $dateUntil) {
        $filterParts[] = 'Periode: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') . ' – ' . \Carbon\Carbon::parse($dateUntil)->format('d/m/Y');
    } elseif ($dateFrom) {
        $filterParts[] = 'Dari: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y');
    } elseif ($dateUntil) {
        $filterParts[] = 'Sampai: ' . \Carbon\Carbon::parse($dateUntil)->format('d/m/Y');
    }
    if ($shiftId) $filterParts[] = 'Shift: ' . (\App\Models\Shift::find($shiftId)?->name ?? '-');
    if ($deptId)  $filterParts[] = 'Dept: '  . (\App\Models\Department::find($deptId)?->name  ?? '-');
    if ($onlyViolations) $filterParts[] = 'Hanya pelanggaran';

    $sheet->mergeCells('A2:J2');
    $sheet->setCellValue('A2', $filterParts ? implode(' | ', $filterParts) : 'Semua Data');
    $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10)->getColor()->setRGB('666666');
    $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    $sheet->mergeCells('A3:J3');
    $sheet->setCellValue('A3', 'Dicetak: ' . now()->format('d/m/Y H:i'));
    $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('999999');
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

    // ── Header Row (row 5) ──────────────────────────────────────────────
    $headerRow = 5;
    $headers = ['No', 'Tanggal', 'Shift', 'Group / Dept', 'Jam', 'Area', 'Temuan', 'Identitas Karyawan', 'Evidence', 'Sanksi / Tindakan'];
    $colLetters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];

    foreach ($headers as $idx => $header) {
        $sheet->setCellValue($colLetters[$idx] . $headerRow, $header);
    }

    // Header styling: dark background, white bold text, center
    $headerRange = 'A' . $headerRow . ':J' . $headerRow;
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold'  => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size'  => 11,
        ],
        'fill' => [
            'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '1F2937'], // dark gray
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ]);
    $sheet->getRowDimension($headerRow)->setRowHeight(30);

    // ── Data rows ───────────────────────────────────────────────────────
    $dataRow  = $headerRow + 1;
    $imgIndex = 0;

    foreach ($patrols as $i => $row) {
        $time     = $row->patrol_time ? \Carbon\Carbon::parse($row->patrol_time) : null;
        $employee = $row->employee;
        $dept     = $employee->department ?? null;
        $photos   = $row->photos ?? [];

        // 1. No
        $sheet->setCellValue('A' . $dataRow, $i + 1);

        // 2. Tanggal
        $sheet->setCellValue('B' . $dataRow, $time ? $time->format('d/m/Y') : '-');

        // 3. Shift
        $sheet->setCellValue('C' . $dataRow, $row->shift->name ?? '-');

        // 4. Group / Dept
        $sheet->setCellValue('D' . $dataRow, $dept->name ?? '-');

        // 5. Jam
        $sheet->setCellValue('E' . $dataRow, $time ? $time->format('H:i') : '-');

        // 6. Area
        $sheet->setCellValue('F' . $dataRow, $row->location->name ?? '-');

        // 7. Temuan (Pelanggaran)
        $sheet->setCellValue('G' . $dataRow, $row->violation?->name ?? 'Tidak ada temuan');

        // 8. Identitas Karyawan (name + NIP)
        if ($employee) {
            $empText = $employee->name . "\nNIP: " . ($employee->nip ?? '-');
            if ($dept) $empText .= "\n" . $dept->name;
            $sheet->setCellValue('H' . $dataRow, $empText);
            $sheet->getStyle('H' . $dataRow)->getAlignment()->setWrapText(true);
        } else {
            $sheet->setCellValue('H' . $dataRow, '-');
        }

        // 9. Evidence (embed photo into cell)
        $hasPhoto = false;
        if (!empty($photos)) {
            $photoArr = is_array($photos) ? $photos : [$photos];
            $firstPhoto = collect($photoArr)->filter()->first();
            if ($firstPhoto) {
                try {
                    $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($firstPhoto);
                    if (file_exists($fullPath)) {
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setName('Evidence_' . ($i + 1));
                        $drawing->setDescription('Foto temuan');
                        $drawing->setPath($fullPath);
                        $drawing->setHeight(75);
                        $drawing->setCoordinates('I' . $dataRow);
                        $drawing->setOffsetX(5);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);
                        $hasPhoto = true;
                        $imgIndex++;
                    }
                } catch (\Throwable $e) {
                    // skip if image fails
                }
            }
        }
        if (!$hasPhoto) {
            $sheet->setCellValue('I' . $dataRow, 'Tidak ada');
            $sheet->getStyle('I' . $dataRow)->getFont()->setItalic(true)->getColor()->setRGB('999999');
        }

        // 10. Sanksi / Tindakan
        $sheet->setCellValue('J' . $dataRow, $row->action->name ?? '-');

        // Row height: taller if photo exists
        $sheet->getRowDimension($dataRow)->setRowHeight($hasPhoto ? 65 : 30);

        // Alternate row shading
        if ($i % 2 === 1) {
            $sheet->getStyle('A' . $dataRow . ':J' . $dataRow)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F9FAFB');
        }

        $dataRow++;
    }

    // ── Borders on all data ─────────────────────────────────────────────
    $lastDataRow = $dataRow - 1;
    if ($lastDataRow >= $headerRow) {
        $dataRange = 'A' . $headerRow . ':J' . max($lastDataRow, $headerRow);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color'       => ['rgb' => 'D1D5DB'],
                ],
            ],
            'alignment' => [
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
    }

    // Center align specific columns
    if ($lastDataRow >= $headerRow + 1) {
        $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastDataRow)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . ($headerRow + 1) . ':E' . $lastDataRow)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('I' . ($headerRow + 1) . ':I' . $lastDataRow)->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
    }

    // ── Column widths ───────────────────────────────────────────────────
    $colWidths = ['A' => 5, 'B' => 14, 'C' => 12, 'D' => 20, 'E' => 8, 'F' => 20, 'G' => 30, 'H' => 25, 'I' => 16, 'J' => 22];
    foreach ($colWidths as $col => $w) {
        $sheet->getColumnDimension($col)->setWidth($w);
    }

    // ── Summary row ─────────────────────────────────────────────────────
    $summaryRow = $dataRow + 1;
    $sheet->mergeCells('A' . $summaryRow . ':J' . $summaryRow);
    $sheet->setCellValue('A' . $summaryRow, 'Total Data: ' . $patrols->count() . ' record');
    $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true)->setSize(10);
    $sheet->getStyle('A' . $summaryRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

    // ── Write to output ─────────────────────────────────────────────────
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

    $fileName = 'rekap-temuan-patrol-' . now()->format('Y-m-d') . '.xlsx';

    return response()->streamDownload(function () use ($writer) {
        $writer->save('php://output');
    }, $fileName, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
})->middleware('auth:web')->missing(fn () => redirect('/admin/login'));

// Checksheet Patrol — export PDF GET endpoint
Route::get('/admin/patrols/checksheet/export-pdf', function () {
    $dateFrom   = request('date_from');
    $dateUntil  = request('date_until');
    $shiftId    = request('shift_id');
    $locationId = request('location_id');

    $query = \App\Models\Patrol::with(['shift', 'user', 'employee.department', 'location'])
        ->orderBy('patrol_time');

    if ($dateFrom)   $query->whereDate('patrol_time', '>=', $dateFrom);
    if ($dateUntil)  $query->whereDate('patrol_time', '<=', $dateUntil);
    if ($shiftId)    $query->where('shift_id', $shiftId);
    if ($locationId) $query->where('location_id', $locationId);

    $patrols = $query->get();

    $filterParts = [];
    if ($dateFrom && $dateUntil) {
        $filterParts[] = 'Tgl: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y')
            . ' – ' . \Carbon\Carbon::parse($dateUntil)->format('d/m/Y');
    } elseif ($dateFrom) {
        $filterParts[] = 'Dari: ' . \Carbon\Carbon::parse($dateFrom)->format('d/m/Y');
    } elseif ($dateUntil) {
        $filterParts[] = 'Sampai: ' . \Carbon\Carbon::parse($dateUntil)->format('d/m/Y');
    }
    if ($shiftId) $filterParts[] = 'Shift: ' . (\App\Models\Shift::find($shiftId)?->name ?? '-');

    $locationName = $locationId ? (\App\Models\Location::find($locationId)?->name ?? null) : null;

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('exports.checksheet-patrol', [
        'patrols'       => $patrols,
        'generated_at'  => now()->format('d/m/Y H:i'),
        'filter_label'  => implode(' | ', $filterParts) ?: null,
        'location_name' => $locationName,
    ])->setPaper('a4', 'portrait');

    return response()->streamDownload(
        fn () => print($pdf->output()),
        'checksheet-patrol-' . now()->format('Y-m-d') . '.pdf'
    );
})->middleware('auth');

// Checksheet Patrol — export EXCEL GET endpoint
Route::get('/admin/patrols/checksheet/export-excel', function () {
    $dateFrom   = request('date_from');
    $dateUntil  = request('date_until');
    $shiftId    = request('shift_id');
    $locationId = request('location_id');

    $query = \App\Models\Patrol::with(['shift', 'user', 'employee.department', 'location'])
        ->orderBy('patrol_time');

    if ($dateFrom)   $query->whereDate('patrol_time', '>=', $dateFrom);
    if ($dateUntil)  $query->whereDate('patrol_time', '<=', $dateUntil);
    if ($shiftId)    $query->where('shift_id', $shiftId);
    if ($locationId) $query->where('location_id', $locationId);

    $patrols = $query->get();

    $locationName = $locationId ? (\App\Models\Location::find($locationId)?->name ?? 'Semua Area') : 'Semua Area';

    return response()->streamDownload(
        function () use ($patrols, $locationName) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Title
            $sheet->setCellValue('A1', 'CHECKSHEET PATROL HR OPERATION');
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

            // Patrol Area
            $sheet->setCellValue('A2', 'PATROL AREA : ' . strtoupper($locationName));
            $sheet->mergeCells('A2:G2');
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(9);

            // Headers (row 4)
            $headers = ['No', 'Tanggal', 'Shift', 'Group', 'Jam', 'Nama Petugas Patrol', 'Paraf'];
            foreach ($headers as $col => $header) {
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1) . '4', $header);
            }
            $sheet->getStyle('A4:G4')->getFont()->setBold(true);
            $sheet->getStyle('A4:G4')->getFill()->setFillType('solid')->getStartColor()->setRGB('E0E0E0');

            // Data
            $row = 5;
            foreach ($patrols as $i => $patrol) {
                $patrolTime = $patrol->patrol_time ? \Carbon\Carbon::parse($patrol->patrol_time) : null;
                $dept = $patrol->employee?->department;

                $sheet->setCellValue('A' . $row, $i + 1);
                $sheet->setCellValue('B' . $row, $patrolTime?->format('d-m-Y') ?? '');
                $sheet->setCellValue('C' . $row, $patrol->shift?->name ?? '');
                $sheet->setCellValue('D' . $row, $dept?->name ?? '');
                $sheet->setCellValue('E' . $row, $patrolTime?->format('H:i') ?? '');
                $sheet->setCellValue('F' . $row, $patrol->user?->name ?? '');
                $sheet->setCellValue('G' . $row, ''); // Paraf column left empty for manual signature

                $row++;
            }

            // Column widths
            $sheet->getColumnDimension('A')->setWidth(5);
            $sheet->getColumnDimension('B')->setWidth(16);
            $sheet->getColumnDimension('C')->setWidth(10);
            $sheet->getColumnDimension('D')->setWidth(12);
            $sheet->getColumnDimension('E')->setWidth(10);
            $sheet->getColumnDimension('F')->setWidth(20);
            $sheet->getColumnDimension('G')->setWidth(25);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        },
        'checksheet-patrol-' . now()->format('Y-m-d') . '.xlsx'
    );
})->middleware('auth');

Route::get('/', function () {
    return view('welcome');
});
