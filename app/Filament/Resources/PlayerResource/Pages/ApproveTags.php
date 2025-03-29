<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use App\Models\PlayerTag;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ApproveTags extends Page implements HasActions, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;

    protected static string $resource = PlayerResource::class;

    protected static string $view = 'filament.resources.player-resource.pages.approve-tags';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PlayerTag::query()->whereNull('approved_at')
            )
            ->columns([
                TextColumn::make('player.name'),
                TextColumn::make('tag.label'),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Approve')
                    ->action(fn (Model $record) => auth()->user()->approveTag($record)),
                Action::make('deny')
                    ->label('Deny')
                    ->action(fn (Model $record) => auth()->user()->rejectTag($record)),
            ])->emptyStateHeading('No Unapproved Tags!');
    }
}
