<?php

namespace App\Livewire;

use App\Models\AddressRequest;
use App\Models\Player;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class MostWantedAddresses extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.most-wanted-addresses')
            ->layout('layouts.app');
    }

    /**
     * Living players with open address requests, most requested first.
     */
    #[Computed]
    public function players(): Paginator
    {
        $openRequests = fn (Builder $query) => $query->whereNull('address_requests.fulfilled_at');

        return Player::query()
            ->whereNull('deceased_at')
            ->whereHas('signer.addressRequests', $openRequests)
            ->withCount(['signer as open_requests_count' => fn (Builder $signer) => $signer
                ->join('address_requests', 'address_requests.signer_id', '=', 'signers.id')
                ->whereNull('address_requests.fulfilled_at')])
            ->with(['team', 'media'])
            ->orderByDesc('open_requests_count')
            ->orderBy('name')
            ->simplePaginate(25);
    }

    #[Computed]
    public function totalOpenRequests(): int
    {
        return AddressRequest::query()->open()->count();
    }
}
