<?php

namespace App\Filament\Resources\InviteCodeResource\Pages;

use App\Filament\Resources\InviteCodeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInviteCode extends EditRecord
{
    protected static string $resource = InviteCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
