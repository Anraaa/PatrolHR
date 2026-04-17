<?php

namespace App\Filament\Admin\Resources\PatrolResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CheckpointsRelationManager extends RelationManager
{
    protected static string $relationship = 'checkpoints';

    protected static ?string $title = 'Riwayat Checkpoint';

    protected static ?string $icon = 'heroicon-o-map-pin';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('scanned_at')
                    ->label('Waktu Scan')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->description(fn ($record) => $record->scanned_at?->diffForHumans())
                    ->weight(\Filament\Support\Enums\FontWeight::Medium),

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Pos / Lokasi')
                    ->icon('heroicon-m-map-pin'),

                Tables\Columns\ImageColumn::make('face_photo')
                    ->label('Foto')
                    ->circular()
                    ->size(44)
                    ->defaultImageUrl(fn () => 'https://ui-avatars.com/api/?name=?&background=e5e7eb&color=6b7280&size=44'),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Petugas')
                    ->icon('heroicon-m-user'),

                Tables\Columns\TextColumn::make('signature_status')
                    ->label('Paraf')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->signature ? 'Ada' : '—')
                    ->color(fn (string $state) => $state === 'Ada' ? 'success' : 'gray'),
            ])
            ->defaultSort('scanned_at', 'asc')
            ->paginated(false)
            ->emptyStateIcon('heroicon-o-map-pin')
            ->emptyStateHeading('Belum ada checkpoint')
            ->emptyStateDescription('Checkpoint akan muncul setelah petugas scan QR di setiap pos.');
    }
}
