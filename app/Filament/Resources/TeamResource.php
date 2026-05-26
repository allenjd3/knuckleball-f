<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamResource\Pages\CreateTeam;
use App\Filament\Resources\TeamResource\Pages\EditTeam;
use App\Filament\Resources\TeamResource\Pages\ListTeams;
use App\Models\Team;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name'),
                Select::make('category_id')
                    ->relationship(name: 'category', titleAttribute: 'name')
                    ->nullable(),
                DatePicker::make('published_at')->default(now()->subDay()),
                FileUpload::make('url')
                    ->directory('teams')
                    ->visibility('public')
                    ->avatar(),
                Checkbox::make('dmca_certification')
                    ->label('I certify that I own this image or have a legitimate license/permission to share it. I understand that Knuckleball follows a strict DMCA policy and will remove infringing content and terminate repeat infringer accounts.')
                    ->rules(fn (Get $get): array => filled($get('url')) ? ['accepted'] : [])
                    ->dehydrated(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('published_at')->sortable()->date(),
                CheckboxColumn::make('rejected'),
                TextColumn::make('category.name'),
            ])
            ->filters([
                TernaryFilter::make('rejected'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->headerActions([
                Action::make('View Teams')
                    ->outlined()
                    ->url(route('teams.index')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeams::route('/'),
            'create' => CreateTeam::route('/create'),
            'edit' => EditTeam::route('/{record}/edit'),
        ];
    }
}
