<?php

namespace App\Filament\Resources;

use App\Actions\CreateEventFeedItem;
use App\Filament\Resources\EventResource\Pages;
use App\Helpers\Countries;
use App\Models\Event;
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

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')
                ->options([
                    'player_signing' => 'Player Signing',
                    'card_show' => 'Card Show',
                    'comic_con' => 'Comic Con',
                    'memorabilia_show' => 'Memorabilia Show',
                ])
                ->required(),
            Select::make('status')
                ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                ->required(),
            Select::make('country')
                ->options(Countries::list())
                ->default('US')
                ->searchable(),
            TextInput::make('name')->required(),
            Textarea::make('rejection_reason')->nullable()->label('Rejection Reason'),
            Toggle::make('is_featured')->label('Featured'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Event::query()->with(['player', 'user'])->latest())
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->limit(40),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'player_signing' => 'Player Signing',
                        'card_show' => 'Card Show',
                        'comic_con' => 'Comic Con',
                        'memorabilia_show' => 'Memorabilia Show',
                        default => ucfirst($state),
                    })
                    ->color(fn ($state) => match ($state) {
                        'player_signing' => 'info',
                        'card_show' => 'primary',
                        'comic_con' => 'warning',
                        'memorabilia_show' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('player.name')->label('Player')->default('—'),
                TextColumn::make('user.name')->label('Submitted By'),
                TextColumn::make('start_date')->date()->sortable(),
                TextColumn::make('city')->label('City'),
                TextColumn::make('is_featured')->badge()->label('Featured')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->color(fn ($state) => $state ? 'warning' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
                SelectFilter::make('type')
                    ->options([
                        'player_signing' => 'Player Signing',
                        'card_show' => 'Card Show',
                        'comic_con' => 'Comic Con',
                        'memorabilia_show' => 'Memorabilia Show',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Event $record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function (Event $record) {
                        $record->update(['status' => 'approved', 'approved_at' => now()]);
                        CreateEventFeedItem::execute($record);
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Event $record) => $record->status !== 'rejected')
                    ->form([
                        Textarea::make('rejection_reason')->label('Reason')->required(),
                    ])
                    ->action(function (Event $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                    }),
                Action::make('toggle_featured')
                    ->label(fn (Event $record) => $record->is_featured ? 'Unfeature' : 'Feature')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->action(fn (Event $record) => $record->update(['is_featured' => ! $record->is_featured])),
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
                            CreateEventFeedItem::execute($record);
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
            'index' => Pages\ListEvents::route('/'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
