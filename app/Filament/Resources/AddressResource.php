<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AddressResource\Pages\CreateAddress;
use App\Filament\Resources\AddressResource\Pages\EditAddress;
use App\Filament\Resources\AddressResource\Pages\ListAddresses;
use App\Models\Address;
use App\Models\Signer;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('address_1')
                    ->required()
                    ->string()
                    ->maxLength(255),
                TextInput::make('address_2')
                    ->nullable()
                    ->string()
                    ->maxLength(255),
                TextInput::make('city')
                    ->required()
                    ->string()
                    ->maxLength(255),
                TextInput::make('state')
                    ->required()
                    ->string()
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->required()
                    ->string()
                    ->maxLength(255),
                Select::make('signer_id')
                    ->label('Signer')
                    ->options(
                        fn () => Signer::where('signable_type', 'player')
                            ->withWhereHas('signable')
                            ->get()
                            ->mapWithKeys(fn ($signer) => [$signer->id => $signer->signable->name])
                    )
                    ->required(),
                DatePicker::make('published_at')
                    ->label('Published At')
                    ->default(now()->subDay())
                    ->nullable(),
                Checkbox::make('rejected')
                    ->label('Reject Address (hide it from review)')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('address_1')
                    ->searchable()
                    ->label('Address 1'),
                TextColumn::make('address_2')
                    ->label('Address 2'),
                TextColumn::make('city'),
                TextColumn::make('state'),
                TextColumn::make('postal_code')
                    ->searchable()
                    ->label('Zip'),
                TextColumn::make('signer.signable.name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                CheckboxColumn::make('rejected'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->filters([
                TernaryFilter::make('rejected'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('publish')
                        ->action(fn (Collection $records) => $records->each->update(['published_at' => now()->startOfDay()])),
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
            'index' => ListAddresses::route('/'),
            'create' => CreateAddress::route('/create'),
            'edit' => EditAddress::route('/{record}/edit'),
        ];
    }
}
