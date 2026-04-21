<?php

namespace App\Filament\Admin\Resources;

use App\Forms\Components\SignaturePad;
use App\Filament\Admin\Resources\PatrolResource\Pages;
use App\Models\Patrol;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;

class PatrolResource extends Resource
{
    protected static ?string $model = Patrol::class;

    protected static ?string $navigationGroup = 'Patroli';

    protected static ?string $label = 'Laporan Patroli';

    protected static ?string $pluralLabel = 'Laporan Patroli';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 0 ? 'warning' : 'success';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // FORM (tidak diubah)
    // ─────────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([

                    Forms\Components\Wizard\Step::make('Info Patroli')
                        ->icon('heroicon-o-clock')
                        ->description('Waktu, shift, dan petugas')
                        ->schema([

                            Forms\Components\Section::make('Waktu & Shift Patroli')
                                ->description('Kapan patroli dilakukan dan siapa yang bertugas')
                                ->icon('heroicon-o-clock')
                                ->schema([
                                    Forms\Components\DateTimePicker::make('patrol_time')
                                        ->label('Tanggal & Jam Patroli')
                                        ->helperText('Dapat diubah jika perlu koreksi waktu')
                                        ->prefixIcon('heroicon-m-calendar')
                                        ->required()
                                        ->default(now())
                                        ->seconds(false)
                                        ->native(false)
                                        ->displayFormat('d/m/Y H:i')
                                        ->closeOnDateSelection()
                                        ->hiddenOn('create'),

                                    Forms\Components\Placeholder::make('patrol_time_display')
                                        ->label('Tanggal & Jam Patroli')
                                        ->content(fn () => now()->translatedFormat('l, d F Y — H:i'))
                                        ->helperText('Otomatis tercatat saat Anda scan QR / buka form ini')
                                        ->visibleOn('create'),

                                    Forms\Components\Select::make('shift_id')
                                        ->label('Grup Shift')
                                        ->helperText('Pilih shift yang sedang bertugas')
                                        ->relationship('shift', 'name')
                                        ->required()
                                        ->preload()
                                        ->native(false)
                                        ->prefixIcon('heroicon-m-clock')
                                        ->hiddenOn('create'),

                                    Forms\Components\Placeholder::make('shift_display')
                                        ->label('Grup Shift')
                                        ->content(function () {
                                            $hour = (int) now()->format('G');
                                            if ($hour >= 7 && $hour < 15) return '🟢  Shift 1 — 07:00 s/d 14:59';
                                            if ($hour >= 15 && $hour < 23) return '🟡  Shift 2 — 15:00 s/d 22:59';
                                            return '🔵  Shift 3 — 23:00 s/d 06:59';
                                        })
                                        ->helperText('Terdeteksi otomatis dari jam saat ini')
                                        ->visibleOn('create'),
                                ])
                                ->columns(2)
                                ->compact(),

                            Forms\Components\Section::make('Petugas Pelapor (PIC)')
                                ->description('Satpam yang menginput laporan ini')
                                ->icon('heroicon-o-user-circle')
                                ->schema([
                                    Forms\Components\Select::make('user_id')
                                        ->label('PIC Patroli')
                                        ->relationship('user', 'name')
                                        ->default(fn () => auth()->id())
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->prefixIcon('heroicon-m-shield-check')
                                        ->hiddenOn('create'),

                                    Forms\Components\Placeholder::make('user_display')
                                        ->label('PIC Patroli')
                                        ->content(fn () => auth()->user()?->name ?? '-')
                                        ->visibleOn('create'),
                                ])
                                ->compact(),
                        ]),

