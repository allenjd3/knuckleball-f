<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages\CreateCard;
use App\Filament\Resources\CardResource\Pages\EditCard;
use App\Filament\Resources\CardResource\Pages\ListCards;
use App\Models\Card;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('manufacturer')->maxLength(255)->required(),
                TextInput::make('series')->maxLength(255)->required(),
                TextInput::make('year')->numeric()->required(),
                TextInput::make('number')->nullable(),
                TextInput::make('variation')->nullable(),
                FileUpload::make('url')
                    ->required()
                    ->directory('cards')
                    ->image(),
                Checkbox::make('dmca_certification')
                    ->label('I certify that I own this image or have a legitimate license/permission to share it. I understand that Knuckleball follows a strict DMCA policy and will remove infringing content and terminate repeat infringer accounts.')
                    ->rules(['accepted'])
                    ->dehydrated(false),
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
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
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
