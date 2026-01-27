<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\ImportAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Imports\ImportDataImporter;
use App\Filament\Resources\PlayerResource\Pages\ApproveTags;
use App\Filament\Resources\PlayerResource\Pages\CreatePlayer;
use App\Filament\Resources\PlayerResource\Pages\EditPlayer;
use App\Filament\Resources\PlayerResource\Pages\ListPlayers;
use App\Models\Player;
use App\Models\Team;
use App\Support\Collections\PlayerCollection;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PlayerResource extends Resource
{
    protected static ?string $model = Player::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                Textarea::make('note')->nullable()->maxLength(500),
                Select::make('team_id')
                    ->relationship(name: 'team', titleAttribute: 'name')
                    ->required(),
                Select::make('last_team_id')
                    ->label('Last Played For')
                    ->options(fn (?Player $record) => Team::published()
                        ->where('rejected', false)
                        ->get()
                        ->pluck('name', 'id')
                        ->reject(fn ($team, $id) => $id === ($record?->team_id ?? 0))
                        ->toArray()
                    )
                    ->default(fn (?Player $record) => $record?->last_team_id),
                Select::make('user_id')
                    ->relationship(name: 'user', titleAttribute: 'name')
                    ->nullable(),
                DatePicker::make('published_at'),
                DatePicker::make('retired_at')
                    ->default(fn (?Player $record) => $record?->retired_at),
                DatePicker::make('deceased_at'),
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
                ImportAction::make()
                    ->importer(ImportDataImporter::class),
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
                CheckboxColumn::make('rejected'),
                TextColumn::make('published_at')->sortable()->date(),
                TextColumn::make('user.name')->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->filters([
                TernaryFilter::make('rejected'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->icon('heroicon-m-check')
                        ->action(fn (PlayerCollection $records) => $records->publishAll()),
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
            'approve-tags' => ApproveTags::route('/approve-tags'),
        ];
    }
}
