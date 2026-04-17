<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActionResource\Pages;
use App\Models\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ActionResource extends Resource
{
    protected static ?string $model = Action::class;

    protected static ?string $navigationIcon = 'heroicon-o-hand-raised';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 6;

    protected static ?string $label = 'Tindakan';

    protected static ?string $pluralLabel = 'Tindakan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Tindakan')
                    ->description('Jenis tindakan/sanksi yang diberikan kepada pelanggar')
                    ->icon('heroicon-o-hand-raised')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Tindakan')
                            ->placeholder('Contoh: Pengarahan, Teguran Lisan, Surat Peringatan 1')
                            ->helperText('Bentuk tindakan/sanksi yang diberikan saat menemukan pelanggaran')
                            ->prefixIcon('heroicon-m-hand-raised')
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
                    ->label('Nama Tindakan')
                    ->icon('heroicon-m-hand-raised')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('patrols_count')
                    ->counts('patrols')
                    ->label('Digunakan')
                    ->badge()
                    ->color('info')
                    ->suffix(' kali')
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
            'index' => Pages\ListActions::route('/'),
            'create' => Pages\CreateAction::route('/create'),
            'edit' => Pages\EditAction::route('/{record}/edit'),
        ];
    }
}
