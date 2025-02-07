<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InviteCodeResource\Pages;
use App\Models\InviteCode;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                TextInput::make('remaining')->numeric(),
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
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListInviteCodes::route('/'),
            'create' => Pages\CreateInviteCode::route('/create'),
            'edit' => Pages\EditInviteCode::route('/{record}/edit'),
        ];
    }
}