                    Forms\Components\Wizard\Step::make('Karyawan & Pelanggaran')
                        ->icon('heroicon-o-identification')
                        ->description('Apakah ada temuan atau pelanggar?')
                        ->schema([

                            Forms\Components\Section::make()
                                ->schema([
                                    Forms\Components\Toggle::make('has_violation')
                                        ->label('Ada Temuan / Pelanggar?')
                                        ->helperText('Aktifkan jika ditemukan karyawan yang melanggar saat patroli ini')
                                        ->onColor('danger')
                                        ->offColor('success')
                                        ->onIcon('heroicon-m-exclamation-triangle')
                                        ->offIcon('heroicon-m-check-circle')
                                        ->live()
                                        ->dehydrated(false)
                                        ->default(false)
                                        ->afterStateHydrated(function ($state, Set $set, $record) {
                                            $set('has_violation', $record?->employee_id !== null);
                                        })
                                        ->columnSpanFull(),
                                ])
                                ->compact(),

                            Forms\Components\Placeholder::make('_no_violation_info')
                                ->label('')
                                ->content(new \Illuminate\Support\HtmlString(
                                    '<div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-950/40 dark:text-green-300">'
                                    . '✅ <strong>Tidak Ada Temuan</strong> — Patroli selesai tanpa pelanggaran. Lanjutkan ke step berikutnya untuk foto & tanda tangan.'
                                    . '</div>'
                                ))
                                ->visible(fn (Get $get) => ! (bool) $get('has_violation'))
                                ->columnSpanFull(),

                            Forms\Components\Section::make('Identitas Karyawan Pelanggar')
                                ->icon('heroicon-o-identification')
                                ->visible(fn (Get $get) => (bool) $get('has_violation'))
                                ->schema([
                                    Forms\Components\Select::make('employee_id')
                                        ->label('Karyawan (NIP — Nama)')
                                        ->relationship('employee', 'name')
                                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->nip} — {$record->name}")
                                        ->searchable()
                                        ->preload()
                                        ->prefixIcon('heroicon-m-magnifying-glass')
                                        ->live()
                                        ->required(fn (Get $get) => (bool) $get('has_violation'))
                                        ->afterStateUpdated(fn (Set $set, ?string $state) =>
                                            $state ? $set('_group_display', Employee::find($state)?->shfgroup) : $set('_group_display', null)
                                        ),

                                    Forms\Components\Placeholder::make('_group_display')
                                        ->label('Shift Group')
                                        ->content(function (Get $get) {
                                            if (!$id = $get('employee_id')) return '— Pilih karyawan dulu';
                                            return Employee::find($id)?->shfgroup ?? '-';
                                        }),
                                ])
                                ->columns(2)
                                ->compact(),

                            Forms\Components\Section::make('Lokasi Patroli')
                                ->icon('heroicon-o-map-pin')
                                ->schema([
                                    Forms\Components\Select::make('location_id')
                                        ->label('Lokasi / Area Patrol')
                                        ->relationship('location', 'name')
                                        ->required()
                                        ->searchable()
                                        ->preload()
                                        ->prefixIcon('heroicon-m-map-pin')
                                        ->columnSpanFull()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                                        ]),
                                ])
                                ->compact(),

                            Forms\Components\Section::make('Jenis Pelanggaran')
                                ->icon('heroicon-o-exclamation-triangle')
                                ->visible(fn (Get $get) => (bool) $get('has_violation'))
                                ->schema([
                                    Forms\Components\Select::make('violation_id')
                                        ->label('Jenis Pelanggaran')
                                        ->relationship('violation', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->prefixIcon('heroicon-m-exclamation-triangle')
                                        ->required(fn (Get $get) => (bool) $get('has_violation'))
                                        ->columnSpanFull()
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('name')->required()->maxLength(255),
                                        ]),
                                ])
                                ->compact(),

                            Forms\Components\Section::make('Detail Temuan & Respon')
                                ->visible(fn (Get $get) => (bool) $get('has_violation'))
                                ->schema([
                                    Forms\Components\Textarea::make('description')
                                        ->label('Deskripsi Temuan')
                                        ->placeholder('Jelaskan temuan pelanggaran secara detail...')
                                        ->rows(5)
                                        ->columnSpanFull(),

                                    Forms\Components\Select::make('action_id')
                                        ->label('Tindakan yang Diambil')
                                        ->relationship('action', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->prefixIcon('heroicon-m-hand-raised')
                                        ->columnSpanFull(),

                                    Forms\Components\FileUpload::make('photos')
                                        ->label('Foto Temuan')
                                        ->helperText('Upload foto bukti pelanggaran (maks 5 foto)')
                                        ->multiple()
                                        ->image()
                                        ->imageEditor()
                                        ->directory('patrol-photos')
                                        ->maxFiles(5)
                                        ->openable()
                                        ->downloadable()
                                        ->columnSpanFull(),
                                ])->compact(),
                        ]),

                    Forms\Components\Wizard\Step::make('Checkpoint & Absensi')
                        ->icon('heroicon-o-qr-code')
                        ->description('Scan QR lokasi, foto muka, dan tanda tangan')
                        ->schema([
                            Forms\Components\Hidden::make('checkpoint_location_id'),
                            Forms\Components\Hidden::make('checkpoint_uuid'),
                            Forms\Components\Hidden::make('checkpoint_face_photo_b64'),
                            Forms\Components\Hidden::make('checkpoint_signature'),

                            Forms\Components\View::make('filament.forms.components.qr-checkpoint')
                                ->columnSpanFull(),
                        ]),
                ])
                ->submitAction(new \Illuminate\Support\HtmlString(
                    '<button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-70 cursor-wait"'
                    . ' class="fi-btn fi-btn-size-md fi-color-custom fi-btn-color-primary relative inline-grid grid-flow-col items-center justify-center gap-1.5 rounded-lg bg-custom-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:bg-custom-500 focus-visible:outline-2"'
                    . ' style="--c-400:var(--primary-400);--c-500:var(--primary-500);--c-600:var(--primary-600)">'
                    . '<span wire:loading.remove>💾 Simpan Laporan Patroli</span>'
                    . '<span wire:loading class="flex items-center gap-1.5"><svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path></svg> Menyimpan…</span>'
                    . '</button>'
                ))
                ->columnSpanFull()
            ])
            ->columns(1);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TABLE  ✨ UPGRADED
    // ─────────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // ── 1. KOLOM ABSENSI / AVATAR ────────────────────────────────
                Tables\Columns\ImageColumn::make('face_photo')
                    ->label('')
                    ->circular()
                    ->size(46)
                    ->ring(2)
                    ->defaultImageUrl(fn ($record) =>
                        'https://ui-avatars.com/api/?name=' . urlencode($record->user?->name ?? '?')
                        . '&background=6366f1&color=ffffff&bold=true&size=64'
                    )
                    ->extraImgAttributes(['class' => 'shadow-md ring-indigo-300 dark:ring-indigo-700']),

                // ── 2. WAKTU PATROLI ─────────────────────────────────────────
                Tables\Columns\TextColumn::make('patrol_time')
                    ->label('Waktu Patroli')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->color('gray')
                    ->description(fn ($record) =>
                        ($record->patrol_time?->format('H:i') ?? '')
                        . '  ·  ' . ($record->patrol_time?->diffForHumans() ?? '')
                    )
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('indigo'),

                // ── 3. PETUGAS + SHIFT ───────────────────────────────────────
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Petugas & Shift')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::SemiBold)
                    ->description(fn ($record) => $record->shift
                        ? '🕐 ' . $record->shift->name
                        : '— Shift tidak tercatat'
                    )
                    ->icon('heroicon-m-shield-check')
                    ->iconColor('violet'),

                // ── 4. LOKASI PATROLI ────────────────────────────────────────
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Lokasi')
                    ->sortable()
                    ->searchable()
                    ->weight(FontWeight::Medium)
                    ->icon('heroicon-m-map-pin')
                    ->iconColor('emerald')
                    ->wrap()
                    ->badge()
                    ->color('success'),

                // ── 5. STATUS QR SCAN ────────────────────────────────────────
                Tables\Columns\TextColumn::make('qr_status')
                    ->label('Validasi QR')
                    ->getStateUsing(fn ($record) => $record->qr_scanned_at ? 'Tervalidasi' : 'Belum Scan')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Tervalidasi' ? 'success' : 'danger')
                    ->icon(fn (string $state): string =>
                        $state === 'Tervalidasi'
                            ? 'heroicon-m-qr-code'
                            : 'heroicon-m-exclamation-circle'
                    )
                    ->description(fn ($record) => $record->qr_scanned_at
                        ? '✔ ' . $record->qr_scanned_at->format('d/m/Y H:i')
                        : 'Scan QR belum dilakukan'
                    ),

                // ── 6. STATUS PELANGGARAN (besar & berwarna) ─────────────────
                Tables\Columns\TextColumn::make('status_pelanggaran')
                    ->label('Status')
                    ->getStateUsing(fn ($record) => $record->employee_id
                        ? 'Ada Pelanggaran'
                        : 'Aman'
                    )
                    ->badge()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Large)
                    ->weight(FontWeight::Bold)
                    ->color(fn (string $state): string => match ($state) {
                        'Ada Pelanggaran' => 'danger',
                        default           => 'success',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'Ada Pelanggaran' => 'heroicon-m-exclamation-triangle',
                        default           => 'heroicon-m-shield-check',
                    }),

                // ── 7. PELANGGAR + NIP + GROUP ──────────────────────────────
                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Pelanggar')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->weight(FontWeight::Medium)
                    ->icon(fn ($record) => $record->employee_id ? 'heroicon-m-user-circle' : null)
                    ->iconColor('rose')
                    ->color(fn ($record) => $record->employee_id ? 'danger' : 'gray')
                    ->description(fn ($record) => $record->employee
                        ? '🪪 ' . $record->employee->nip . '   •   Grp ' . $record->employee->shfgroup
                        : null
                    ),

                // ── 8. PELANGGARAN ──────────────────────────────────────────
                Tables\Columns\TextColumn::make('violation.name')
                    ->label('Pelanggaran')
                    ->badge()
                    ->default('Tidak ada')
                    ->color(fn (?string $state): string =>
                        ($state === null || $state === 'Tidak ada') ? 'gray' : 'danger'
                    )
                    ->icon(fn (?string $state): string =>
                        ($state === null || $state === 'Tidak ada')
                            ? 'heroicon-m-check-circle'
                            : 'heroicon-m-no-symbol'
                    )
                    ->wrap(),

                // ── 9. TINDAKAN ─────────────────────────────────────────────
                Tables\Columns\TextColumn::make('action.name')
                    ->label('Tindakan')
                    ->badge()
                    ->default('—')
                    ->color(fn (?string $state): string => match (true) {
                        $state && (str_contains($state, 'SP') || str_contains($state, 'Peringatan')) => 'warning',
                        $state && $state !== '—' => 'info',
                        default => 'gray',
                    })
                    ->icon(fn (?string $state): string => match (true) {
                        $state && (str_contains($state, 'SP') || str_contains($state, 'Peringatan')) => 'heroicon-m-hand-raised',
                        $state && $state !== '—' => 'heroicon-m-check-badge',
                        default => 'heroicon-m-minus',
                    }),

                // ── 10. FOTO TEMUAN ─────────────────────────────────────────
                Tables\Columns\ImageColumn::make('photos')
                    ->label('Bukti Foto')
                    ->circular()
                    ->stacked()
                    ->limit(3)
                    ->limitedRemainingText()
                    ->ring(2)
                    ->overlap(4),

            ])

            // ─────────────────────────────────────────────────────────────────
            // FILTERS  ✨
            // ─────────────────────────────────────────────────────────────────
            ->filters([
                // Filter shift
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'name')
                    ->preload()
                    ->native(false),

                // Filter Group Karyawan
                Tables\Filters\SelectFilter::make('shfgroup')
                    ->label('Group Shift Pelanggar')
                    ->options([
                        'A' => 'Group A',
                        'B' => 'Group B',
                        'C' => 'Group C',
                        'D' => 'Group D',
                    ])
                    ->native(false)
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $q, $group) => $q->whereHas('employee', fn ($q) => $q->where('shfgroup', $group))
                    )),

                // Filter status QR
                Tables\Filters\Filter::make('qr_validated')
                    ->label('Sudah Validasi QR')
                    ->query(fn (Builder $query) => $query->whereNotNull('qr_scanned_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('qr_pending')
                    ->label('Belum Validasi QR')
                    ->query(fn (Builder $query) => $query->whereNull('qr_scanned_at'))
                    ->toggle(),

                // Filter ada pelanggaran
                Tables\Filters\Filter::make('has_violation')
                    ->label('Ada Pelanggaran')
                    ->query(fn (Builder $query) => $query->whereNotNull('employee_id'))
                    ->toggle(),

                // Filter rentang tanggal
                Tables\Filters\Filter::make('patrol_time')
                    ->label('Rentang Tanggal Patroli')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Dari Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Sampai Tanggal')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'],  fn ($q, $date) => $q->whereDate('patrol_time', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('patrol_time', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null)  $indicators[] = Tables\Filters\Indicator::make('Dari: ' . $data['from'])->removeField('from');
                        if ($data['until'] ?? null) $indicators[] = Tables\Filters\Indicator::make('Sampai: ' . $data['until'])->removeField('until');
                        return $indicators;
                    }),

                // Filter petugas
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Petugas')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),

                // Filter lokasi
                Tables\Filters\SelectFilter::make('location_id')
                    ->label('Lokasi Patroli')
                    ->relationship('location', 'name')
                    ->searchable()
                    ->preload()
                    ->native(false),
            ])
            ->filtersLayout(FiltersLayout::AboveContentCollapsible)
            ->filtersFormColumns(3)

            // ─────────────────────────────────────────────────────────────────
            // HEADER ACTIONS
            // ─────────────────────────────────────────────────────────────────
            ->headerActions([
                Tables\Actions\Action::make('scan_qr')
                    ->label('Scan QR Patrol')
                    ->icon('heroicon-o-qr-code')
                    ->color('indigo')
                    ->url(fn () => static::getUrl('scan-qr'))
                    ->button(),
            ])

            // ─────────────────────────────────────────────────────────────────
            // ROW ACTIONS
            // ─────────────────────────────────────────────────────────────────
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->color('info')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->color('warning')
                        ->icon('heroicon-o-pencil-square'),

                    Tables\Actions\Action::make('download_report')
                        ->label('Unduh Laporan')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn ($record) => route('patrol.report.download', $record))
                        ->openUrlInNewTab(),

                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash'),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->color('gray')
                ->button()
                ->label('Aksi'),
            ])

            // ─────────────────────────────────────────────────────────────────
            // BULK ACTIONS
            // ─────────────────────────────────────────────────────────────────
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('export_selected')
                        ->label('Export ke Excel')
                        ->icon('heroicon-o-table-cells')
                        ->color('success')
                        ->action(fn ($records) => null) // ganti dengan logic export sesungguhnya
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])

            // ─────────────────────────────────────────────────────────────────
            // SORTING & MISC
            // ─────────────────────────────────────────────────────────────────
            ->defaultSort('patrol_time', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->extremePaginationLinks()
            ->poll('60s')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->recordClasses(fn ($record) => match (true) {
                // Baris merah terang jika ada pelanggaran & belum scan QR
                $record->employee_id && ! $record->qr_scanned_at
                    => 'bg-rose-50 dark:bg-rose-950/20 border-l-4 border-rose-500',

                // Baris oranye jika ada pelanggaran tapi sudah scan
                (bool) $record->employee_id
                    => 'bg-orange-50 dark:bg-orange-950/20 border-l-4 border-orange-400',

                // Baris kuning jika belum scan QR (tapi tidak ada pelanggaran)
                ! $record->qr_scanned_at
                    => 'bg-amber-50 dark:bg-amber-950/20 border-l-4 border-amber-400',

                // Normal — aman & sudah scan
                default => 'border-l-4 border-transparent',
            })
            ->emptyStateIcon('heroicon-o-shield-exclamation')
            ->emptyStateHeading('Belum Ada Laporan Patroli')
            ->emptyStateDescription('Laporan patroli akan muncul di sini setelah petugas melakukan input atau scan QR.')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Buat Laporan Baru')
                    ->icon('heroicon-m-plus')
                    ->url(static::getUrl('create'))
                    ->button()
                    ->color('primary'),
            ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // QUERY
    // ─────────────────────────────────────────────────────────────────────────
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Add eager loading to prevent N+1 queries
        $query->with([
            'user',
            'employee',
            'shift',
            'location',
            'violation',
            'action',
            'checkpoints',
        ]);

        if (! auth()->user()?->hasRole('super_admin')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    // ─────────────────────────────────────────────────────────────────────────
    public static function getRelations(): array
    {
        return [
            \App\Filament\Admin\Resources\PatrolResource\RelationManagers\CheckpointsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'   => Pages\ListPatrols::route('/'),
            'create'  => Pages\CreatePatrol::route('/create'),
            'view'    => Pages\ViewPatrol::route('/{record}'),
            'edit'    => Pages\EditPatrol::route('/{record}/edit'),
            'scan-qr' => Pages\ScanQrCode::route('/scan-qr'),
        ];
    }
}