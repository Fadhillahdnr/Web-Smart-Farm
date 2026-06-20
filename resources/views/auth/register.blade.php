<x-guest-layout>

    <h2 class="text-2xl font-bold text-gray-700 mb-6 text-center">
        Create Account
    </h2>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- NAME -->
        <div>
            <label class="text-sm text-gray-600">Full Name</label>
            <input type="text" name="name"
                value="{{ old('name') }}"
                required autofocus
                class="input-modern w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none">

            <x-input-error :messages="$errors->get('name')" class="mt-1 text-sm text-red-500" />
        </div>

        <!-- EMAIL -->
        <div>
            <label class="text-sm text-gray-600">Email</label>
            <input type="email" name="email"
                value="{{ old('email') }}"
                required
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

        <!-- CONFIRM PASSWORD -->
        <div>
            <label class="text-sm text-gray-600">Confirm Password</label>
            <input type="password" name="password_confirmation"
                required
                class="input-modern w-full mt-1 px-4 py-2 border rounded-lg focus:outline-none">

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-sm text-red-500" />
        </div>

        <!-- BUTTON -->
        <div class="space-y-3">

            <button
                class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold shadow transition">
                Register
            </button>

            <!-- BACK TO LOGIN -->
            <a href="{{ route('login') }}"
                class="block text-center w-full border border-gray-400 text-gray-600 hover:bg-gray-100 py-2 rounded-lg font-semibold transition">
                Already have account? Login
            </a>

        </div>

    </form>

</x-guest-layout>