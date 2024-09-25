<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" class="mb-1" />
                <x-filament::input.wrapper>
                    <x-filament::input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                </x-filament::input.wrapper>
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" class="mb-1" />
                <x-filament::input.wrapper>
                    <x-filament::input id="password" type="password" name="password" required autocomplete="current-password" />
                </x-filament::input.wrapper>
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-filament::input.checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4 gap-4">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-filament::button
                    type="submit"
                >
                    {{ __('Log in') }}
                </x-filament::button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
