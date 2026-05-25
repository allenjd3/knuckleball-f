<?php

namespace App\Filament\Resources\CardShopResource\Pages;

use App\Filament\Resources\CardShopResource;
use App\Models\ShopClaim;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManageShopClaims extends ManageRelatedRecords
{
    protected static string $resource = CardShopResource::class;
    protected static string $relationship = 'claims';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    public static function getNavigationLabel(): string
    {
        return 'Claims';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Claimant')->searchable(),
                TextColumn::make('user.email')->label('Email'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('verification_notes')->label('Notes')->limit(60)->default('—'),
                TextColumn::make('reviewed_at')->label('Reviewed')->dateTime()->placeholder('—'),
                TextColumn::make('created_at')->label('Submitted')->date()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (ShopClaim $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function (ShopClaim $record) {
                        $record->update(['status' => 'approved', 'reviewed_at' => now()]);
                        $record->cardShop->update(['owner_user_id' => $record->user_id]);
                        // Reject any other pending claims for this shop
                        $record->cardShop->claims()
                            ->where('id', '!=', $record->id)
                            ->where('status', 'pending')
                            ->update(['status' => 'rejected', 'reviewed_at' => now()]);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (ShopClaim $record) => $record->status === 'pending')
                    ->form([
                        Textarea::make('rejection_reason')->label('Reason')->required(),
                    ])
                    ->action(function (ShopClaim $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'reviewed_at' => now(),
                        ]);
                    }),
            ]);
    }
}
