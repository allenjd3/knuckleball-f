<?php

namespace App\Forms\Schema;

use App\Actions\CreateFeedItem;
use App\Models\FeeMaterial;
use App\Models\InPersonAutograph;
use App\Models\Signer;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Arr;

class InPersonAutographForm
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
            DatePicker::make('obtained_date')
                ->label('Date')
                ->required()
                ->maxDate(now()),
            Select::make('fee_material_id')
                ->label('Item signed')
                ->options(fn () => FeeMaterial::pluck('name', 'id')->toArray())
                ->preload()
                ->searchable()
                ->createOptionModalHeading('Create Item')
                ->createOptionForm([
                    TextInput::make('name'),
                ])
                ->createOptionUsing(fn (array $data) => FeeMaterial::create($data)->id),
            TextInput::make('location')
                ->label('Where')
                ->placeholder('e.g. National Card Show, stage door')
                ->maxLength(255),
            Toggle::make('is_declined')
                ->label('They declined to sign / no-show?'),
            Textarea::make('comment')->maxLength(255),
            FileUpload::make('photos')
                ->label('Photos')
                ->multiple()
                ->image()
                ->directory('in-person-autographs')
                ->nullable(),
        ];
    }

    public static function shouldBeVisibleFor(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->can('create', InPersonAutograph::class);
    }

    public function using($data)
    {
        $photos = Arr::pull($data, 'photos', []);

        $autograph = request()->user()
            ->inPersonAutographs()
            ->create(array_merge($data, ['signer_id' => $this->signer->id]));

        foreach ($photos as $photo) {
            $autograph->media()->create(['url' => $photo]);
        }

        // A declined/no-show isn't something to celebrate in the feed —
        // it still counts toward the response rate, it just doesn't post.
        if (! $autograph->is_declined) {
            CreateFeedItem::execute(feedItem: $autograph, comment: $autograph->comment);
        }

        return $autograph;
    }
}
