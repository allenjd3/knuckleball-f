<?php

namespace App\Filament\Resources;

use App\Enums\AddressRequestReason;
use App\Filament\Resources\AddressRequestResource\Pages\ListAddressRequests;
use App\Models\AddressRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AddressRequestResource extends Resource
{
    protected static ?string $model = AddressRequest::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['user', 'signer.signable']))
            ->columns([
                TextColumn::make('signer.signable.name')
                    ->label('Player')
                    ->searchable()
                    ->url(fn (AddressRequest $record) => $record->signer?->signable?->path()),
                TextColumn::make('user.name')
                    ->label('Requested by')
                    ->searchable(),
                TextColumn::make('reason')
                    ->badge()
                    ->formatStateUsing(fn (AddressRequestReason $state) => $state->label()),
                TextColumn::make('note')
                    ->limit(60)
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Requested')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('fulfilled_at')
                    ->label('Fulfilled')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Open'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('fulfilled_at')
                    ->label('Status')
                    ->nullable()
                    ->placeholder('All requests')
                    ->trueLabel('Fulfilled')
                    ->falseLabel('Open')
                    ->default(false),
                SelectFilter::make('reason')
                    ->options(collect(AddressRequestReason::cases())->mapWithKeys(fn (AddressRequestReason $reason) => [$reason->value => $reason->label()])),
            ])
            ->recordActions([
                Action::make('markFulfilled')
                    ->label('Mark fulfilled')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (AddressRequest $record) => ! $record->isFulfilled())
                    ->action(fn (AddressRequest $record) => $record->fulfill()),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAddressRequests::route('/'),
        ];
    }
}
