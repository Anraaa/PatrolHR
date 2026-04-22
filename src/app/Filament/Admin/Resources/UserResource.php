<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $label = 'Pengguna';

    protected static ?string $pluralLabel = 'Pengguna';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = -2;

    public static function getNavigationBadge(): ?string
    {
        return cache()->remember('user_count_badge', 3600, fn () => (string) static::getModel()::count());
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['roles']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'roles.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Role' => $record->roles->pluck('name')->implode(', '),
            'Email' => $record->email,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Informasi Akun')
                    ->description('Data dasar pengguna sistem')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Masukkan nama lengkap pengguna')
                            ->helperText('Nama yang akan ditampilkan di sistem')
                            ->prefixIcon('heroicon-m-user')
                            ->minLength(2)
                            ->maxLength(255)
                            ->columnSpan('full')
                            ->required(),
                        Forms\Components\FileUpload::make('avatar_url')
                            ->label('Foto Profil')
                            ->helperText('Upload foto profil (maks 2MB, format JPG/PNG)')
                            ->image()
                            ->optimize('webp')
                            ->imageEditor()
                            ->imagePreviewHeight('250')
                            ->panelAspectRatio('7:2')
                            ->panelLayout('integrated')
                            ->columnSpan('full'),
                        Forms\Components\TextInput::make('email')
                            ->label('Alamat Email')
                            ->placeholder('contoh@perusahaan.com')
                            ->helperText('Email digunakan untuk login ke sistem')
                            ->required()
                            ->prefixIcon('heroicon-m-envelope')
                            ->columnSpan('full')
                            ->email(),
                        Forms\Components\Select::make('role')
                            ->label('Peran Pengguna')
                            ->helperText('Tentukan hak akses pengguna di sistem')
                            ->prefixIcon('heroicon-m-shield-check')
                            ->options([
                                'admin' => 'Admin - Akses penuh ke semua fitur',
                                'manager' => 'Manager - Menerima laporan & notifikasi',
                                'pic' => 'PIC - Petugas patroli lapangan',
                            ])
                            ->required()
                            ->native(false)
                            ->columnSpan('full'),
                    ])->columns(2),

                Forms\Components\Section::make('Keamanan')
                    ->description('Pengaturan kata sandi akun')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Kata Sandi')
                            ->placeholder('Minimal 8 karakter')
                            ->helperText(fn (string $context): string => $context === 'create'
                                ? 'Buat kata sandi yang kuat untuk akun ini'
                                : 'Kosongkan jika tidak ingin mengubah kata sandi')
                            ->password()
                            ->revealable()
                            ->confirmed()
                            ->columnSpan(1)
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Kata Sandi')
                            ->placeholder('Ketik ulang kata sandi')
                            ->helperText('Harus sama persis dengan kata sandi di atas')
                            ->required(fn (string $context): bool => $context === 'create')
                            ->columnSpan(1)
                            ->password()
                            ->revealable(),
                    ])->columns(2),

                Forms\Components\Section::make('Hak Akses (Shield)')
                    ->description('Role dari Filament Shield untuk kontrol permission detail')
                    ->icon('heroicon-o-key')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Shield Roles')
                            ->helperText('Pilih satu atau lebih role untuk mengatur permission akses menu & fitur')
                            ->required()
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload(),
                    ])
                    ->columns(1)
                    ->collapsible(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('avatar_url')
                    ->defaultImageUrl(url('https://www.gravatar.com/avatar/64e1b8d34f425d19e1ee2ea7236d3028?d=mp&r=g&s=250'))
                    ->label('Foto')
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Lengkap')
                    ->sortable()
                    ->searchable()
                    ->description(fn ($record) => $record->email),
                Tables\Columns\TextColumn::make('role')
                    ->label('Peran')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'manager' => 'warning',
                        'pic' => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'admin' => 'Admin',
                        'manager' => 'Manager',
                        'pic' => 'PIC',
                        default => $state,
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Shield Roles')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Sejak')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Peran')
                    ->options([
                        'admin' => 'Admin',
                        'manager' => 'Manager',
                        'pic' => 'PIC',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
