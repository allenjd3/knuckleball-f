<x-guest-layout>
    @pushOnce('header-scripts')
        <x-turnstile.scripts />
    @endPushOnce

    <x-authentication-card>
        <x-slot name="logo">
            <h3 style="--color-600: var(--primary-600)" class="text-[26px] tracking-tight text-custom-600">Knuckleball</h3>
        </x-slot>

        <x-validation-errors class="mb-4" />

        <form
            method="POST"
            action="{{ route('register') }}"
            x-data="inviteCode(@js(old('code')))"
            x-init="getParam()"
        >
            @csrf
            <div>
                <x-label for="code" value="{{ __('Invite Code') }}" class="mb-1" />
                <x-filament::input.wrapper>
                    <x-filament::input x-ref="code" id="code" type="text" x-model="code" name="code" required />
                </x-filament::input.wrapper>
            </div>

            <div>
                <x-label for="name" value="{{ __('Name') }}" class="mb-1" />
                <x-filament::input.wrapper>
                    <x-filament::input x-ref="name" id="name" type="text" name="name" :value="old('name')" required autocomplete="name" />
                </x-filament::input.wrapper>
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" class="mb-1" />
                <x-filament::input.wrapper>
                    <x-filament::input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
                </x-filament::input.wrapper>
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" class="mb-1" />
                <x-filament::input.wrapper>
                    <x-filament::input id="password" type="password" name="password" required autocomplete="new-password" />
                </x-filament::input.wrapper>
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" class="mb-1" />
                <x-filament::input.wrapper>
                    <x-filament::input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                </x-filament::input.wrapper>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif
            <x-turnstile />

            <div class="flex items-center justify-end mt-4 gap-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('dmca') }}" target="_blank">
                    {{ __('DMCA Policy') }}
                </a>
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-filament::button type="submit">
                    {{ __('Register') }}
                </x-filament::button>
            </div>
        </form>
        @vite('resources/ts/invitecode.ts')
    </x-authentication-card>
</x-guest-layout>
