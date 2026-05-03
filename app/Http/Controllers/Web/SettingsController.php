<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\User;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\SalonSetting;
use App\Services\NotificationConfigService;
use App\Support\LanguageProficiency;
use App\Support\RegistrationStarterServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    use ResolvesActiveSalon;

    private function salon()
    {
        return $this->activeSalon();
    }

    /**
     * Services for the settings UI / starter sync: session "active" salon can differ from
     * Tenant::current(), and BelongsToTenant would otherwise AND two different salon_ids.
     */
    private function servicesForSalonSettings(\App\Models\Salon $salon): \Illuminate\Database\Eloquent\Builder
    {
        return Service::withoutGlobalScopes()->where('salon_id', $salon->id);
    }

    private function serviceCategoriesForSalonSettings(\App\Models\Salon $salon): \Illuminate\Database\Eloquent\Builder
    {
        return ServiceCategory::withoutGlobalScopes()->where('salon_id', $salon->id);
    }

    /**
     * Invited stylists may only change their own profile & security, not salon-wide settings.
     */
    private function abortUnlessCanEditSalonWideSettings(): void
    {
        if (Auth::user()->dashboardScopedStaffId() !== null) {
            abort(403, 'You can only update your profile and security here. Contact a salon admin to change business or service settings.');
        }
    }

    public function index()
    {
        $salon    = $this->salon();
        $settings = $salon->settings()->pluck('value', 'key');
        $user     = Auth::user();

        $settingsPersonalOnly = $user->dashboardScopedStaffId() !== null;
        $tabLabelsOrder       = [
            'salon' => 'Business',
            'services' => 'Service',
            'hours' => 'Hours',
            'social' => 'Social Links',
            'notifications' => 'Notifications',
            'profile' => 'Profile',
            'security' => 'Security',
        ];
        $allowedTabKeys = $settingsPersonalOnly
            ? ['profile', 'security']
            : array_keys($tabLabelsOrder);
        $settingsTabLabels = [];
        foreach ($tabLabelsOrder as $key => $label) {
            if (in_array($key, $allowedTabKeys, true)) {
                $settingsTabLabels[$key] = $label;
            }
        }
        $defaultTab = $settingsPersonalOnly ? 'profile' : 'salon';
        $requestedTab = trim((string) session('tab', request()->get('tab', $defaultTab)));
        if (! in_array($requestedTab, $allowedTabKeys, true)) {
            $requestedTab = $defaultTab;
        }
        $settingsInitialTab = $requestedTab;
        $hideSalonProfileBar = $settingsPersonalOnly;

        $notificationDefinitions = NotificationConfigService::definitions();
        $settingsArr = $salon->settings()->pluck('value', 'key')->all();
        $notificationConfig = app(NotificationConfigService::class)->mergedConfigArray($salon, $settingsArr);

        $bookingTimeDisplay = $salon->getSetting('booking_time_display', 'business');
        $localeOptions = \App\Support\DisplayFormatter::localeOptions();
        $starterCatalog = config('registration_starter_services');
        $predefinedSlugs = array_keys((array) $starterCatalog);
        $businessTypes = BusinessType::query()
            ->whereIn('slug', $predefinedSlugs)
            ->orderBy('sort_order')
            ->get();
        $customBusinessTypes = BusinessType::query()
            ->whereNotIn('slug', $predefinedSlugs)
            ->orderBy('name')
            ->get();
        $selectedBusinessTypeIds = $salon->businessTypes()->pluck('business_types.id')->map(fn ($id) => (int) $id)->all();
        if ($selectedBusinessTypeIds === [] && $salon->business_type_id) {
            $selectedBusinessTypeIds = [(int) $salon->business_type_id];
        }
        $selectedBusinessTypeSlugs = BusinessType::query()
            ->whereIn('id', $selectedBusinessTypeIds)
            ->pluck('slug')
            ->filter(fn ($slug) => is_string($slug) && $slug !== '')
            ->values()
            ->all();
        $selectedStarterCategories = $this->selectedStarterCategoryKeysForSalon($salon, $starterCatalog, $selectedBusinessTypeIds);
        $selectedStarterServices = $this->selectedStarterServiceKeysForSalon($salon, $starterCatalog, $selectedBusinessTypeIds);
        $selectedStarterServiceMeta = $this->selectedStarterServiceMetaForSalon($salon, $starterCatalog, $selectedBusinessTypeIds);
        // Same branch as Staff & HR: tenant scope + active_salon_id can disagree for multi-location.
        $existingTeamMembers = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where(function ($q) use ($salon) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', '!=', (int) $salon->owner_id);
            })
            ->orderBy('sort_order')
            ->get();

        return view('settings.index', compact(
            'salon',
            'settings',
            'user',
            'notificationDefinitions',
            'notificationConfig',
            'bookingTimeDisplay',
            'localeOptions',
            'businessTypes',
            'customBusinessTypes',
            'selectedBusinessTypeIds',
            'selectedBusinessTypeSlugs',
            'starterCatalog',
            'selectedStarterCategories',
            'selectedStarterServices',
            'selectedStarterServiceMeta',
            'existingTeamMembers',
            'settingsTabLabels',
            'settingsPersonalOnly',
            'settingsInitialTab',
            'hideSalonProfileBar'
        ));
    }

    public function updateSalon(Request $request)
    {
        $this->abortUnlessCanEditSalonWideSettings();
        $salon = $this->salon();

        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:150'],
            'email'                => ['nullable', 'email', 'max:150'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'website'            => ['nullable', 'url', 'max:200'],
            'description'        => ['nullable', 'string', 'max:1000'],
            'address_line1'      => ['nullable', 'string', 'max:200'],
            'address_line2'      => ['nullable', 'string', 'max:200'],
            'city'               => ['nullable', 'string', 'max:100'],
            'county'             => ['nullable', 'string', 'max:100'],
            'postcode'           => ['nullable', 'string', 'max:20'],
            'country'            => ['nullable', 'string', 'max:2'],
            'timezone'           => ['required', 'string', 'timezone'],
            'currency'           => ['required', 'string', 'size:3', 'in:' . implode(',', array_keys(\App\Helpers\CurrencyHelper::all()))],
            'booking_time_display' => ['nullable', 'in:business,customer'],
            'home_services_enabled' => ['sometimes', 'boolean'],
        ]);

        $bookingTimeDisplay = $data['booking_time_display'] ?? 'business';
        unset($data['booking_time_display']);

        $data['home_services_enabled'] = $request->boolean('home_services_enabled');

        $salon->update($data);

        SalonSetting::updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'booking_time_display'],
            ['value' => $bookingTimeDisplay, 'type' => 'string']
        );

        return $this->redirectAfterSettingsSave($request, 'Salon profile updated.', 'salon');
    }

    public function updateServices(Request $request)
    {
        $this->abortUnlessCanEditSalonWideSettings();
        $salon = $this->salon();
        $data = $request->validate([
            'business_type_ids'    => ['nullable', 'array'],
            'business_type_ids.*'  => ['integer', 'exists:business_types,id'],
            'custom_business_types' => ['nullable', 'array', 'max:10'],
            'custom_business_types.*' => ['nullable', 'string', 'max:80'],
            'starter_categories'   => ['nullable', 'array'],
            'starter_categories.*' => ['string'],
            'starter_services'     => ['nullable', 'array'],
            'starter_services.*'   => ['string'],
            'starter_service_meta' => ['nullable', 'array'],
        ]);

        $typeIds = array_values(array_unique(array_map('intval', (array) ($data['business_type_ids'] ?? []))));
        $customNames = array_values(array_unique(array_filter(array_map(
            fn ($name) => trim((string) $name),
            (array) ($data['custom_business_types'] ?? [])
        ))));
        $typeIds = array_values(array_unique(array_merge($typeIds, $this->ensureCustomBusinessTypes($customNames))));

        if ($typeIds === []) {
            throw ValidationException::withMessages([
                'business_type_ids' => ['Select at least one business type or add a custom type.'],
            ]);
        }

        $currentPivotIds = $salon->businessTypes()->pluck('business_types.id')->map(fn ($id) => (int) $id)->all();
        $removed = array_diff($currentPivotIds, $typeIds);
        foreach ($removed as $rid) {
            if ($this->servicesForSalonSettings($salon)->where('business_type_id', $rid)->exists()) {
                throw ValidationException::withMessages([
                    'business_type_ids' => ['Remove or reassign services that use a business type before you can remove that type.'],
                ]);
            }
        }

        $salon->businessTypes()->sync($typeIds);
        if (! in_array((int) $salon->business_type_id, $typeIds, true)) {
            $salon->update(['business_type_id' => $typeIds[0]]);
        }

        $selectedServiceKeys = array_values(array_unique(array_filter(
            (array) ($data['starter_services'] ?? []),
            fn ($v) => is_string($v) && $v !== ''
        )));

        $serviceMetaInput = (array) $request->input('starter_service_meta', []);
        $serviceOverrides = [];
        foreach ($selectedServiceKeys as $serviceKey) {
            [$serviceSlug] = explode(':', $serviceKey, 2);
            $token = str_replace(':', '__', $serviceKey);
            $meta = (array) ($serviceMetaInput[$token] ?? []);
            if ($serviceSlug === 'unisex') {
                $men = (array) ($meta['men'] ?? []);
                $women = (array) ($meta['women'] ?? []);
                $menDuration = isset($men['duration_minutes']) ? (int) $men['duration_minutes'] : 0;
                $menPrice = isset($men['price']) ? (float) $men['price'] : 0;
                $womenDuration = isset($women['duration_minutes']) ? (int) $women['duration_minutes'] : 0;
                $womenPrice = isset($women['price']) ? (float) $women['price'] : 0;

                if ($menDuration < 1) {
                    throw ValidationException::withMessages([
                        "starter_service_meta.$token.men.duration_minutes" => ['Enter men service time in minutes.'],
                    ]);
                }
                if ($menPrice <= 0) {
                    throw ValidationException::withMessages([
                        "starter_service_meta.$token.men.price" => ['Enter a valid men service price.'],
                    ]);
                }
                if ($womenDuration < 1) {
                    throw ValidationException::withMessages([
                        "starter_service_meta.$token.women.duration_minutes" => ['Enter women service time in minutes.'],
                    ]);
                }
                if ($womenPrice <= 0) {
                    throw ValidationException::withMessages([
                        "starter_service_meta.$token.women.price" => ['Enter a valid women service price.'],
                    ]);
                }

                $serviceOverrides[$serviceKey] = [
                    'men' => [
                        'duration_minutes' => $menDuration,
                        'price' => round($menPrice, 2),
                    ],
                    'women' => [
                        'duration_minutes' => $womenDuration,
                        'price' => round($womenPrice, 2),
                    ],
                ];
                continue;
            }

            $duration = isset($meta['duration_minutes']) ? (int) $meta['duration_minutes'] : 0;
            $price = isset($meta['price']) ? (float) $meta['price'] : 0;
            if ($duration < 1) {
                throw ValidationException::withMessages([
                    "starter_service_meta.$token.duration_minutes" => ['Enter service time in minutes for each selected service.'],
                ]);
            }
            if ($price <= 0) {
                throw ValidationException::withMessages([
                    "starter_service_meta.$token.price" => ['Enter a valid service price for each selected service.'],
                ]);
            }

            $serviceOverrides[$serviceKey] = [
                'duration_minutes' => $duration,
                'price' => round($price, 2),
            ];
        }

        $this->syncStarterSelections(
            $salon,
            $typeIds,
            array_values(array_unique(array_filter((array) ($data['starter_categories'] ?? []), fn ($v) => is_string($v) && $v !== ''))),
            $selectedServiceKeys,
            $serviceOverrides,
            $request->has('starter_services')
        );

        return $this->redirectAfterSettingsSave($request, 'Service setup updated.', 'services');
    }

    public function updateHours(Request $request)
    {
        $this->abortUnlessCanEditSalonWideSettings();
        $salon = $this->salon();

        $data = $request->validate([
            'hours' => ['required', 'array'],
        ]);

        $salon->update(['opening_hours' => $data['hours']]);

        return $this->redirectAfterSettingsSave($request, 'Opening hours updated.', 'hours');
    }

    public function updateNotifications(Request $request)
    {
        $this->abortUnlessCanEditSalonWideSettings();
        $salon = $this->salon();
        $definitions = NotificationConfigService::definitions();

        $rules = [
            'qh_enabled' => ['sometimes', 'boolean'],
            'qh_from'    => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'qh_to'      => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'qh_mode'    => ['nullable', 'in:skip,delay'],
        ];

        foreach ($definitions as $id => $def) {
            $rules["notification_rules.{$id}.enabled"] = ['sometimes', 'boolean'];
            if (($def['timing'] ?? '') === 'scheduled') {
                $rules["notification_rules.{$id}.offset_hours"] = ['nullable', 'integer', 'min:1', 'max:168'];
            }
            if (in_array('email', $def['channels'] ?? [], true)) {
                $rules["notification_templates.{$id}.email_subject"] = ['nullable', 'string', 'max:200'];
                $rules["notification_templates.{$id}.email_body"] = ['nullable', 'string', 'max:8000'];
            }
            if (in_array('sms', $def['channels'] ?? [], true)) {
                $rules["notification_templates.{$id}.sms_body"] = ['nullable', 'string', 'max:640'];
            }
        }

        $request->validate($rules);

        $payload = [
            'version'     => 2,
            'rules'       => [],
            'templates'   => [],
            'quiet_hours' => [
                'enabled' => $request->boolean('qh_enabled'),
                'from'    => $request->input('qh_from', '22:00'),
                'to'      => $request->input('qh_to', '07:00'),
                'mode'    => $request->input('qh_mode', 'skip') === 'delay' ? 'delay' : 'skip',
            ],
        ];

        foreach ($definitions as $id => $def) {
            $payload['rules'][$id] = [
                'enabled' => $request->boolean("notification_rules.{$id}.enabled"),
                'offset_hours' => ($def['timing'] ?? '') === 'scheduled'
                    ? (int) $request->input("notification_rules.{$id}.offset_hours", $def['default_offset_hours'] ?? 24)
                    : null,
            ];

            $tplIn = (array) $request->input("notification_templates.{$id}", []);
            $tplOut = [];
            if (in_array('email', $def['channels'] ?? [], true)) {
                foreach (['email_subject', 'email_body'] as $k) {
                    if (isset($tplIn[$k]) && $tplIn[$k] !== null) {
                        $tplOut[$k] = (string) $tplIn[$k];
                    }
                }
            }
            if (in_array('sms', $def['channels'] ?? [], true) && array_key_exists('sms_body', $tplIn)) {
                $tplOut['sms_body'] = (string) $tplIn['sms_body'];
            }
            if ($tplOut !== []) {
                $payload['templates'][$id] = $tplOut;
            }
        }

        app(NotificationConfigService::class)->persist($salon, $payload);

        return $this->redirectAfterSettingsSave($request, 'Notification settings updated.', 'notifications');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->merge([
            'locale' => $request->filled('locale') ? $request->input('locale') : null,
            'timezone' => $request->filled('timezone') ? $request->input('timezone') : null,
        ]);

        $langCodes = LanguageProficiency::allowedCodes();

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'     => ['nullable', 'string', 'max:20'],
            'experience' => ['nullable', 'string', 'max:120'],
            'language_proficiency'   => ['nullable', 'array', 'max:30'],
            'language_proficiency.*' => ['string', Rule::in($langCodes)],
            'timezone'  => ['nullable', 'string', 'timezone:all'],
            'locale'    => ['nullable', 'string', 'in:' . implode(',', array_keys(\App\Support\DisplayFormatter::localeOptions()))],
        ]);

        $data['language_proficiency'] = LanguageProficiency::encode($data['language_proficiency'] ?? []);

        $user->update($data);

        return $this->redirectAfterSettingsSave($request, 'Profile updated.', 'profile');
    }

    public function updateTeamMembers(Request $request)
    {
        $this->abortUnlessCanEditSalonWideSettings();
        $salon = $this->salon();

        $langCodes = LanguageProficiency::allowedCodes();
        $singleSave = $request->boolean('save_single_team_member');

        $data = $request->validate([
            'staff_members'          => [$singleSave ? 'required' : 'nullable', 'array', $singleSave ? 'max:1' : 'max:10'],
            'staff_members.*.id'     => ['nullable', 'integer'],
            'staff_members.*.name'   => ['nullable', 'string', 'max:100'],
            'staff_members.*.email'  => ['nullable', 'email', 'max:150'],
            'staff_members.*.phone'  => ['nullable', 'string', 'max:20'],
            'staff_members.*.role'   => ['nullable', 'in:owner,manager,stylist,therapist,receptionist,junior'],
            'staff_members.*.commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'staff_members.*.bio'    => ['nullable', 'string', 'max:1000'],
            'staff_members.*.experience' => ['nullable', 'string', 'max:120'],
            'staff_members.*.language_proficiency'   => ['nullable', 'array', 'max:30'],
            'staff_members.*.language_proficiency.*' => ['string', Rule::in($langCodes)],
            'staff_members.*.color'  => ['nullable', 'string', 'max:7'],
            'staff_members.*.assign_services' => ['nullable'],
        ]);

        $rows = $data['staff_members'] ?? [];
        $rows = array_values(array_filter($rows, fn ($row) => is_array($row)));

        if ($singleSave) {
            if (count($rows) !== 1) {
                throw ValidationException::withMessages([
                    'staff_members' => ['Submit one team member at a time from this form.'],
                ]);
            }
            $rows = $this->mergeSingleTeamMemberPayloadIntoFullList($salon, $rows[0]);
        }

        $rows = $this->normalizeTeamMemberLanguageProficiencyForSync($rows);

        $this->syncTeamMembers($salon, $rows);

        $message = $singleSave ? 'Team member saved.' : 'Team members updated.';

        return $this->redirectAfterSettingsSave($request, $message, 'profile');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:8', 'confirmed'],
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return $this->redirectAfterSettingsSave($request, 'Password changed successfully.', 'security');
    }

    public function updateSocialLinks(Request $request)
    {
        $this->abortUnlessCanEditSalonWideSettings();
        $salon = $this->salon();

        $platforms = ['instagram', 'facebook', 'tiktok', 'whatsapp', 'google', 'twitter', 'youtube', 'linkedin', 'pinterest'];

        $rules = [];
        foreach ($platforms as $p) {
            $rules["social_links.{$p}"] = ['nullable', 'url', 'max:300'];
        }

        $data = $request->validate($rules);

        // Build clean array — only keep non-empty URLs
        $links = [];
        foreach ($platforms as $p) {
            $url = $data['social_links'][$p] ?? null;
            if ($url) {
                $links[$p] = $url;
            }
        }

        $salon->update(['social_links' => $links]);

        return $this->redirectAfterSettingsSave($request, 'Social links updated.', 'social');
    }

    /**
     * @param  list<int>  $typeIds
     * @param  list<string>  $selectedCategories
     * @param  list<string>  $selectedServices
     * @param  array<string, array<string, mixed>>  $serviceOverrides
     * @param  bool  $pruneUnmatchedStarters When false, starter-catalog rows are not removed: the form
     *               omits starter_services when no service checkboxes are checked, so saves that only
     *               change business types or categories must not wipe the catalog.
     */
    private function syncStarterSelections(\App\Models\Salon $salon, array $typeIds, array $selectedCategories, array $selectedServices, array $serviceOverrides = [], bool $pruneUnmatchedStarters = false): void
    {
        $allowedCategoryKeys = RegistrationStarterServices::allowedCategoryKeysForTypeIds($typeIds);
        foreach ($selectedCategories as $key) {
            if (! in_array($key, $allowedCategoryKeys, true)) {
                throw ValidationException::withMessages([
                    'starter_categories' => ['One or more selected categories are not valid for your business types.'],
                ]);
            }
        }

        $allowedServiceKeys = RegistrationStarterServices::allowedKeysForTypeIds($typeIds);
        foreach ($selectedServices as $key) {
            if (! in_array($key, $allowedServiceKeys, true)) {
                throw ValidationException::withMessages([
                    'starter_services' => ['One or more selected services are not valid for your business types.'],
                ]);
            }
        }

        RegistrationStarterServices::seedStarterCategories($salon, $selectedCategories);
        RegistrationStarterServices::seedSalon($salon, $selectedServices, $serviceOverrides);

        $catalog = (array) config('registration_starter_services');
        $types = BusinessType::query()->whereIn('id', $typeIds)->get(['id', 'slug'])->keyBy('slug');
        $serviceDefinitions = [];
        foreach ($catalog as $slug => $rows) {
            $type = $types->get((string) $slug);
            if (! $type) {
                continue;
            }
            foreach ((array) $rows as $row) {
                $key = (string) ($row['key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $composite = $slug . ':' . $key;
                $serviceDefinitions[$composite] = [
                    'slug' => (string) $slug,
                    'business_type_id' => (int) $type->id,
                    'name' => trim((string) ($row['name'] ?? '')),
                ];
            }
        }

        if ($serviceDefinitions === [] || ! $pruneUnmatchedStarters) {
            return;
        }

        $selectedKeyMap = array_fill_keys($selectedServices, true);
        $existingServices = $this->servicesForSalonSettings($salon)
            ->whereIn('business_type_id', $typeIds)
            ->get(['id', 'business_type_id', 'name', 'duration_minutes', 'price']);

        foreach ($existingServices as $service) {
            $matchedKey = null;
            foreach ($serviceDefinitions as $composite => $def) {
                if ((int) $def['business_type_id'] !== (int) $service->business_type_id) {
                    continue;
                }
                if ($this->starterServiceNameMatches((string) $service->name, (string) $def['name'], (string) ($def['slug'] ?? ''))) {
                    $matchedKey = $composite;
                    break;
                }
            }

            if ($matchedKey !== null && ! isset($selectedKeyMap[$matchedKey])) {
                $service->delete();
            }
        }
    }

    /**
     * @param  array<string, mixed>  $incoming  One validated form row (checkboxes, arrays, etc.)
     * @return array<int, array<string, mixed>>
     */
    private function mergeSingleTeamMemberPayloadIntoFullList(\App\Models\Salon $salon, array $incoming): array
    {
        $base = $this->teamMemberRowsForSyncFromDatabase($salon);
        $incId = isset($incoming['id']) ? (int) $incoming['id'] : 0;

        if ($incId > 0) {
            foreach ($base as $k => $row) {
                if ((int) ($row['id'] ?? 0) === $incId) {
                    $base[$k] = array_merge($row, $incoming);

                    return array_values($base);
                }
            }

            throw ValidationException::withMessages([
                'staff_members.0.id' => ['That team member could not be found for this location.'],
            ]);
        }

        $base[] = $incoming;

        return array_values($base);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalizeTeamMemberLanguageProficiencyForSync(array $rows): array
    {
        foreach ($rows as $i => $row) {
            if (! is_array($row)) {
                continue;
            }
            $lp = $row['language_proficiency'] ?? null;
            if (is_string($lp)) {
                $rows[$i]['language_proficiency'] = $lp;
            } else {
                $encoded = LanguageProficiency::encode(is_array($lp) ? $lp : []);
                $rows[$i]['language_proficiency'] = $encoded;
            }
        }

        return $rows;
    }

    /**
     * Same shape as the profile team-member form rows, for merging single saves without dropping others.
     *
     * @return array<int, array<string, mixed>>
     */
    private function teamMemberRowsForSyncFromDatabase(\App\Models\Salon $salon): array
    {
        return Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where(function ($q) use ($salon) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', '!=', (int) $salon->owner_id);
            })
            ->orderBy('sort_order')
            ->get()
            ->map(function (Staff $member) {
                $hasServices = DB::table('service_staff')
                    ->where('staff_id', $member->id)
                    ->exists();

                return [
                    'id' => $member->id,
                    'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'role' => $member->role,
                    'experience' => $member->experience,
                    'language_proficiency' => $member->language_proficiency,
                    'commission_rate' => $member->commission_rate,
                    'color' => $member->color ?: '#7C3AED',
                    'bio' => $member->bio,
                    'assign_services' => $hasServices ? '1' : '0',
                ];
            })
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncTeamMembers(\App\Models\Salon $salon, array $rows): void
    {
        $rows = array_values(array_filter($rows, fn ($row) => is_array($row) && trim((string) ($row['name'] ?? '')) !== ''));

        $services = Service::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->orderBy('sort_order')
            ->get(['id', 'allowed_roles']);

        $defaultColors = ['#7C3AED', '#EC4899', '#0EA5E9', '#14B8A6', '#F59E0B', '#84CC16'];
        $maxSort = (int) Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->max('sort_order');
        $keptIds = [];

        foreach ($rows as $i => $row) {
            if (empty($row['role']) || ! is_string($row['role'])) {
                throw ValidationException::withMessages([
                    "staff_members.{$i}.role" => ['Choose a role for each team member you add.'],
                ]);
            }

            $name = trim((string) $row['name']);
            $parts = preg_split('/\s+/u', $name, 2, PREG_SPLIT_NO_EMPTY) ?: [];
            $first = (string) ($parts[0] ?? '');
            $last = (string) ($parts[1] ?? '');
            $initials = mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
            $initials = $initials !== '' ? mb_substr($initials, 0, 3) : '??';

            $color = isset($row['color']) && is_string($row['color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $row['color'])
                ? $row['color']
                : $defaultColors[$i % count($defaultColors)];
            $assign = ($row['assign_services'] ?? null) == '1' || ($row['assign_services'] ?? null) === 1 || ($row['assign_services'] ?? null) === true;
            $commission = isset($row['commission_rate']) ? (float) $row['commission_rate'] : 0.0;
            $commission = max(0.0, min(100.0, $commission));
            $role = (string) $row['role'];
            $serviceIds = $services
                ->filter(fn (Service $service) => $service->allowsStaffRole($role))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $staff = null;
            $id = isset($row['id']) ? (int) $row['id'] : 0;
            if ($id > 0) {
                $staff = Staff::withoutGlobalScopes()
                    ->where('salon_id', $salon->id)
                    ->where('id', $id)
                    ->first();
            }

            $roleName = $this->mapStaffRoleToLoginRole((string) $row['role']);
            $linkedUser = $this->resolveOrCreateStaffUser($salon->id, $first, $last, $row, $roleName);

            if ($staff) {
                $staff->update([
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $this->optionalString($row['email'] ?? null),
                    'phone' => $this->optionalString($row['phone'] ?? null),
                    'role' => $role,
                    'bio' => $this->optionalString($row['bio'] ?? null),
                    'experience' => $this->optionalString($row['experience'] ?? null),
                    'language_proficiency' => $this->optionalString(is_string($row['language_proficiency'] ?? null) ? $row['language_proficiency'] : null),
                    'user_id' => $linkedUser?->id,
                    'color' => $color,
                    'commission_rate' => $commission,
                    'initials' => $initials,
                    'is_active' => true,
                    'bookable_online' => true,
                ]);
            } else {
                $staff = Staff::create([
                    'salon_id' => $salon->id,
                    'user_id' => $linkedUser?->id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $this->optionalString($row['email'] ?? null),
                    'phone' => $this->optionalString($row['phone'] ?? null),
                    'role' => $role,
                    'bio' => $this->optionalString($row['bio'] ?? null),
                    'experience' => $this->optionalString($row['experience'] ?? null),
                    'language_proficiency' => $this->optionalString(is_string($row['language_proficiency'] ?? null) ? $row['language_proficiency'] : null),
                    'color' => $color,
                    'commission_rate' => $commission,
                    'initials' => $initials,
                    'sort_order' => ++$maxSort,
                    'is_active' => true,
                    'bookable_online' => true,
                ]);
            }

            if ($assign && $serviceIds !== []) {
                $staff->services()->sync($serviceIds);
            } else {
                $staff->services()->sync([]);
            }
            $keptIds[] = (int) $staff->id;
        }

        $toDelete = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where(function ($q) use ($salon) {
                $q->whereNull('user_id')
                    ->orWhere('user_id', '!=', (int) $salon->owner_id);
            })
            ->when($keptIds !== [], fn ($q) => $q->whereNotIn('id', $keptIds))
            ->get();

        foreach ($toDelete as $member) {
            if ($member->user_id) {
                User::query()->where('id', (int) $member->user_id)->update(['is_active' => false]);
            }
            $member->delete();
        }
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  list<int>  $typeIds
     * @return list<string>
     */
    private function selectedStarterCategoryKeysForSalon(\App\Models\Salon $salon, array $catalog, array $typeIds): array
    {
        if ($typeIds === []) {
            return [];
        }

        $typeById = BusinessType::query()->whereIn('id', $typeIds)->get(['id', 'slug'])->keyBy('id');
        $selected = [];
        $categories = $this->serviceCategoriesForSalonSettings($salon)->whereIn('business_type_id', $typeIds)->get(['business_type_id', 'slug']);
        foreach ($categories as $cat) {
            $type = $typeById->get((int) $cat->business_type_id);
            if (! $type) {
                continue;
            }
            $rows = (array) ($catalog[$type->slug] ?? []);
            $slugs = [];
            foreach ($rows as $row) {
                $catSlug = trim((string) ($row['category_slug'] ?? Str::slug((string) ($row['category'] ?? 'General'))));
                $slugs[$catSlug !== '' ? $catSlug : 'general'] = true;
            }
            $slug = trim((string) $cat->slug);
            if (isset($slugs[$slug])) {
                $selected[] = $type->slug . ':' . $slug;
            }
        }

        return array_values(array_unique($selected));
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  list<int>  $typeIds
     * @return list<string>
     */
    private function selectedStarterServiceKeysForSalon(\App\Models\Salon $salon, array $catalog, array $typeIds): array
    {
        if ($typeIds === []) {
            return [];
        }

        $types = BusinessType::query()->whereIn('id', $typeIds)->get(['id', 'slug'])->keyBy('id');
        $definitions = [];
        foreach ($types as $type) {
            foreach ((array) ($catalog[$type->slug] ?? []) as $row) {
                $key = (string) ($row['key'] ?? '');
                if ($key === '') {
                    continue;
                }
                $definitions[] = [
                    'composite' => $type->slug . ':' . $key,
                    'slug' => (string) $type->slug,
                    'business_type_id' => (int) $type->id,
                    'name' => trim((string) ($row['name'] ?? '')),
                ];
            }
        }

        $selected = [];
        $services = $this->servicesForSalonSettings($salon)->whereIn('business_type_id', $typeIds)->get(['business_type_id', 'name']);
        foreach ($services as $service) {
            foreach ($definitions as $def) {
                if ((int) $def['business_type_id'] !== (int) $service->business_type_id) {
                    continue;
                }
                if ($this->starterServiceNameMatches((string) $service->name, (string) $def['name'], (string) ($def['slug'] ?? ''))) {
                    $selected[] = $def['composite'];
                    break;
                }
            }
        }

        return array_values(array_unique($selected));
    }

    /**
     * @param  array<string, mixed>  $catalog
     * @param  list<int>  $typeIds
     * @return array<string, mixed>
     */
    private function selectedStarterServiceMetaForSalon(\App\Models\Salon $salon, array $catalog, array $typeIds): array
    {
        if ($typeIds === []) {
            return [];
        }

        $types = BusinessType::query()->whereIn('id', $typeIds)->get(['id', 'slug'])->keyBy('id');
        $definitions = [];
        foreach ($types as $type) {
            foreach ((array) ($catalog[$type->slug] ?? []) as $row) {
                $key = trim((string) ($row['key'] ?? ''));
                $name = trim((string) ($row['name'] ?? ''));
                if ($key === '' || $name === '') {
                    continue;
                }
                $definitions[] = [
                    'composite' => $type->slug . ':' . $key,
                    'slug' => (string) $type->slug,
                    'business_type_id' => (int) $type->id,
                    'name' => $name,
                ];
            }
        }

        if ($definitions === []) {
            return [];
        }

        $meta = [];
        $services = $this->servicesForSalonSettings($salon)
            ->whereIn('business_type_id', $typeIds)
            ->get(['business_type_id', 'name', 'duration_minutes', 'price']);

        foreach ($services as $service) {
            $serviceName = trim((string) $service->name);
            foreach ($definitions as $def) {
                if ((int) $def['business_type_id'] !== (int) $service->business_type_id) {
                    continue;
                }
                $baseName = (string) $def['name'];
                $composite = (string) $def['composite'];
                $slug = (string) $def['slug'];
                if ($slug === 'unisex') {
                    if ($serviceName === $baseName . ' (Men)') {
                        $meta[$composite]['men'] = [
                            'duration_minutes' => (int) $service->duration_minutes,
                            'price' => round((float) $service->price, 2),
                        ];
                        break;
                    }
                    if ($serviceName === $baseName . ' (Women)') {
                        $meta[$composite]['women'] = [
                            'duration_minutes' => (int) $service->duration_minutes,
                            'price' => round((float) $service->price, 2),
                        ];
                        break;
                    }
                    continue;
                }

                if ($serviceName === $baseName) {
                    $meta[$composite] = [
                        'duration_minutes' => (int) $service->duration_minutes,
                        'price' => round((float) $service->price, 2),
                    ];
                    break;
                }
            }
        }

        return $meta;
    }

    private function mapStaffRoleToLoginRole(string $staffRole): string
    {
        return match (strtolower(trim($staffRole))) {
            'owner' => 'tenant_admin',
            'manager' => 'manager',
            'receptionist' => 'receptionist',
            default => 'stylist',
        };
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function resolveOrCreateStaffUser(int $salonId, string $firstName, string $lastName, array $row, string $roleName): ?User
    {
        $email = $this->optionalString($row['email'] ?? null);
        if ($email === null) {
            return null;
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();
        if (! $user) {
            $displayName = trim($firstName . ' ' . $lastName);
            if ($displayName === '') {
                $displayName = $email;
            }
            $user = User::query()->create([
                'name' => $displayName,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'is_active' => true,
                'timezone' => null,
                'locale' => null,
            ]);
        }

        $staffProfile = Staff::withoutGlobalScopes()->where('user_id', $user->id)->first();
        if ($staffProfile && (int) $staffProfile->salon_id !== $salonId) {
            throw ValidationException::withMessages([
                'staff_members' => ['A team member email is already linked to another business. Use a different email.'],
            ]);
        }

        $user->syncRoles([$roleName]);

        return $user;
    }

    private function optionalString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $out = trim((string) $value);

        return $out === '' ? null : $out;
    }

    private function starterServiceNameMatches(string $actualName, string $baseName, string $slug): bool
    {
        $actual = trim($actualName);
        $base = trim($baseName);
        if ($actual === '' || $base === '') {
            return false;
        }
        if ($actual === $base) {
            return true;
        }
        if ($slug !== 'unisex') {
            return false;
        }

        return in_array($actual, [$base . ' (Men)', $base . ' (Women)'], true);
    }

    private function redirectAfterSettingsSave(Request $request, string $message, string $tab)
    {
        $returnTo = trim((string) $request->input('return_to', ''));
        if ($returnTo === '') {
            return back()->with('success', $message)->with('tab', $tab);
        }

        $parsed = parse_url($returnTo);
        if ($parsed === false) {
            return back()->with('success', $message)->with('tab', $tab);
        }

        if (isset($parsed['host']) && strcasecmp((string) $parsed['host'], $request->getHost()) !== 0) {
            return back()->with('success', $message)->with('tab', $tab);
        }

        $path = (string) ($parsed['path'] ?? '');
        $query = isset($parsed['query']) && is_string($parsed['query']) ? $parsed['query'] : '';

        if ($path === '' || ! str_starts_with($path, '/') || str_starts_with($path, '//')) {
            return back()->with('success', $message)->with('tab', $tab);
        }

        // return_to often carries the full path from the browser (e.g. /vellor/public/dashboard).
        // redirect() prepends the app URL again, which already includes the base path — strip once
        // so we pass a path relative to the application root (e.g. /dashboard).
        $basePath = rtrim($request->getBasePath(), '/') ?: '';
        if ($basePath !== '') {
            if ($path === $basePath || $path === $basePath . '/') {
                $path = '/';
            } elseif (str_starts_with($path, $basePath . '/')) {
                $path = substr($path, strlen($basePath)) ?: '/';
            }
        }

        $target = $path . ($query !== '' ? ('?' . $query) : '');

        return redirect($target)->with('success', $message);
    }

    /**
     * @param  list<string>  $names
     * @return list<int>
     */
    private function ensureCustomBusinessTypes(array $names): array
    {
        if ($names === []) {
            return [];
        }

        $ids = [];
        $nextSort = (int) BusinessType::query()->max('sort_order');
        foreach ($names as $name) {
            $slugBase = Str::slug($name);
            if ($slugBase === '') {
                continue;
            }
            $slug = $slugBase;
            $n = 1;
            while (BusinessType::query()->where('slug', $slug)->exists()) {
                $existing = BusinessType::query()->where('slug', $slug)->first();
                if ($existing && strcasecmp((string) $existing->name, $name) === 0) {
                    $ids[] = (int) $existing->id;
                    continue 2;
                }
                $slug = $slugBase . '-' . $n;
                $n++;
            }

            $type = BusinessType::query()->create([
                'name' => $name,
                'slug' => $slug,
                'sort_order' => ++$nextSort,
            ]);
            $ids[] = (int) $type->id;
        }

        return array_values(array_unique($ids));
    }
}
