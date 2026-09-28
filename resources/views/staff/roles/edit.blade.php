<x-app-layout>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-800">Edit Role: {{ $role->name }}</h2>
        <button type="button" onclick="window.location.href='{{ route('staff.roles.index') }}'" class="flex items-center gap-2 px-4 py-2 text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors font-semibold text-sm border border-gray-300" title="Press ESC to return">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Return
        </button>
    </div>

    <form action="{{ route('staff.roles.update', $role) }}" method="POST">
        @csrf
        
        <!-- Header Section with Stats and Form -->
        <div class="grid grid-cols-2 gap-6 mb-6">
            <!-- Left: Stats Cards -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="space-y-4">
                    <!-- Update Mode Card -->
                    <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                        <svg class="h-12 w-12 rounded-lg bg-purple-100 p-3 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">Update</p>
                            <p class="text-sm font-semibold text-gray-800">Mode</p>
                        </div>
                    </div>

                    <!-- Permission Groups -->
                    <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                        <svg class="h-12 w-12 rounded-lg bg-green-100 p-3 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-5-9h10v2H7z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">{{ $modules->count() }}</p>
                            <p class="text-sm font-semibold text-gray-800">Permission Groups</p>
                        </div>
                    </div>

                    <!-- Available Permissions -->
                    <div class="flex items-center gap-4 pb-4 border-b border-gray-100">
                        <svg class="h-12 w-12 rounded-lg bg-orange-100 p-3 text-orange-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-5-9h10v2H7z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase">{{ $modules->sum(fn($perms) => $perms->count()) }}</p>
                            <p class="text-sm font-semibold text-gray-800">Available Permissions</p>
                        </div>
                    </div>

                    <!-- Selected Permissions -->
                    <div class="flex items-center gap-4">
                        <svg class="h-12 w-12 rounded-lg bg-red-100 p-3 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                        </svg>
                        <div>
                            <p class="text-xs font-semibold text-gray-500 uppercase" id="selectedCount">{{ count($rolePermissions) }}</p>
                            <p class="text-sm font-semibold text-gray-800">Selected Permissions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Role Name Form -->
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-gray-800">Role Name</h3>
                </div>
                
                <p class="text-xs text-gray-600 mb-4">Current Role: <span class="font-semibold">{{ $role->name }}</span></p>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ $role->name }}" required class="w-full px-4 py-2.5 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm placeholder-gray-400" placeholder="Enter role name">
                </div>

                <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                    <p class="text-xs text-blue-700 leading-relaxed flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2z" clip-rule="evenodd" />
                        </svg>
                        <span><strong>↓ Review the permissions below,</strong> then save when the access list matches this role.</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Set Permissions Section -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-md font-bold text-gray-800">Set Permissions</h3>
                        <p class="text-xs text-gray-600 mt-1">Use search, quick actions, or individual cards to shape what this role can access.</p>
                    </div>
                    
                    <div class="flex gap-1 items-center">
                        <div class="relative z-30" id="permissionSearchContainer">
                            <div class="flex rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                                <span class="px-3 py-2 bg-gray-50 text-gray-400 border-r border-gray-200">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </span>
                                <input type="text" id="permissionSearch" placeholder="Search permission or group" class="px-3 py-2 text-sm focus:outline-none focus:ring-0 w-48" onkeyup="filterPermissionsWithDropdown(this.value)" onfocus="showPermissionDropdown()">
                            </div>
                            <div class="absolute top-full mt-2 w-full bg-white border border-gray-200 rounded-lg shadow-xl max-h-56 overflow-y-auto" id="permissionDropdown" style="display: none; z-index: 9999;">
                                @php
                                    $recommendations = [];
                                    foreach($modules as $groupName => $perms) {
                                        foreach($perms as $perm) {
                                            $recommendations[] = ['name' => $perm->name, 'id' => $perm->id, 'group' => $groupName];
                                        }
                                    }
                                @endphp
                                @foreach($recommendations as $item)
                                    <button type="button" onclick="addPermissionFromDropdown({{ $item['id'] }}); document.getElementById('permissionSearch').value = ''; closePermissionDropdown();" class="w-full text-left px-4 py-3 hover:bg-blue-50 border-b border-gray-50 last:border-0 transition-colors permission-item" data-permission-id="{{ $item['id'] }}">
                                        <div class="font-bold text-blue-600 text-sm">{{ $item['name'] }}</div>
                                        <div class="text-gray-500 text-xs uppercase tracking-wide">{{ $item['group'] }}</div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <button type="button" onclick="toggleSelectAll(); closePermissionDropdown();" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Select All
                        </button>
                        <button type="button" onclick="clearAllPermissions(); closePermissionDropdown();" class="px-4 py-2 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors font-semibold text-sm border border-gray-300">Clear</button>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-semibold text-gray-700"><span id="groupsVisible">{{ $modules->count() }}</span> groups visible</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <span class="text-sm font-semibold text-gray-700"><span id="permissionCounter">{{ count($rolePermissions) }}</span> / {{ $modules->sum(fn($perms) => $perms->count()) }} permissions selected</span>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-8">
                @foreach($modules as $module => $permissions)
                    <div class="module-section">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h4 class="text-sm font-bold text-gray-800">{{ $module }}</h4>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span class="module-count" data-module="{{ $module }}">{{ $permissions->filter(fn($p) => in_array($p->id, $rolePermissions))->count() }}</span> / {{ $permissions->count() }} selected
                                </p>
                            </div>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <span class="text-xs font-semibold text-gray-600 group-hover:text-gray-800">Full Access</span>
                                <input type="checkbox" class="module-full-access sr-only" data-module="{{ $module }}" onchange="toggleModuleFullAccess(this); updateToggleUI(this)" {{ $permissions->every(fn($p) => in_array($p->id, $rolePermissions)) ? 'checked' : '' }}>
                                <div class="relative inline-flex w-11 h-6 {{ $permissions->every(fn($p) => in_array($p->id, $rolePermissions)) ? 'bg-blue-500' : 'bg-gray-300' }} rounded-full transition-colors module-toggle" data-module-toggle="{{ $module }}">
                                    <span class="absolute top-1 w-4 h-4 bg-white rounded-full transition-all transform {{ $permissions->every(fn($p) => in_array($p->id, $rolePermissions)) ? 'left-6' : 'left-1' }}"></span>
                                </div>
                            </label>
                        </div>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                            @foreach($permissions as $permission)
                                <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all group module-permission" data-module="{{ $module }}">
                                    <input type="checkbox" name="permissions[]" value="{{ $permission->id }}" 
                                        {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500 transition-colors permission-checkbox" onchange="updateModuleCount('{{ $module }}')">
                                    <span class="ml-2 text-[12px] text-gray-700 group-hover:text-gray-900 font-medium break-words">{{ $permission->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex gap-3">
                <a href="{{ route('staff.roles.index') }}" class="px-6 py-2.5 bg-white text-gray-700 rounded-lg hover:bg-gray-100 transition-colors font-bold border border-gray-200">← Back to Roles</a>
                <button type="submit" class="px-6 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-bold">✓ Save Role</button>
            </div>
        </div>
    </form>

    <script>
        function updateToggleUI(toggle) {
            const module = toggle.dataset.module;
            const toggleDiv = document.querySelector('.module-toggle[data-module-toggle="' + module + '"]');
            const span = toggleDiv.querySelector('span');
            if (toggle.checked) {
                toggleDiv.classList.add('bg-blue-500');
                toggleDiv.classList.remove('bg-gray-300');
                span.classList.add('left-6');
                span.classList.remove('left-1');
            } else {
                toggleDiv.classList.remove('bg-blue-500');
                toggleDiv.classList.add('bg-gray-300');
                span.classList.remove('left-6');
                span.classList.add('left-1');
            }
        }

        function updateModuleCount(module) {
            const checkboxes = document.querySelectorAll('.module-permission[data-module="' + module + '"] .permission-checkbox');
            const checked = Array.from(checkboxes).filter(cb => cb.checked).length;
            document.querySelector('.module-count[data-module="' + module + '"]').textContent = checked;
            
            // Update total
            const allCheckboxes = document.querySelectorAll('.permission-checkbox');
            const totalChecked = Array.from(allCheckboxes).filter(cb => cb.checked).length;
            document.getElementById('permissionCounter').textContent = totalChecked;
            document.getElementById('selectedCount').textContent = totalChecked;
        }

        function toggleModuleFullAccess(toggle) {
            const module = toggle.dataset.module;
            const checkboxes = document.querySelectorAll('.module-permission[data-module="' + module + '"] .permission-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = toggle.checked;
            });
            updateModuleCount(module);
        }

        function toggleSelectAll() {
            const allCheckboxes = document.querySelectorAll('.permission-checkbox');
            const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
            allCheckboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            // Update all module counts
            document.querySelectorAll('.module-section').forEach(section => {
                const moduleName = section.querySelector('.module-full-access').dataset.module;
                updateModuleCount(moduleName);
            });
            
            // Update all toggle switches
            document.querySelectorAll('.module-full-access').forEach(toggle => {
                updateToggleUI(toggle);
            });
        }

        function clearAllPermissions() {
            const allCheckboxes = document.querySelectorAll('.permission-checkbox');
            allCheckboxes.forEach(cb => {
                cb.checked = false;
            });
            
            // Update all module counts
            document.querySelectorAll('.module-section').forEach(section => {
                const moduleName = section.querySelector('.module-full-access').dataset.module;
                updateModuleCount(moduleName);
            });
            
            // Update all toggle switches
            document.querySelectorAll('.module-full-access').forEach(toggle => {
                toggle.checked = false;
                updateToggleUI(toggle);
            });
        }

        function filterPermissions(query) {
            const lowerQuery = query.toLowerCase();
            let visibleGroups = 0;
            
            document.querySelectorAll('.module-section').forEach(section => {
                const moduleName = section.querySelector('h4').textContent.toLowerCase();
                let hasVisiblePermissions = false;
                
                section.querySelectorAll('.module-permission').forEach(permission => {
                    const permName = permission.querySelector('span').textContent.toLowerCase();
                    const isVisible = moduleName.includes(lowerQuery) || permName.includes(lowerQuery);
                    permission.style.display = isVisible ? 'flex' : 'none';
                    if (isVisible) hasVisiblePermissions = true;
                });
                
                section.style.display = hasVisiblePermissions ? 'block' : 'none';
                if (hasVisiblePermissions) visibleGroups++;
            });
            
            document.getElementById('groupsVisible').textContent = visibleGroups;
        }

        function showPermissionDropdown() {
            document.getElementById('permissionDropdown').style.display = 'block';
        }

        function closePermissionDropdown() {
            document.getElementById('permissionDropdown').style.display = 'none';
        }

        function filterPermissionsWithDropdown(query) {
            const lowerQuery = query.toLowerCase();
            const dropdown = document.getElementById('permissionDropdown');
            let visibleCount = 0;

            document.querySelectorAll('.permission-item').forEach(item => {
                const permName = item.textContent.toLowerCase();
                const isVisible = permName.includes(lowerQuery) || lowerQuery === '';
                item.style.display = isVisible ? 'block' : 'none';
                if (isVisible) visibleCount++;
            });

            dropdown.style.display = visibleCount > 0 ? 'block' : 'none';
            filterPermissions(query);
        }

        function addPermissionFromDropdown(permissionId) {
            const checkbox = document.querySelector(`input.permission-checkbox[value="${permissionId}"]:not(:checked)`);
            if (checkbox) {
                checkbox.checked = true;
                const module = checkbox.closest('.module-permission').getAttribute('data-module');
                updateModuleCount(module);
            }
        }

        function addRecommendedPermission(permissionId) {
            const checkbox = document.querySelector(`input.permission-checkbox[value="${permissionId}"]:not(:checked)`);
            if (checkbox) {
                checkbox.checked = true;
                const module = checkbox.closest('.module-permission').getAttribute('data-module');
                updateModuleCount(module);
            }
        }

        // Click outside to close dropdown
        document.addEventListener('click', function(event) {
            const container = document.getElementById('permissionSearchContainer');
            if (container && !container.contains(event.target)) {
                closePermissionDropdown();
            }
        });

        // ESC key shortcut to return
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                window.location.href = '{{ route('staff.roles.index') }}';
            }
        });
    </script>
</x-app-layout>
