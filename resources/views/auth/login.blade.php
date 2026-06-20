<x-guest-layout>

    <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">
        Login Account
    </h2>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- EMAIL -->
        <div>
            <label class="text-sm text-gray-600">Email</label>
            <input type="email" name="email"
                value="{{ old('email') }}"
                required autofocus
                class="input-modern w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none">

            <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-500" />
        </div>

        <!-- PASSWORD -->
        <div>
            <label class="text-sm text-gray-600">Password</label>
            <input type="password" name="password"
                required
                class="input-modern w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none">

            <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-500" />
        </div>

        <!-- REMEMBER -->
        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="remember" class="rounded">
                Remember me
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-green-600 hover:underline">
                    Forgot?
                </a>
            @endif
        </div>

        <!-- BUTTON -->
        <div class="space-y-3">

            <button
                class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold shadow transition">
                Login
            </button>

            <!-- REGISTER -->
            @if (Route::has('register'))
            <a href="{{ route('register') }}"
                class="block text-center w-full border border-green-600 text-green-600 hover:bg-green-50 py-2 rounded-lg font-semibold transition">
                Create Account
            </a>
            @endif

        </div>

    </form>

</x-guest-layout>