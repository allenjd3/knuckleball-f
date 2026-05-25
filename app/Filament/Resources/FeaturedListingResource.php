<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedListingResource\Pages\ListFeaturedListings;
use App\Models\FeaturedListing;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FeaturedListingResource extends Resource
{
    protected static ?string $model = FeaturedListing::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-star';
    protected static ?string $navigationLabel = 'Featured Events';
    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event.name')
                    ->label('Event')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('user.name')
                    ->label('Purchaser')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plan_type')
                    ->label('Plan')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('amount_paid')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),

                TextColumn::make('starts_at')
                    ->label('Started')
                    ->date('M j, Y')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date('M j, Y')
                    ->placeholder('Subscription-managed')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (FeaturedListing $record): string => $record->isActive() ? 'active' : 'expired')
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'gray',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('plan_type')
                    ->options([
                        'one_time' => 'One-time',
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                    ]),
            ])
            ->recordActions([
                Action::make('extend')
                    ->label('Extend 30 days')
                    ->icon('heroicon-o-arrow-right')
                    ->action(function (FeaturedListing $record): void {
                        $record->update([
                            'expires_at' => ($record->expires_at ?? now())->addDays(30),
                        ]);
                        $record->event?->update(['is_featured' => true]);
                    })
                    ->requiresConfirmation(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeaturedListings::route('/'),
        ];
    }
}
