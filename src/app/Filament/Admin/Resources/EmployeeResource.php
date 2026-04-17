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

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $label = 'Karyawan';

    protected static ?string $pluralLabel = 'Karyawan';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
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
                        Forms\Components\Select::make('dept_id')
                            ->label('Departemen')
                            ->helperText('Unit kerja tempat karyawan bertugas')
                            ->relationship('department', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-m-building-office')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama Departemen Baru')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])->columns(2),
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
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Departemen')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar Pada')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('dept_id')
                    ->label('Departemen')
                    ->relationship('department', 'name'),
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
