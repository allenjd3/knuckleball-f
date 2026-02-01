<?php

namespace App\Filament\Resources\PlayerResource\Pages;

use App\Filament\Resources\PlayerResource;
use App\Models\SignerTag;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Resources\Pages\Page;
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

    protected string $view = 'filament.resources.player-resource.pages.approve-tags';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SignerTag::query()->whereNull('approved_at')
            )
            ->columns([
                TextColumn::make('signer.signable.name')
                    ->url(fn (Model $record) => $record->signer->signable->path()),
                TextColumn::make('tag.label'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->action(fn (Model $record) => auth()->user()->approveTag($record)),
                Action::make('deny')
                    ->label('Deny')
                    ->action(fn (Model $record) => auth()->user()->rejectTag($record)),
            ])->emptyStateHeading('No Unapproved Tags!');
    }
}
