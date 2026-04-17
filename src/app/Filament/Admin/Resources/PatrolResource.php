<?php

namespace App\Filament\Admin\Resources;

use App\Forms\Components\SignaturePad;
use App\Filament\Admin\Resources\PatrolResource\Pages;
use App\Models\Patrol;
use App\Models\Employee;
use App\Models\Department;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PatrolResource extends Resource
{
    protected static ?string $model = Patrol::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

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
    // FORM
    // ─────────────────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // PEMBUNGKUS WIZARD (Penting untuk mencegah error isContained)
                Forms\Components\Wizard::make([

                    // ── STEP 1: Info Patroli ──────────────────────────────
                    Forms\Components\Wizard\Step::make('Info Patroli')
                        ->icon('heroicon-o-clock')
                        ->description('Waktu, shift, dan petugas')
                        ->schema([

                            // ── Waktu & Shift ────────────────────────────
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

                            // ── Petugas Pelapor ──────────────────────────
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

                    // ── STEP 2: Identitas & Pelanggaran ──────────────────
                    Forms\Components\Wizard\Step::make('Karyawan & Pelanggaran')
                        ->icon('heroicon-o-identification')
                        ->description('Apakah ada temuan atau pelanggar?')
                        ->schema([

                            // ── Toggle: Ada Temuan? ───────────────────────
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

                            // ── Info: Tidak Ada Temuan ────────────────────
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
                                            $state ? $set('_dept_display', Employee::with('department')->find($state)?->department?->name) : $set('_dept_display', null)
                                        ),

                                    Forms\Components\Placeholder::make('_dept_display')
                                        ->label('Departemen')
                                        ->content(function (Get $get) {
                                            if (!$id = $get('employee_id')) return '— Pilih karyawan dulu';
                                            return Employee::with('department')->find($id)?->department?->name ?? '-';
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

                    // ── STEP 3: Checkpoint & Absensi ─────────────────────
                    Forms\Components\Wizard\Step::make('Checkpoint & Absensi')
                        ->icon('heroicon-o-qr-code')
                        ->description('Scan QR lokasi, foto muka, dan tanda tangan')
                        ->schema([
                            // Hidden fields — diisi oleh Alpine.js/Livewire dispatch
                            Forms\Components\Hidden::make('checkpoint_location_id'),
                            Forms\Components\Hidden::make('checkpoint_uuid'),
                            Forms\Components\Hidden::make('checkpoint_face_photo_b64'),
                            Forms\Components\Hidden::make('checkpoint_signature'),

                            // Interactive QR + GPS + Photo + Signature UI
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
    // TABLE
    // ─────────────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('face_photo')
                    ->label('Absen')
                    ->circular()
                    ->size(44)
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=?&background=e5e7eb&color=6b7280&size=44'),

                Tables\Columns\TextColumn::make('patrol_time')
                    ->label('Waktu Patroli')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn ($record) => $record->patrol_time?->diffForHumans())
                    ->weight(\Filament\Support\Enums\FontWeight::Medium),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Petugas')
                    ->sortable()
                    ->description(fn ($record) => $record->shift?->name),

                Tables\Columns\TextColumn::make('status_pelanggaran')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->employee_id ? 'Ada Pelanggaran' : 'Tidak Ada Pelanggaran')
                    ->color(fn (string $state): string => $state === 'Ada Pelanggaran' ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('employee.name')
                    ->label('Karyawan Pelanggar')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->description(fn ($record) => $record->employee
                        ? ($record->employee->nip . ' · ' . $record->employee?->department?->name)
                        : null
                    ),

                Tables\Columns\TextColumn::make('violation.name')
                    ->label('Pelanggaran')
                    ->badge()
                    ->default('Tidak ada pelanggaran')
                    ->color(fn (?string $state): string =>
                        ($state === null || $state === 'Tidak ada pelanggaran') ? 'success' : 'danger'
                    ),

                Tables\Columns\TextColumn::make('action.name')
                    ->label('Tindakan')
                    ->badge()
                    ->default('—')
                    ->color(fn (?string $state): string => match (true) {
                        $state && (str_contains($state, 'SP') || str_contains($state, 'Peringatan')) => 'danger',
                        $state && $state !== '—' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\ImageColumn::make('photos')
                    ->label('Foto Temuan')
                    ->circular()
                    ->stacked()
                    ->limit(3),
            ])
            ->defaultSort('patrol_time', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('shift_id')
                    ->label('Shift')
                    ->relationship('shift', 'name'),
                
                Tables\Filters\SelectFilter::make('department')
                    ->label('Departemen')
                    ->options(fn () => Department::pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $q, $deptId) => $q->whereHas('employee', fn ($q) => $q->where('dept_id', $deptId))
                    )),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getEloquentQuery();

        // Non-admin users only see their own patrol records
        if (! auth()->user()?->hasRole('super_admin')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //\App\Filament\Admin\Resources\PatrolResource\RelationManagers\AttachmentsRelationManager::class,
            \App\Filament\Admin\Resources\PatrolResource\RelationManagers\CheckpointsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPatrols::route('/'),
            'create' => Pages\CreatePatrol::route('/create'),
            'view'   => Pages\ViewPatrol::route('/{record}'),
            'edit'   => Pages\EditPatrol::route('/{record}/edit'),
        ];
    }
}