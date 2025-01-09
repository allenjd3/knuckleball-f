<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerResource\Pages\CreatePlayer;
use App\Filament\Resources\PlayerResource\Pages\EditPlayer;
use App\Filament\Resources\PlayerResource\Pages\ListPlayers;
use App\Models\Player;
use App\Models\Team;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')->required(),
                Select::make('team_id')
                    ->relationship(name: 'team', titleAttribute: 'name')
                    ->required(),
                Select::make('last_team_id')
                    ->label('Last Played For')
                    ->options(fn (?Player $record) => Team::get()->pluck('name', 'id')->reject(fn ($team, $id) => $id === ($record?->team_id ?? 0))->toArray())
                    ->default(fn (?Player $record) => $record?->last_team_id),
                Select::make('user_id')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->nullable(),
                DatePicker::make('published_at'),
                DatePicker::make('retired_at')
                    ->default(fn (?Player $record) => $record?->retired_at),
                FileUpload::make('url')
                    ->directory('avatars')
                    ->avatar(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('View Players')
                    ->outlined()
                    ->url(route('players.index')),
            ])
            ->columns([
                ImageColumn::make('media.url')->circular(),
                TextColumn::make('name')
                    ->url(fn (Player $player) => $player->path())
                    ->sortable()
                    ->searchable(),
                TextColumn::make('retired_at_status')
                    ->label('Player Status')
                    ->state(function ($record) {
                        if (is_null($record->published_at)) {
                            return 'Unpublished';
                        }
                        return ! is_null($record->retired_at) && $record->retired_at?->isPast() ? 'Retired' : 'Active';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Unpublished' => 'danger',
                        'Retired' => 'warning',
                        'Active' => 'success',
                    }),
                TextColumn::make('team.name'),
                TextColumn::make('lastTeam.name')->label('Last Team')->sortable(),
                TextColumn::make('retired_at')
                    ->state(fn ($record) => $record->retired_at?->format('Y') ?? 'NULL'),
                TextColumn::make('published_at')->sortable()->date(),
                TextColumn::make('user.name')->searchable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlayers::route('/'),
            'create' => CreatePlayer::route('/create'),
            'edit' => EditPlayer::route('/{record}/edit'),
        ];
    }
}
