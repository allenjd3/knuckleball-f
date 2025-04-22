<x-mail::message>
# Moderation report

There are currently
{{ $unapprovedAddressesCount }} addresses that need review- <a href="{{ route('filament.cp.resources.addresses.index', ['tableSortColumn' => 'published_at', 'tableSortDirection' => 'asc', 'tableFilters' => ['rejected' => ['value' => 0]]]) }}">Review</a>

{{ $unapprovedTeamsCount }} teams that need review- <a href="{{ route('filament.cp.resources.teams.index', ['tableSortColumn' => 'published_at', 'tableSortDirection' => 'asc', 'tableFilters' => ['rejected' => ['value' => 0]]]) }}">Review</a>

{{ $unapprovedPlayersCount }} players that need review- <a href="{{ route('filament.cp.resources.players.index', ['tableSortColumn' => 'published_at', 'tableSortDirection' => 'asc', 'tableFilters' => ['rejected' => ['value' => 0]]]) }}">Review</a>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
