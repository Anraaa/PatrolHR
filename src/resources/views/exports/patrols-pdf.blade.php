<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Patroli</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 15px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .header p { font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #2563eb; color: white; padding: 6px 4px; text-align: left; font-size: 9px; font-weight: bold; }
        td { padding: 5px 4px; border-bottom: 1px solid #ddd; font-size: 9px; vertical-align: top; }
        tr:nth-child(even) { background-color: #f8fafc; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .footer { margin-top: 15px; text-align: right; font-size: 8px; color: #999; }
        .no-data { text-align: center; padding: 20px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN TEMUAN PATROLI</h1>
        <p>Dicetak pada: {{ $generated_at }} &mdash; Total Data: {{ $patrols->count() }} record</p>
    </div>

    @if($patrols->isEmpty())
        <p class="no-data">Tidak ada data patroli untuk ditampilkan.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th style="width: 3%">No</th>
                    <th style="width: 10%">Tgl Patrol</th>
                    <th style="width: 7%">NIP</th>
                    <th style="width: 12%">Nama</th>
                    <th style="width: 9%">Group</th>
                    <th style="width: 7%">Shift</th>
                    <th style="width: 18%">Item Temuan</th>
                    <th style="width: 10%">Pelanggaran</th>
                    <th style="width: 10%">Action</th>
                    <th style="width: 9%">Lokasi</th>
                    <th style="width: 5%">PIC</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patrols as $index => $patrol)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $patrol->patrol_time?->format('d/m/Y H:i') }}</td>
                        <td>{{ $patrol->employee?->nip }}</td>
                        <td>{{ $patrol->employee?->name }}</td>
                        <td>{{ $patrol->employee?->shfgroup }}</td>
                        <td><span class="badge badge-info">{{ $patrol->shift?->name }}</span></td>
                        <td>{{ $patrol->description }}</td>
                        <td><span class="badge badge-danger">{{ $patrol->violation?->name }}</span></td>
                        <td>
                            @php
                                $actionName = $patrol->action?->name ?? '';
                                $badgeClass = match(true) {
                                    str_contains($actionName, 'Peringatan 3') => 'badge-danger',
                                    str_contains($actionName, 'Peringatan') => 'badge-warning',
                                    str_contains($actionName, 'Teguran') => 'badge-warning',
                                    str_contains($actionName, 'Pernyataan') => 'badge-info',
                                    default => 'badge-success',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $actionName }}</span>
                        </td>
                        <td>{{ $patrol->location?->name }}</td>
                        <td>{{ $patrol->user?->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        <p>Dokumen ini digenerate secara otomatis oleh Sistem Checksheet Patrol</p>
    </div>
</body>
</html>
