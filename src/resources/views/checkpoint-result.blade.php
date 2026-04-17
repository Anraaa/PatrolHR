<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Checkpoint Patroli' }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .card {
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 4px 24px rgba(0,0,0,.1);
            padding: 2.5rem 2rem;
            max-width: 360px;
            width: 100%;
            text-align: center;
        }
        .icon { font-size: 4rem; margin-bottom: 1rem; }
        .title { font-size: 1.2rem; font-weight: 700; color: #111827; margin-bottom: .5rem; }
        .msg { font-size: .9rem; color: #6b7280; line-height: 1.5; }
        .sub { font-size: .8rem; color: #9ca3af; margin-top: .75rem; }
        .badge {
            display: inline-block;
            padding: .3rem .9rem;
            border-radius: 9999px;
            font-size: .8rem;
            font-weight: 600;
            margin-bottom: 1.25rem;
        }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .checkpoint-list {
            text-align: left;
            margin-top: 1.25rem;
            border-top: 1px solid #f3f4f6;
            padding-top: 1rem;
        }
        .checkpoint-list .list-title { font-size: .8rem; font-weight: 700; color: #374151; margin-bottom: .6rem; }
        .checkpoint-list ul { list-style: none; }
        .checkpoint-list li {
            font-size: .8rem; color: #6b7280;
            padding: .35rem 0;
            border-bottom: 1px solid #f9fafb;
            display: flex; justify-content: space-between;
        }
        .checkpoint-list li:last-child { border-bottom: none; }
        .btn {
            display: inline-block;
            margin-top: 1.5rem;
            background: #4f46e5;
            color: #fff;
            border: none;
            padding: .625rem 1.5rem;
            border-radius: .5rem;
            font-size: .9rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover { background: #4338ca; }
        .btn-gray { background: #6b7280; }
        .btn-gray:hover { background: #4b5563; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">{{ $icon ?? '📍' }}</div>

        @if($success ?? false)
            <span class="badge badge-success">CHECKPOINT DICATAT</span>
        @else
            <span class="badge badge-danger">GAGAL</span>
        @endif

        <div class="title">{{ $title ?? '' }}</div>
        <div class="msg">{{ $message ?? '' }}</div>

        @if(!empty($subMessage))
            <div class="sub">{{ $subMessage }}</div>
        @endif

        @if(!empty($checkpoints))
            <div class="checkpoint-list">
                <div class="list-title">Checkpoint hari ini ({{ count($checkpoints) }} titik):</div>
                <ul>
                    @foreach($checkpoints as $cp)
                        <li>
                            <span>{{ $loop->iteration }}. {{ $cp->location->name }}</span>
                            <span>{{ $cp->scanned_at->format('H:i:s') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($actionUrl))
            <a class="btn" href="{{ $actionUrl }}">{{ $actionLabel ?? 'Kembali' }}</a>
        @endif
    </div>
</body>
</html>
