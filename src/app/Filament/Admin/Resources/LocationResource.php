<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\LocationResource\Pages;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LocationResource extends Resource
{
    protected static ?string $model = Location::class;


    protected static ?string $pluralLabel = 'Lokasi';

    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount(['patrols']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Lokasi')
                    ->description('Titik/area yang menjadi pos patroli')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lokasi')
                            ->placeholder('Contoh: Gerbang Utama, Area Produksi A, Gudang')
                            ->helperText('Nama area/titik yang akan dipatroli oleh petugas')
                            ->prefixIcon('heroicon-m-map-pin')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Koordinat GPS & Radius Scan')
                    ->description('Petugas hanya bisa scan QR jika berada dalam radius yang ditentukan')
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->placeholder('-6.185466285468135')
                            ->helperText('Buka Google Maps → klik kanan di titik lokasi → salin koordinat pertama')
                            ->numeric()
                            ->step(0.000000000000001)
                            ->rules(['nullable', 'numeric', 'between:-90,90'])
                            ->prefixIcon('heroicon-m-map-pin'),

                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->placeholder('106.55540621550654')
                            ->helperText('Koordinat kedua dari Google Maps')
                            ->numeric()
                            ->step(0.000000000000001)
                            ->rules(['nullable', 'numeric', 'between:-180,180'])
                            ->prefixIcon('heroicon-m-map-pin'),

                        Forms\Components\TextInput::make('radius_meters')
                            ->label('Radius Scan (meter)')
                            ->helperText('Petugas harus berada dalam radius ini untuk bisa scan QR lokasi ini')
                            ->numeric()
                            ->default(30)
                            ->minValue(5)
                            ->maxValue(500)
                            ->suffix('m')
                            ->prefixIcon('heroicon-m-arrows-pointing-out')
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('_geo_notice')
                            ->label('')
                            ->content(new \Illuminate\Support\HtmlString(
                                '<div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-300">'
                                . '⚠️ <strong>Perlu HTTPS:</strong> Verifikasi GPS hanya aktif jika halaman diakses via HTTPS. '
                                . 'Pada HTTP, scan tetap bisa dilakukan namun pembatasan radius tidak diberlakukan.'
                                . '</div>'
                            ))
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('QR Code Lokasi')
                    ->description('QR Code untuk scan patroli di lokasi ini')
                    ->icon('heroicon-o-qr-code')
                    ->schema([
                        Forms\Components\Placeholder::make('qr_preview')
                            ->label('QR Code')
                            ->content(function ($record) {
                                if (! $record) {
                                    return 'QR Code akan di-generate otomatis setelah lokasi disimpan.';
                                }
                                $svg = QrCode::format('svg')
                                    ->size(200)
                                    ->margin(1)
                                    ->generate($record->qr_content);

                                return new \Illuminate\Support\HtmlString(
                                    '<div style="text-align:center;max-width:280px;margin:0 auto;border:1px solid #e2e8f0;overflow:hidden;border-radius:12px">'
                                    . '<div style="background:#1e3a5f;padding:10px;color:#fff;font-weight:bold;font-size:13px;letter-spacing:0.5px">' . e($record->name) . '</div>'
                                    . '<div style="padding:20px;background:#fff">' . $svg . '</div>'
                                    . '<div style="background:#2563eb;padding:6px;color:#fff;font-size:10px;font-weight:bold;letter-spacing:1px">SCAN UNTUK LAPORAN PATROLI</div>'
                                    . '<div style="background:#f8fafc;padding:8px;border-top:1px solid #e2e8f0">'
                                    . '<p style="font-size:9px;color:#94a3b8;font-family:monospace;word-break:break-all">ID: ' . e($record->uuid) . '</p>'
                                    . '<p style="font-size:9px;color:#64748b;margin-top:4px">' . e($record->qr_content) . '</p>'
                                    . '</div>'
                                    . '</div>'
                                );
                            })
                            ->columnSpanFull(),
                    ])
                    ->visible(fn (string $operation): bool => $operation === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lokasi')
                    ->icon('heroicon-m-map-pin')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('uuid')
                    ->label('UUID')
                    ->limit(12)
                    ->tooltip(fn ($record) => $record->uuid)
                    ->copyable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('patrols_count')
                    ->counts('patrols')
                    ->label('Total Patroli')
                    ->badge()
                    ->color('success')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('downloadQr')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->action(function (Location $record) {
                        $svg = QrCode::format('svg')
                            ->size(300)
                            ->margin(1)
                            ->generate($record->qr_content);

                        $html = view('exports.location-qr', [
                            'locations' => collect([$record]),
                            'title' => 'QR Code - ' . $record->name,
                        ])->render();

                        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                            ->setPaper('a6', 'portrait');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'qr-' . \Illuminate\Support\Str::slug($record->name) . '.pdf'
                        );
                    }),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('printAllQr')
                        ->label('Cetak Semua QR')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $html = view('exports.location-qr', [
                                'locations' => $records,
                                'title' => 'QR Code Lokasi Patroli',
                            ])->render();

                            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)
                                ->setPaper('a4', 'portrait');

                            return response()->streamDownload(
                                fn () => print($pdf->output()),
                                'qr-lokasi-patroli-' . now()->format('Y-m-d') . '.pdf'
                            );
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }
}
