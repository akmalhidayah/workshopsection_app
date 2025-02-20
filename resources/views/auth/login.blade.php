<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="flex justify-center">
            <img src="{{ asset('images/logo-bms.png') }}" alt="Logo" class="w-50 h-40 fill-current text-gray-500">
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4 relative">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full pr-10"
                          type="password"
                          name="password"
                          required autocomplete="current-password" />

        <!-- Tombol Mata -->
        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-3 flex items-center justify-center text-sm leading-5">
            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 3C5.455 3 1.743 6.423.5 10c1.243 3.577 4.955 7 9.5 7s8.257-3.423 9.5-7c-1.243-3.577-4.955-7-9.5-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 3 0 000 6z" />
            </svg>
        </button>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("password");
            var eyeIcon = document.getElementById("eyeIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.innerHTML = `<path d="M2.1 5a10.92 10.92 0 0115.8 0l-1.5 1.5A8.93 8.93 0 0010 4c-2.2 0-4.2.8-5.7 2.2L2.1 5zm1.4 1.4L4.9 7.7c-1.4 1.4-2.3 3.3-2.3 5.3s.8 4 2.3 5.3l1.5-1.5A8.93 8.93 0 0010 16a8.93 8.93 0 006.3-2.2l1.5 1.5c1.4-1.4 2.3-3.3 2.3-5.3s-.8-4-2.3-5.3l-1.5-1.5A8.93 8.93 0 0010 8a8.93 8.93 0 00-6.3 2.2L3.5 6.4z" />`;
            } else {
                passwordInput.type = "password";
                eyeIcon.innerHTML = `<path d="M10 3C5.455 3 1.743 6.423.5 10c1.243 3.577 4.955 7 9.5 7s8.257-3.423 9.5-7c-1.243-3.577-4.955-7-9.5-7zm0 12a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z" />`;
            }
        }
    </script>
</x-guest-layout>
