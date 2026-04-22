<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\EmployeeResource\Pages;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;


    protected static ?string $pluralLabel = 'Karyawan';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return cache()->remember('employee_count_badge', 3600, fn () => (string) static::getModel()::count());
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['user']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Karyawan')
                    ->description('Informasi identitas karyawan yang terdaftar')
                    ->icon('heroicon-o-identification')
                    ->schema([
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP (Nomor Induk Pegawai)')
                            ->placeholder('Contoh: NIP00001')
                            ->helperText('Nomor identitas unik karyawan, tidak boleh sama dengan karyawan lain')
                            ->prefixIcon('heroicon-m-hashtag')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Masukkan nama lengkap karyawan')
                            ->helperText('Nama sesuai data kepegawaian')
                            ->prefixIcon('heroicon-m-user')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('shfgroup')
                            ->label('Shift Group')
                            ->helperText('Grup shift karyawan (A, B, C, atau D)')
                            ->options([
                                'A' => 'Group A',
                                'B' => 'Group B',
                                'C' => 'Group C',
                                'D' => 'Group D',
                            ])
                            ->required()
                            ->searchable()
                            ->prefixIcon('heroicon-m-user-group'),
                    ])->columns(2),
                Forms\Components\Section::make('Akun Pengguna')
                    ->description('Link karyawan dengan akun user sistem')
                    ->icon('heroicon-o-user-circle')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Akun User')
                            ->placeholder('Pilih akun user (opsional)')
                            ->helperText('Pilih akun user untuk terhubung dengan karyawan ini')
                            ->relationship('user', 'email')
                            ->getOptionLabelUsing(fn ($value) => \App\Models\User::find($value)?->email ?? '')
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-m-user'),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-user'),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Akun User')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->default('-')
                    ->icon('heroicon-m-user-circle'),
                Tables\Columns\TextColumn::make('shfgroup')
                    ->label('Shift Group')
                    ->icon('heroicon-m-user-group')
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'A' => 'success',
                        'B' => 'info',
                        'C' => 'warning',
                        'D' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('shfgroup')
                    ->label('Shift Group')
                    ->options([
                        'A' => 'Group A',
                        'B' => 'Group B',
                        'C' => 'Group C',
                        'D' => 'Group D',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
