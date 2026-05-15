<?php

namespace App\Livewire;

use App\Helpers\Countries;
use App\Helpers\States;
use App\Models\CardShop;
use App\Models\Category;
use App\Services\GeocodingService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class SubmitCardShop extends Component
{
    use WithFileUploads;

    public string $name       = '';
    public string $ownerName  = '';
    public string $address    = '';
    public string $city       = '';
    public string $state      = '';
    public string $zipCode    = '';
    public string $phone      = '';
    public string $website    = '';
    public string $country    = 'US';
    public string $description = '';
    public array  $selectedCategories = [];
    public array  $hours = [];
    public $logo = null;
    public $storePhoto = null;

    public bool $submitted = false;

    protected array $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function mount(): void
    {
        foreach ($this->days as $day) {
            $this->hours[$day] = ['open' => '', 'close' => ''];
        }
    }

    #[Computed]
    public function categories()
    {
        return Category::orderBy('name')->get();
    }

    #[Computed]
    public function countries(): array
    {
        return Countries::list();
    }

    #[Computed]
    public function states(): ?array
    {
        return States::forCountry($this->country);
    }

    public function updatedCountry(): void
    {
        $this->state = '';
    }

    public function submit(): void
    {
        $this->validate([
            'name'        => 'required|string|max:255',
            'ownerName'   => 'nullable|string|max:255',
            'city'        => 'required|string|max:255',
            'state'       => ['required', 'string', 'size:2', Rule::in(array_keys(States::forCountry($this->country) ?? []))],
            'country'     => 'required|string|size:2',
            'address'     => 'nullable|string|max:255',
            'zipCode'     => 'nullable|string|max:10',
            'phone'       => 'nullable|string|max:20',
            'website'     => 'nullable|url|max:255',
            'description' => 'nullable|string',
        ]);

        $coords = null;
        $fullAddress = collect([$this->address, $this->city, $this->state, $this->zipCode, $this->country])->filter()->implode(', ');
        if ($fullAddress) {
            $coords = app(GeocodingService::class)->geocode($fullAddress);
        }

        $filteredHours  = collect($this->hours)->filter(fn ($h) => ! empty($h['open']))->toArray();
        $logoPath       = $this->logo       ? $this->logo->store('shops/logos', 'public')   : null;
        $storePhotoPath = $this->storePhoto ? $this->storePhoto->store('shops/photos', 'public') : null;

        $shop = CardShop::create([
            'name'        => $this->name,
            'owner_name'  => $this->ownerName ?: null,
            'address'     => $this->address ?: null,
            'city'        => $this->city,
            'state'       => $this->state ?: null,
            'zip_code'    => $this->zipCode ?: null,
            'country'     => $this->country,
            'phone'       => $this->phone ?: null,
            'website'     => $this->website ?: null,
            'description' => $this->description ?: null,
            'hours'       => $filteredHours ?: null,
            'logo'        => $logoPath,
            'photos'      => $storePhotoPath ? [$storePhotoPath] : null,
            'user_id'     => auth()->id(),
            'status'      => 'pending',
            'latitude'    => $coords['latitude'] ?? null,
            'longitude'   => $coords['longitude'] ?? null,
        ]);

        if ($this->selectedCategories) {
            $shop->categories()->sync($this->selectedCategories);
        }

        $this->submitted = true;
    }

    public function render()
    {
        return view('livewire.submit-card-shop')
            ->layout('layouts.app');
    }
}
