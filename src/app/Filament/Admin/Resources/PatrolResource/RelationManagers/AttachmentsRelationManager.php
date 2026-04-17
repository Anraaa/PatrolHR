<?php

namespace App\Filament\Admin\Resources\PatrolResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $title = 'Lampiran Bukti';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->label('Upload File Bukti')
                    ->helperText('Upload foto dokumentasi atau tanda tangan pelanggar (maks 5MB)')
                    ->required()
                    ->directory('patrol-attachments')
                    ->image()
                    ->maxSize(5120)
                    ->imagePreviewHeight('250')
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->label('Jenis Lampiran')
                    ->helperText('Pilih tipe file yang di-upload')
                    ->options([
                        'photo' => 'Foto Dokumentasi',
                        'signature' => 'Tanda Tangan Pelanggar',
                    ])
                    ->required()
                    ->native(false)
                    ->prefixIcon('heroicon-m-photo'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Preview')
                    ->height(80)
                    ->width(80),
                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis Lampiran')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'photo' => 'Foto Dokumentasi',
                        'signature' => 'Tanda Tangan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'photo' => 'info',
                        'signature' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'photo' => 'heroicon-m-photo',
                        'signature' => 'heroicon-m-pencil-square',
                        default => 'heroicon-m-document',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Di-upload Pada')
                    ->dateTime('d M Y H:i'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}
