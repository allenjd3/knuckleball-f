<?php

namespace App\Filament\Resources\FeaturedListingResource\Pages;

use App\Filament\Resources\FeaturedListingResource;
use Filament\Resources\Pages\ListRecords;

class ListFeaturedListings extends ListRecords
{
    protected static string $resource = FeaturedListingResource::class;
}
