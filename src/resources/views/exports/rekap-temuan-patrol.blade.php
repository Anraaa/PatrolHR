<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8pt; color: #1a1a1a; }

        .page-header { text-align: center; margin-bottom: 12px; }
        .page-header h1 { font-size: 14pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .page-header p { font-size: 8pt; color: #555; margin-top: 3px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #b0b0b0; padding: 4px 5px; vertical-align: middle; }
        thead th {
            background: #1F2937;
            color: #fff;
            font-size: 7.5pt;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        tbody tr:nth-child(odd)  td { background: #ffffff; }

        .col-no         { width: 3%;  text-align: center; }
        .col-tanggal    { width: 9%; }
        .col-shift      { width: 7%;  text-align: center; }
        .col-group      { width: 10%; }
        .col-jam        { width: 5%;  text-align: center; font-family: monospace; }
        .col-area       { width: 10%; }
        .col-temuan     { width: 18%; }
        .col-karyawan   { width: 14%; }
        .col-evidence   { width: 14%; text-align: center; }
        .col-sanksi     { width: 10%; }

        .badge {
            display: inline-block;
            border-radius: 20px;
            padding: 1px 6px;
            font-size: 7pt;
            font-weight: bold;
        }
        .badge-shift { background: #dbeafe; color: #1d4ed8; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-gray { background: #f3f4f6; color: #6b7280; }

        .text-muted { color: #9ca3af; font-style: italic; }
        .text-green { color: #16a34a; font-weight: bold; }
        .text-small { font-size: 7pt; color: #6b7280; }

        .evidence-img { max-height: 55px; max-width: 70px; border: 1px solid #e5e7eb; border-radius: 3px; }
        .evidence-wrap { display: inline-block; margin: 1px; }

        .footer { margin-top: 10px; font-size: 7pt; color: #888; text-align: right; }
    </style>
</head>
<body>

    <div class="page-header">
        <h1>Rekap Temuan Patrol</h1>
        @if ($filter_label)
            <p>{{ $filter_label }}</p>
        @endif
        <p>Dicetak: {{ $generated_at }} &nbsp;|&nbsp; Total: {{ $patrols->count() }} data</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-shift">Shift</th>
                <th class="col-group">Group / Dept</th>
                <th class="col-jam">Jam</th>
                <th class="col-area">Area</th>
                <th class="col-temuan">Temuan</th>
                <th class="col-karyawan">Identitas Karyawan</th>
                <th class="col-evidence">Evidence</th>
                <th class="col-sanksi">Sanksi / Tindakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($patrols as $i => $patrol)
                @php
                    $pt         = $patrol->patrol_time ? \Carbon\Carbon::parse($patrol->patrol_time) : null;
                    $employee   = $patrol->employee;
                    $shfgroup   = $employee?->shfgroup ?? '—';
                    $violation  = $patrol->violation;
                    $action     = $patrol->action;
                    $actionName = $action->name ?? null;

                    $actionBadge = match(true) {
                        $actionName && (str_contains($actionName, 'SP') || str_contains($actionName, 'PHK')) => 'badge-danger',
                        $actionName && (str_contains($actionName, 'Peringatan') || str_contains($actionName, 'Teguran')) => 'badge-warning',
                        $actionName && (str_contains($actionName, 'Pernyataan') || str_contains($actionName, 'Pembinaan')) => 'badge-info',
                        $actionName => 'badge-success',
                        default => 'badge-gray',
                    };

                    $photos = $patrol->photo_base64_list ?? [];
                @endphp
                <tr>
                    {{-- 1. No --}}
                    <td class="col-no">{{ $i + 1 }}</td>

                    {{-- 2. Tanggal --}}
                    <td class="col-tanggal">{{ $pt?->format('d/m/Y') ?? '—' }}</td>

                    {{-- 3. Shift --}}
                    <td class="col-shift">
                        @if ($patrol->shift)
                            <span class="badge badge-shift">{{ $patrol->shift->name }}</span>
                        @else
                            —
                        @endif
                    </td>

                    {{-- 4. Group / Dept --}}
                    <td class="col-group">{{ $shfgroup }}</td>

                    {{-- 5. Jam --}}
                    <td class="col-jam">{{ $pt?->format('H:i') ?? '—' }}</td>

                    {{-- 6. Area --}}
                    <td class="col-area">{{ $patrol->location?->name ?? '—' }}</td>

                    {{-- 7. Temuan (Pelanggaran) --}}
                    <td class="col-temuan">
                        @if ($violation)
                            <span class="badge badge-danger">{{ $violation->name }}</span>
                        @else
                            <span class="text-green">✓ Tidak ada temuan</span>
                        @endif
                    </td>

                    {{-- 8. Identitas Karyawan --}}
                    <td class="col-karyawan">
                        @if ($employee)
                            <strong>{{ $employee->name }}</strong><br>
                            <span class="text-small">NIP: {{ $employee->nip ?? '-' }}</span>
                            <br><span class="text-small">Group: {{ $shfgroup }}</span>
                        @else
                            —
                        @endif
                    </td>

                    {{-- 9. Evidence (foto temuan) --}}
                    <td class="col-evidence">
                        @if (!empty($photos))
                            @foreach ($photos as $b64)
                                <span class="evidence-wrap">
                                    <img class="evidence-img" src="{{ $b64 }}" alt="Bukti">
                                </span>
                            @endforeach
                        @else
                            <span class="text-muted">Tidak ada</span>
                        @endif
                    </td>

                    {{-- 10. Sanksi / Tindakan --}}
                    <td class="col-sanksi">
                        @if ($actionName)
                            <span class="badge {{ $actionBadge }}">{{ $actionName }}</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align:center; color:#aaa; padding: 20px;">Tidak ada data temuan untuk filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Dokumen ini digenerate otomatis oleh Sistem Patrol.</div>

</body>
</html>
