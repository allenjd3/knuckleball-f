<?php

namespace App\Filament\Resources;

use App\Actions\CreateShopSpotlightFeedItem;
use App\Filament\Resources\CardShopResource\Pages;
use App\Helpers\Countries;
use App\Models\CardShop;
use App\Services\GeocodingService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CardShopResource extends Resource
{
    protected static ?string $model = CardShop::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Card Shops';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required(),
            Select::make('status')
                ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                ->required(),
            Select::make('country')
                ->options(Countries::list())
                ->default('US')
                ->searchable(),
            Textarea::make('rejection_reason')->nullable()->label('Rejection Reason'),
            Toggle::make('is_featured')->label('Featured'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(CardShop::query()->with(['user', 'ownerUser'])->latest())
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->limit(40),
                TextColumn::make('city')->label('City'),
                TextColumn::make('state')->label('State'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('user.name')->label('Submitted By'),
                TextColumn::make('ownerUser.name')->label('Owner')->default('—'),
                TextColumn::make('is_featured')->badge()->label('Featured')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),
                TextColumn::make('created_at')->date()->sortable()->label('Submitted'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (CardShop $record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (CardShop $record) {
                        $record->update(['status' => 'approved', 'approved_at' => now()]);
                        CreateShopSpotlightFeedItem::execute($record);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (CardShop $record) => $record->status !== 'rejected')
                    ->form([
                        Textarea::make('rejection_reason')->label('Reason')->required(),
                    ])
                    ->action(function (CardShop $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
                Action::make('toggle_featured')
                    ->label(fn (CardShop $record) => $record->is_featured ? 'Unfeature' : 'Feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn (CardShop $record) => $record->update(['is_featured' => ! $record->is_featured])),
                Action::make('geocode')
                    ->label('Geocode')
                    ->icon('heroicon-o-map-pin')
                    ->color('gray')
                    ->visible(fn (CardShop $record) => ! $record->latitude || ! $record->longitude)
                    ->action(function (CardShop $record) {
                        $address = collect([$record->address, $record->city, $record->state, $record->zip_code, $record->country])
                            ->filter()->implode(', ');
                        $coords = app(GeocodingService::class)->geocode($address);
                        if ($coords) {
                            $record->update(['latitude' => $coords['latitude'], 'longitude' => $coords['longitude']]);
                        }
                    }),
                Action::make('view_claims')
                    ->label('Claims')
                    ->icon('heroicon-o-identification')
                    ->color('gray')
                    ->visible(fn (CardShop $record) => $record->claims()->where('status', 'pending')->exists())
                    ->url(fn (CardShop $record) => static::getUrl('claims', ['record' => $record])),
            ])
            ->bulkActions([
                BulkAction::make('approve_all')
                    ->label('Approve Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->update(['status' => 'approved', 'approved_at' => now()]);
                            CreateShopSpotlightFeedItem::execute($record);
                        }
                    }),
                BulkAction::make('reject_all')
                    ->label('Reject Selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['status' => 'rejected'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCardShops::route('/'),
            'edit' => Pages\EditCardShop::route('/{record}/edit'),
            'claims' => Pages\ManageShopClaims::route('/{record}/claims'),
        ];
    }
}
