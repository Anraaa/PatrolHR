<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 9pt; color: #1a1a1a; }

        .page-header { text-align: center; margin-bottom: 6px; }
        .page-header h1 {
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-decoration: underline;
        }
        .patrol-area {
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .meta { font-size: 7pt; color: #888; text-align: right; margin-bottom: 8px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #555; padding: 4px 5px; vertical-align: middle; }
        thead th {
            background: #ffffff;
            color: #000;
            font-size: 8pt;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        tbody td { background: #ffffff; font-size: 8pt; }

        .col-no       { width: 5%;  text-align: center; }
        .col-tanggal  { width: 16%; text-align: center; }
        .col-shift    { width: 8%;  text-align: center; }
        .col-group    { width: 10%; text-align: center; }
        .col-jam      { width: 8%;  text-align: center; font-family: monospace; }
        .col-petugas  { width: 23%; }
        .col-paraf    { width: 30%; text-align: center; }

        .sig-img { max-height: 35px; max-width: 90px; }

        .footer { margin-top: 6px; font-size: 7pt; color: #888; text-align: right; }
    </style>
</head>
<body>

    <div class="page-header">
        <h1>Checksheet Patrol HR Operation</h1>
    </div>

    <div class="patrol-area">
        Patrol Area : {{ $location_name ? strtoupper($location_name) : 'SEMUA AREA' }}
    </div>

    @if ($filter_label)
        <div class="meta">Filter: {{ $filter_label }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-tanggal">Tanggal</th>
                <th class="col-shift">Shift</th>
                <th class="col-group">Group</th>
                <th class="col-jam">Jam</th>
                <th class="col-petugas">Nama Petugas Patrol</th>
                <th class="col-paraf">Paraf</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($patrols as $i => $patrol)
                @php
                    $pt   = $patrol->patrol_time ? \Carbon\Carbon::parse($patrol->patrol_time) : null;
                    $dept = $patrol->employee?->department;
                    $sig  = $patrol->signature;
                    if ($sig && !str_starts_with($sig, 'data:')) {
                        try {
                            $path = \Illuminate\Support\Facades\Storage::disk('public')->path($sig);
                            if (file_exists($path)) {
                                $mime = mime_content_type($path) ?: 'image/png';
                                $sig  = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
                            } else { $sig = null; }
                        } catch (\Throwable $e) { $sig = null; }
                    }
                @endphp
                <tr>
                    <td class="col-no">{{ $i + 1 }}</td>
                    <td class="col-tanggal">{{ $pt?->format('d-m-Y') ?? '—' }}</td>
                    <td class="col-shift">{{ $patrol->shift?->name ?? '—' }}</td>
                    <td class="col-group">{{ $dept?->name ?? '—' }}</td>
                    <td class="col-jam">{{ $pt?->format('H:i') ?? '—' }}</td>
                    <td class="col-petugas">{{ $patrol->user?->name ?? '—' }}</td>
                    <td class="col-paraf">
                        @if ($sig)
                            <img class="sig-img" src="{{ $sig }}" alt="Paraf">
                        @else
                            &nbsp;
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center; color:#aaa; padding: 16px;">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Dicetak: {{ $generated_at }} &nbsp;|&nbsp; Total: {{ $patrols->count() }} data</div>

</body>
</html>
