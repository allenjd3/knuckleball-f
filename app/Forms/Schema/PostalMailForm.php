<?php

namespace App\Forms\Schema;

use App\Actions\CreateFeedItem;
use App\Enums\SendMethod;
use App\Models\FeeMaterial;
use App\Models\PostalMail;
use App\Models\Signer;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\DB;

class PostalMailForm
{
    public function __construct(
        public Signer $signer,
    ) {}

    public static function for(Signer $signer)
    {
        return new self($signer);
    }

    public static function schema(): array
    {
        return [
            Radio::make('method')
                ->label('How did you send it?')
                ->options(SendMethod::options())
                ->default(SendMethod::Mail->value)
                ->inline()
                ->required(),
            DatePicker::make('date_sent')->required(),
            DatePicker::make('returned_date'),
            Select::make('fee_material_id')
                ->label('Material')
                ->required()
                ->options(fn () => FeeMaterial::pluck('name', 'id')->toArray())
                ->preload()
                ->searchable()
                ->createOptionModalHeading('Create Item')
                ->createOptionForm([
                    TextInput::make('name'),
                ])
                ->createOptionUsing(fn (array $data) => FeeMaterial::create($data)->id),
            ...FailedReturnForm::schema(),
            Textarea::make('comment')->maxLength(255),
        ];
    }

    public static function shouldBeVisibleFor(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('create', PostalMail::class);
    }

    public function using($data)
    {
        return DB::transaction(function () use ($data) {
            $postalMail = request()->user()
                ->postalMails()
                ->create(array_merge(FailedReturnForm::withoutFormOnlyFields($data), ['signer_id' => $this->signer->id]));

            $postalMail->feeMaterials()->attach(data_get($data, 'fee_material_id'));

            CreateFeedItem::execute(feedItem: $postalMail, comment: $postalMail->comment);

            FailedReturnForm::handleAddressRequest($postalMail, $data);

            return $postalMail;
        });
    }
}
