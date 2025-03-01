<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImportDataResource\Pages\CreateImportData;
use App\Filament\Resources\ImportDataResource\Pages\EditImportData;
use App\Filament\Resources\ImportDataResource\Pages\ListImportData;
use App\Jobs\ProcessPlayerData;
use App\Models\ImportData;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImportDataResource extends Resource
{
    protected static ?string $model = ImportData::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                KeyValue::make('data'),
                TextInput::make('errors')->nullable(),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('data'),
                TextColumn::make('errors'),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->headerActions([
                Action::make('import')
                    ->label('Start Import')
                    ->requiresConfirmation()
                    ->action(fn () => ProcessPlayerData::dispatch()),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListImportData::route('/'),
            'create' => CreateImportData::route('/create'),
            'edit' => EditImportData::route('/{record}/edit'),
        ];
    }
}
