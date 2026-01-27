<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\InviteCodeResource\Pages\CreateInviteCode;
use App\Filament\Resources\InviteCodeResource\Pages\EditInviteCode;
use App\Filament\Resources\InviteCodeResource\Pages\ListInviteCodes;
use App\Models\InviteCode;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InviteCodeResource extends Resource
{
    protected static ?string $model = InviteCode::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')->required(),
                TextInput::make('remaining')->numeric()->default(0),
                Checkbox::make('is_unlimited')->label('Unlimited Uses?'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code'),
                TextColumn::make('remaining'),
                IconColumn::make('is_unlimited')
                    ->label('Unlimited?')
                    ->boolean(),
                TextColumn::make('register_link')
                    ->label('Register Link (click to copy)')
                    ->copyable()
                    ->copyMessage('Register link copied')
                    ->copyMessageDuration(1500),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => ListInviteCodes::route('/'),
            'create' => CreateInviteCode::route('/create'),
            'edit' => EditInviteCode::route('/{record}/edit'),
        ];
    }
}
