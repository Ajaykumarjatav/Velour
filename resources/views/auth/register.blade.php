@extends('layouts.auth')
@section('title', 'Create Account')
@section('auth_container_class', 'max-w-full sm:max-w-xl md:max-w-2xl lg:max-w-3xl xl:max-w-4xl')
@section('content')
<h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 sm:mb-6">Create your free account</h2>

<form action="{{ route('register.submit') }}" method="POST" class="space-y-4 sm:space-y-5" id="register-form">
    @csrf
    <div class="grid grid-cols-1 gap-4 sm:gap-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 md:gap-4">
            <div class="min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Your full name</label>
                <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                       class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Business name</label>
                <input type="text" name="salon_name" value="{{ old('salon_name') }}" required autocomplete="organization"
                       class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('salon_name') border-red-400 @enderror">
                @error('salon_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Business phone <span class="text-gray-400">(optional)</span></label>
            <input type="tel" name="salon_phone" value="{{ old('salon_phone') }}" autocomplete="tel"
                   class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
        </div>
        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Business types <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-2">Select all that apply.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 rounded-xl border border-gray-200 bg-gray-50/80 p-3 sm:p-4 max-h-[min(14rem,50vh)] sm:max-h-56 md:max-h-72 overflow-y-auto overscroll-contain">
                @foreach($businessTypes as $type)
                    <label class="flex items-center gap-2 text-sm text-gray-800 cursor-pointer">
                        <input type="checkbox" name="business_type_ids[]" value="{{ $type->id }}"
                               data-bt-slug="{{ $type->slug }}"
                               class="rounded border-gray-300 text-velour-600 focus:ring-velour-500"
                               {{ in_array((int) $type->id, array_map('intval', old('business_type_ids', [])), true) ? 'checked' : '' }}
                               onchange="window.registrationSyncStarters && window.registrationSyncStarters()">
                        <span>{{ $type->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('business_type_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div id="starter-categories-block" class="space-y-3 rounded-xl border border-gray-200 p-3 sm:p-4 hidden min-w-0">
            <label class="block text-sm font-medium text-gray-700">Predefined service categories <span class="text-gray-400 font-normal">(optional)</span></label>
            <p class="text-xs text-gray-500">Pick starter categories first to filter the suggested services.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
            @foreach($starterCatalog as $slug => $items)
                @php
                    $cats = [];
                    foreach ($items as $item) {
                        $catSlug = (string) ($item['category_slug'] ?? \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'General')));
                        if ($catSlug === '') {
                            $catSlug = 'general';
                        }
                        $catName = (string) ($item['category'] ?? 'General');
                        if (! isset($cats[$catSlug])) {
                            $cats[$catSlug] = $catName === '' ? 'General' : $catName;
                        }
                    }
                @endphp
                @foreach($cats as $catSlug => $catName)
                    @php $catVal = $slug . ':' . $catSlug; @endphp
                    <label class="starter-category flex items-start gap-2 text-sm text-gray-800 cursor-pointer hidden min-w-0" data-bt-slug="{{ $slug }}" data-cat-id="{{ $catVal }}">
                        <input type="checkbox" name="starter_categories[]" value="{{ $catVal }}"
                               class="mt-0.5 rounded border-gray-300 text-velour-600 focus:ring-velour-500"
                               {{ in_array($catVal, old('starter_categories', []), true) ? 'checked' : '' }}>
                        <span>{{ $catName }}</span>
                    </label>
                @endforeach
            @endforeach
            </div>
            @error('starter_categories')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div id="starter-services-block" class="space-y-3 rounded-xl border border-gray-200 p-3 sm:p-4 hidden min-w-0">
            <label class="block text-sm font-medium text-gray-700">Predefined services <span class="text-gray-400 font-normal">(optional)</span></label>
            <p class="text-xs text-gray-500">Add suggested services to your menu now — filtered by the categories selected above.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
            @foreach($starterCatalog as $slug => $items)
                @foreach($items as $item)
                    @php $val = $slug . ':' . $item['key']; @endphp
                    @php
                        $catSlug = (string) ($item['category_slug'] ?? \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'General')));
                        if ($catSlug === '') {
                            $catSlug = 'general';
                        }
                        $catId = $slug . ':' . $catSlug;
                    @endphp
                    <label class="starter-offer flex items-start gap-2 text-sm text-gray-800 cursor-pointer hidden min-w-0" data-bt-slug="{{ $slug }}" data-cat-id="{{ $catId }}">
                        <input type="checkbox" name="starter_services[]" value="{{ $val }}"
                               class="mt-0.5 rounded border-gray-300 text-velour-600 focus:ring-velour-500"
                               {{ in_array($val, old('starter_services', []), true) ? 'checked' : '' }}>
                        <span>{{ $item['name'] }} <span class="text-gray-400">({{ $item['duration_minutes'] }} min — £{{ number_format((float) $item['price'], 2) }})</span></span>
                    </label>
                @endforeach
            @endforeach
            </div>
            @error('starter_services')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        @php
            $staffOld = old('staff_members');
            if (! is_array($staffOld)) {
                $staffOld = [];
            }
            if (count($staffOld) === 0) {
                $staffOld = [[]];
            }
            $staffRoles = ['stylist', 'therapist', 'manager', 'receptionist', 'junior', 'owner'];
        @endphp
        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1">Team members <span class="text-gray-400 font-normal">(optional)</span></label>
            <p class="text-xs text-gray-500 mb-3">Add people who work at your business. You can invite them to log in later from Staff &amp; HR.</p>
            <div id="staff-rows" class="space-y-4">
                @foreach($staffOld as $idx => $st)
                    @php $st = is_array($st) ? $st : []; @endphp
                    <div class="staff-member-row rounded-xl border border-gray-200 bg-gray-50/80 dark:bg-gray-900/20 p-4 space-y-3">
                        <div class="flex justify-between items-center gap-2">
                            <span class="staff-row-title text-sm font-medium text-gray-800">Team member {{ $loop->iteration }}</span>
                            <button type="button" class="staff-remove-btn text-xs font-medium text-red-600 hover:text-red-700 {{ count($staffOld) <= 1 ? 'hidden' : '' }}">Remove</button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            <div class="md:col-span-2 min-w-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Full name</label>
                                <input type="text" name="staff_members[{{ $idx }}][name]" value="{{ $st['name'] ?? '' }}"
                                       class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent"
                                       placeholder="e.g. Alex Smith">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                                <input type="email" name="staff_members[{{ $idx }}][email]" value="{{ $st['email'] ?? '' }}" autocomplete="off"
                                       class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Phone</label>
                                <input type="tel" name="staff_members[{{ $idx }}][phone]" value="{{ $st['phone'] ?? '' }}"
                                       class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Role <span class="text-red-500">*</span> <span class="text-gray-400 font-normal">(if adding)</span></label>
                                <select name="staff_members[{{ $idx }}][role]" class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm bg-white focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                                    <option value="">—</option>
                                    @foreach($staffRoles as $r)
                                        <option value="{{ $r }}" {{ ($st['role'] ?? '') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Commission %</label>
                                <input type="number" name="staff_members[{{ $idx }}][commission_rate]" min="0" max="100" step="0.1"
                                       value="{{ $st['commission_rate'] ?? '0' }}"
                                       class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                            </div>
                            <div class="min-w-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Calendar colour</label>
                                <input type="color" name="staff_members[{{ $idx }}][color]" value="{{ $st['color'] ?? '#7C3AED' }}"
                                       class="w-full h-11 min-w-0 px-1 py-1 rounded-xl border border-gray-300 cursor-pointer bg-white">
                            </div>
                            <div class="md:col-span-2 min-w-0">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Bio</label>
                                <textarea name="staff_members[{{ $idx }}][bio]" rows="2" placeholder="Optional"
                                          class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">{{ $st['bio'] ?? '' }}</textarea>
                            </div>
                        </div>
                        <input type="hidden" name="staff_members[{{ $idx }}][assign_services]" value="0">
                        @php
                            $as = old('staff_members.'.$idx.'.assign_services');
                            if ($as === null) {
                                $assignChecked = true;
                            } elseif (is_array($as)) {
                                $assignChecked = in_array('1', $as, true) || in_array(1, $as, true);
                            } else {
                                $assignChecked = (string) $as === '1';
                            }
                        @endphp
                        <label class="inline-flex items-start gap-2 text-sm text-gray-700 cursor-pointer">
                            <input type="checkbox" name="staff_members[{{ $idx }}][assign_services]" value="1" class="mt-0.5 rounded border-gray-300 text-velour-600"
                                   {{ $assignChecked ? 'checked' : '' }}>
                            <span>Offer all services on this menu to this team member</span>
                        </label>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-staff-member" class="mt-2 text-sm font-medium text-velour-600 hover:text-velour-700">+ Add another team member</button>
            @error('staff_members')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 md:gap-4">
            <div class="min-w-0 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                       class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('email') border-red-400 @enderror">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="register-password">Password</label>
                <div class="relative">
                    <input id="register-password" type="password" name="password" required autocomplete="new-password"
                           class="w-full min-w-0 pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                    @include('auth._password-visibility-toggle', ['targetId' => 'register-password'])
                </div>
            </div>
            <div class="min-w-0">
                <label class="block text-sm font-medium text-gray-700 mb-1.5" for="register-password-confirmation">Confirm password</label>
                <div class="relative">
                    <input id="register-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full min-w-0 pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                    @include('auth._password-visibility-toggle', ['targetId' => 'register-password-confirmation'])
                </div>
            </div>
        </div>
    </div>

    <button type="submit"
            class="w-full bg-velour-600 hover:bg-velour-700 active:bg-velour-800 text-white font-semibold py-3 sm:py-2.5 px-4 rounded-xl text-base sm:text-sm transition-colors shadow-sm mt-2 touch-manipulation">
        Create account
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    Already have an account?
    <a href="{{ route('login') }}" class="text-velour-600 hover:text-velour-700 font-medium">Sign in</a>
</p>

<script>
(function () {
    function sync() {
        var checked = Array.prototype.slice.call(document.querySelectorAll('input[name="business_type_ids[]"]:checked'));
        var slugs = {};
        checked.forEach(function (el) {
            var s = el.getAttribute('data-bt-slug');
            if (s) slugs[s] = true;
        });

        var anyCategoryVisible = false;
        var selectedCategoryIds = {};
        var selectedBySlug = {};
        document.querySelectorAll('.starter-category').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            var show = slug && slugs[slug];
            el.classList.toggle('hidden', !show);
            if (show) anyCategoryVisible = true;
            var chk = el.querySelector('input[type="checkbox"]');
            if (chk) {
                if (!show) {
                    chk.checked = false;
                }
                if (show && chk.checked) {
                    var catId = el.getAttribute('data-cat-id');
                    selectedCategoryIds[catId] = true;
                    selectedBySlug[slug] = (selectedBySlug[slug] || 0) + 1;
                }
            }
        });

        var categoriesBlock = document.getElementById('starter-categories-block');
        if (categoriesBlock) categoriesBlock.classList.toggle('hidden', !anyCategoryVisible);

        var anyServiceVisible = false;
        document.querySelectorAll('.starter-offer').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            var catId = el.getAttribute('data-cat-id');
            var show = !!(slug && slugs[slug]);
            if (show) {
                var filterByCat = (selectedBySlug[slug] || 0) > 0;
                if (filterByCat) {
                    show = !!selectedCategoryIds[catId];
                }
            }
            el.classList.toggle('hidden', !show);
            if (show) anyServiceVisible = true;
            var chk = el.querySelector('input[type="checkbox"]');
            if (!show && chk) chk.checked = false;
        });
        var block = document.getElementById('starter-services-block');
        if (block) block.classList.toggle('hidden', !anyServiceVisible);
    }
    window.registrationSyncStarters = sync;
    document.addEventListener('change', function (e) {
        if (e.target && e.target.closest('.starter-category')) {
            sync();
        }
    });
    document.addEventListener('DOMContentLoaded', sync);
})();

