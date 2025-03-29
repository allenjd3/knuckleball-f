<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InviteCodeResource\Pages\CreateInviteCode;
use App\Filament\Resources\InviteCodeResource\Pages\EditInviteCode;
use App\Filament\Resources\InviteCodeResource\Pages\ListInviteCodes;
use App\Models\InviteCode;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction as ActionsEditAction;
use Filament\Tables\Columns\BooleanColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InviteCodeResource extends Resource
{
    protected static ?string $model = InviteCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
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
                BooleanColumn::make('is_unlimited')->label('Unlimited?'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionsEditAction::make(),
            ])
            ->bulkActions([
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
