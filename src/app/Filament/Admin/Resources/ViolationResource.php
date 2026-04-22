<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ViolationResource\Pages;
use App\Models\Violation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ViolationResource extends Resource
{
    protected static ?string $model = Violation::class;


    protected static ?string $pluralLabel = 'Jenis Pelanggaran';

    protected static ?int $navigationSort = 5;

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount(['patrols']);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Pelanggaran')
                    ->description('Daftar jenis pelanggaran yang mungkin ditemukan saat patroli')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Pelanggaran')
                            ->placeholder('Contoh: Tidak memakai APD, Merokok di area terlarang')
                            ->helperText('Deskripsi singkat jenis pelanggaran yang bisa terjadi')
                            ->prefixIcon('heroicon-m-exclamation-triangle')
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
                    ->label('Jenis Pelanggaran')
                    ->icon('heroicon-m-exclamation-triangle')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('patrols_count')
                    ->counts('patrols')
                    ->label('Jumlah Kasus')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 10 => 'danger',
                        $state >= 5 => 'warning',
                        default => 'success',
                    })
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
            'index' => Pages\ListViolations::route('/'),
            'create' => Pages\CreateViolation::route('/create'),
            'edit' => Pages\EditViolation::route('/{record}/edit'),
        ];
    }
}