(function () {
    var maxRows = 10;
    var container = document.getElementById('staff-rows');
    var addBtn = document.getElementById('add-staff-member');
    if (!container || !addBtn) return;

    function renumberStaffRows() {
        var rows = container.querySelectorAll('.staff-member-row');
        rows.forEach(function (row, i) {
            row.querySelectorAll('[name^="staff_members"]').forEach(function (el) {
                el.name = el.name.replace(/staff_members\[\d+\]/, 'staff_members[' + i + ']');
            });
            var title = row.querySelector('.staff-row-title');
            if (title) title.textContent = 'Team member ' + (i + 1);
            var rm = row.querySelector('.staff-remove-btn');
            if (rm) rm.classList.toggle('hidden', rows.length <= 1);
        });
    }

    addBtn.addEventListener('click', function () {
        var rows = container.querySelectorAll('.staff-member-row');
        if (rows.length >= maxRows) return;
        var clone = rows[0].cloneNode(true);
        clone.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], textarea').forEach(function (el) {
            el.value = '';
        });
        clone.querySelectorAll('input[type="color"]').forEach(function (el) {
            el.value = '#7C3AED';
        });
        clone.querySelectorAll('select').forEach(function (el) {
            el.selectedIndex = 0;
        });
        clone.querySelectorAll('input[type="checkbox"]').forEach(function (el) {
            el.checked = true;
        });
        clone.querySelectorAll('input[type="hidden"]').forEach(function (el) {
            if (el.name && el.name.indexOf('assign_services') !== -1) el.value = '0';
        });
        container.appendChild(clone);
        renumberStaffRows();
    });

    container.addEventListener('click', function (e) {
        var btn = e.target.closest('.staff-remove-btn');
        if (!btn) return;
        var row = btn.closest('.staff-member-row');
        if (!row || container.querySelectorAll('.staff-member-row').length <= 1) return;
        row.remove();
        renumberStaffRows();
    });

    renumberStaffRows();
})();
</script>
@endsection
