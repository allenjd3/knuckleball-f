<div class="max-w-5xl mx-auto flex gap-8 mt-8">
    <aside class="flex flex-col items-center">
        <div class="rounded-full size-32 overflow-hidden">
            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}" class="object-cover w-full h-full"/>
        </div>

        <div class="font-bold text-center mt-4">
        <div>{{ $user->name }}</div>
        <div class="text-xs space-y-4">
            <div>Joined: {{ $user->created_at?->format('M d, Y') }}</div>
            <div>Following: 5 Followers: 20</div>
        </div>
    </aside>
    <div class="divide-y">
        @foreach ($this->postalMails as $postalMail)
            <div class="p-4 cursor-pointer" @click="window.location.href='{{ route("players.show", $postalMail->player ) }}'">
                <div class="items-center">
                    <div class="font-bold">
                        {{ $postalMail->player->name }}
                    </div>
                    <div class="flex gap-4">
                        <span>Sent: {{ $postalMail->date_sent->format('M d, Y') }}</span>
                        <span>Returned: {{ $postalMail->returned_date?->format('M d, Y') }}</span>
                    </div>
                    <div>
                        {{ $postalMail->comment }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
