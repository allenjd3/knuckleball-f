<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlayerResource\Pages;
use App\Models\Player;
use App\Models\Team;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
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
                    ->options(fn (Player $record) => Team::get()->pluck('name', 'id')->reject(fn ($team, $id) => $id === $record->team_id )->toArray())
                    ->default(fn (Player $record) => $record->last_team_id),
                Select::make('user_id')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->nullable(),
                DatePicker::make('published_at'),
                DatePicker::make('retired_at')
                    ->default(fn (Player $record) => $record->retired_at),
                FileUpload::make('url')
                    ->directory('avatars')
                    ->avatar(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Player::query()->with(['team', 'lastTeam', 'user', 'media']))
            ->headerActions([
                Action::make('View Players')
                    ->outlined()
                    ->url(route('players.index')),
            ])
            ->columns([
                ImageColumn::make('media.url')->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->url(fn (Player $player) => $player->path())
                    ->sortable()
                    ->searchable(),
                TextColumn::make('retired_at_status')
                    ->label('Player Status')
                    ->state(function ($record) {
                        if (is_null($record->published_at)) {
                            return 'Unpublished';
                        }

                        return !is_null($record->retired_at) && $record->retired_at?->isPast() ? 'Retired' : 'Active';
                    })
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'Unpublished' => 'danger',
                        'Retired' => 'warning',
                        'Active' => 'success',
                    }),
                Tables\Columns\TextColumn::make('team.name'),
                TextColumn::make('lastTeam.name')->label('Last Team')->sortable(),
                TextColumn::make('retired_at')
                    ->state(fn ($record) => $record->retired_at?->format('Y') ?? 'NULL'),
                Tables\Columns\TextColumn::make('published_at')->sortable()->date(),
                Tables\Columns\TextColumn::make('user.name')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at');
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
            'index' => Pages\ListPlayers::route('/'),
            'create' => Pages\CreatePlayer::route('/create'),
            'edit' => Pages\EditPlayer::route('/{record}/edit'),
        ];
    }
}
