<x-app-layout>
    <div class="space-y-6" x-data="{ activeTemplateId: localStorage.getItem('notif_activeTemplateId') || null }" x-init="
        $watch('activeTemplateId', value => localStorage.setItem('notif_activeTemplateId', value));
    ">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800">System Notifications</h2>
            <a href="{{ route('settings.index') }}" class="flex items-center text-sm text-gray-500 hover:text-[#4634ff] transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Settings
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-200">
            <!-- Templates List -->
            <div x-show="!activeTemplateId || activeTemplateId == 'null'">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Notification Templates</h3>
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-[#4634ff]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-white uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-right text-xs font-bold text-white uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($templates as $template)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $template->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $template->subject }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="activeTemplateId = {{ $template->id }}" type="button" class="px-4 py-2 text-sm font-medium text-white bg-[#4634ff] rounded-lg hover:bg-[#3828cc] transition-colors">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Edit Template -->
            @foreach($templates as $template)
            <div x-show="activeTemplateId == {{ $template->id }}" x-transition class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-800">Edit {{ $template->name }}</h3>
                    <button @click="activeTemplateId = null" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 flex items-center shadow-sm transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </button>
                </div>

                <!-- Shortcodes -->
                <div class="mb-6">
                    <div class="flex justify-between items-center bg-[#4634ff] text-white px-4 py-2 rounded-t-lg">
                        <h3 class="text-sm font-bold">Short Code</h3>
                        <h3 class="text-sm font-bold text-right">Description</h3>
                    </div>
                    <div class="border border-gray-200 rounded-b-lg">
                        @php
                            $shortcodesData = is_string($template->shortcodes) ? json_decode($template->shortcodes, true) : $template->shortcodes;
                        @endphp
                        @if(!empty($shortcodesData) && is_array($shortcodesData))
                            @foreach($shortcodesData as $code => $desc)
                            <div class="flex justify-between items-center px-4 py-3 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                                <p class="text-sm text-gray-800">{{ $code }}</p>
                                <p class="text-sm text-gray-500">{{ $desc }}</p>
                            </div>
                            @endforeach
                        @else
                            <div class="px-4 py-3 text-sm text-gray-500 italic">No specific shortcodes available for this template.</div>
                        @endif
                    </div>
                </div>

                <form action="{{ route('settings.notification.template.update', $template->id) }}" method="POST" class="space-y-6">
                    @csrf
                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" name="subject" value="{{ $template->subject }}" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email Body</label>
                            <textarea name="email_body" rows="12" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm font-mono">{{ $template->email_body }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SMS Body</label>
                            <textarea name="sms_body" rows="6" class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-[#4634ff] focus:border-[#4634ff] sm:text-sm font-mono">{{ $template->sms_body }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Notifications</label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_email_enabled" value="1" {{ $template->is_email_enabled ? 'checked' : '' }} class="w-5 h-5 text-[#4634ff] border-gray-300 rounded focus:ring-[#4634ff]">
                                    <span class="ml-2 text-sm text-gray-700">Enable</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">SMS Notifications</label>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="is_sms_enabled" value="1" {{ $template->is_sms_enabled ? 'checked' : '' }} class="w-5 h-5 text-[#4634ff] border-gray-300 rounded focus:ring-[#4634ff]">
                                    <span class="ml-2 text-sm text-gray-700">Enable</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#4634ff] text-white py-3 rounded-lg font-bold text-lg shadow-lg hover:bg-[#3828cc] transition-all">
                        Save Changes
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>