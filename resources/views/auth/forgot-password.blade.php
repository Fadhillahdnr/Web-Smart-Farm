<x-guest-layout>

    <h2 class="text-2xl font-bold text-gray-700 mb-4 text-center">
        Reset Password
    </h2>

    <p class="text-sm text-gray-500 text-center mb-6">
        Masukkan email kamu, nanti kami kirim link untuk reset password.
    </p>

    <!-- SESSION STATUS -->
    <x-auth-session-status class="mb-4 text-center text-green-600" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
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

        <!-- BUTTON -->
        <div class="space-y-3">

            <button
                class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg font-semibold shadow transition">
                Kirim Link Reset
            </button>

            <!-- BACK -->
            <a href="{{ route('login') }}"
                class="block text-center w-full border border-gray-400 text-gray-600 hover:bg-gray-100 py-2 rounded-lg font-semibold transition">
                Kembali ke Login
            </a>

        </div>

    </form>

</x-guest-layout>