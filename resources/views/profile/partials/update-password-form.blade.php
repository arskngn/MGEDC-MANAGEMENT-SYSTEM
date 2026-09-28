<section x-data="{
    showCurrent: false,
    showNew: false,
    showConfirm: false
}">
    <header class="mb-6">
        <h2 class="text-lg font-bold text-[#0a1233]">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-500">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-sm font-bold text-gray-700 mb-1">{{ __('Current Password') }}</label>
            <div class="relative">
                <input id="update_password_current_password" name="current_password"
                    :type="showCurrent ? 'text' : 'password'"
                    class="w-full px-4 py-2 pr-11 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#4634ff] text-sm"
                    autocomplete="current-password" />
                <button type="button" @click="showCurrent = !showCurrent"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                    tabindex="-1" aria-label="Toggle password visibility">
                    <svg x-show="!showCurrent" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showCurrent" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.12 9.12l.841-.84m7.07-7.07l-.84.84M21 21l-9-9" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="block text-sm font-bold text-gray-700 mb-1">{{ __('New Password') }}</label>
            <div class="relative">
                <input id="update_password_password" name="password"
                    :type="showNew ? 'text' : 'password'"
                    class="w-full px-4 py-2 pr-11 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#4634ff] text-sm"
                    autocomplete="new-password" />
                <button type="button" @click="showNew = !showNew"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                    tabindex="-1" aria-label="Toggle password visibility">
                    <svg x-show="!showNew" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showNew" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.12 9.12l.841-.84m7.07-7.07l-.84.84M21 21l-9-9" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-sm font-bold text-gray-700 mb-1">{{ __('Confirm Password') }}</label>
            <div class="relative">
                <input id="update_password_password_confirmation" name="password_confirmation"
                    :type="showConfirm ? 'text' : 'password'"
                    class="w-full px-4 py-2 pr-11 border border-gray-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-[#4634ff] text-sm"
                    autocomplete="new-password" />
                <button type="button" @click="showConfirm = !showConfirm"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 cursor-pointer"
                    tabindex="-1" aria-label="Toggle password visibility">
                    <svg x-show="!showConfirm" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <svg x-show="showConfirm" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.12 9.12l.841-.84m7.07-7.07l-.84.84M21 21l-9-9" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4 border-t border-gray-100 pt-6">
            <button type="submit" class="px-6 py-2 bg-[#4634ff] text-white rounded-lg hover:bg-blue-700 transition-colors font-bold text-sm">
                {{ __('Save Changes') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-bold text-green-600"
                >{{ __('Saved successfully.') }}</p>
            @endif
        </div>
    </form>
</section>
