<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages\CreateCard;
use App\Filament\Resources\CardResource\Pages\EditCard;
use App\Filament\Resources\CardResource\Pages\ListCards;
use App\Models\Card;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('manufacturer')->maxLength(255)->required(),
                TextInput::make('series')->maxLength(255)->required(),
                TextInput::make('year')->numeric()->required(),
                TextInput::make('number')->nullable(),
                TextInput::make('variation')->nullable(),
                FileUpload::make('url')
                    ->required()
                    ->directory('cards')
                    ->image(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('media.url')->size(200),
                TextColumn::make('manufacturer')->sortable()->searchable(),
                TextColumn::make('series')->sortable()->searchable(),
                TextColumn::make('year')->sortable(),
                TextColumn::make('number'),
                TextColumn::make('variation'),
            ])
            ->filters([])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => ListCards::route('/'),
            'create' => CreateCard::route('/create'),
            'edit' => EditCard::route('/{record}/edit'),
        ];
    }
}
