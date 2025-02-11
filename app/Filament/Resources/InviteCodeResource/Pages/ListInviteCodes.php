<?php

namespace App\Filament\Resources\InviteCodeResource\Pages;

use App\Filament\Resources\InviteCodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInviteCodes extends ListRecords
{
    protected static string $resource = InviteCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
