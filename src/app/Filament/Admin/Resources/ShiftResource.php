<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ShiftResource\Pages;
use App\Models\Shift;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShiftResource extends Resource
{
    protected static ?string $model = Shift::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?string $label = 'Shift Kerja';

    protected static ?string $pluralLabel = 'Shift Kerja';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Shift')
                    ->description('Pengaturan jadwal shift kerja karyawan')
                    ->icon('heroicon-o-clock')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Shift')
                            ->placeholder('Contoh: Shift 1 (06:00 - 14:00)')
                            ->helperText('Berikan nama shift beserta rentang jam kerja agar mudah dikenali')
                            ->prefixIcon('heroicon-m-clock')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ]),
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
                    ->label('Nama Shift')
                    ->icon('heroicon-m-clock')
                    ->searchable()
                    ->sortable(),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShifts::route('/'),
            'create' => Pages\CreateShift::route('/create'),
            'edit' => Pages\EditShift::route('/{record}/edit'),
        ];
    }
}
