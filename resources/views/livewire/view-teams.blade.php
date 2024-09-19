<div class="max-w-7xl py-16 mx-auto">
    <h1 class="text-3xl">Teams</h1>
    @foreach($this->teams as $team)
        <div>{{ $team->name }}</div>
    @endforeach

</div>
