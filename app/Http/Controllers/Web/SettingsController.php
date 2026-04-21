<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BusinessType;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\SalonSetting;
use App\Services\NotificationConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SettingsController extends Controller
{
    use ResolvesActiveSalon;

    private function salon()
    {
        return $this->activeSalon();
    }

    public function index()
    {
        $salon    = $this->salon();
        $settings = $salon->settings()->pluck('value', 'key');
        $user     = Auth::user();

        $notificationDefinitions = NotificationConfigService::definitions();
        $settingsArr = $salon->settings()->pluck('value', 'key')->all();
        $notificationConfig = app(NotificationConfigService::class)->mergedConfigArray($salon, $settingsArr);

        $bookingTimeDisplay = $salon->getSetting('booking_time_display', 'business');
        $localeOptions = \App\Support\DisplayFormatter::localeOptions();
        $businessTypes = BusinessType::query()->orderBy('sort_order')->get();
        $selectedBusinessTypeIds = $salon->businessTypes()->pluck('business_types.id')->map(fn ($id) => (int) $id)->all();
        if ($selectedBusinessTypeIds === [] && $salon->business_type_id) {
            $selectedBusinessTypeIds = [(int) $salon->business_type_id];
        }

        return view('settings.index', compact(
            'salon',
            'settings',
            'user',
            'notificationDefinitions',
            'notificationConfig',
            'bookingTimeDisplay',
            'localeOptions',
            'businessTypes',
            'selectedBusinessTypeIds'
        ));
    }

    public function updateSalon(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:150'],
            'business_type_ids'    => ['required', 'array', 'min:1'],
            'business_type_ids.*'  => ['integer', 'exists:business_types,id'],
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
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['business_type_ids'])));
        unset($data['business_type_ids']);

        $currentPivotIds = $salon->businessTypes()->pluck('business_types.id')->map(fn ($id) => (int) $id)->all();
        $removed = array_diff($currentPivotIds, $ids);
        foreach ($removed as $rid) {
            if ($salon->services()->where('business_type_id', $rid)->exists()) {
                throw ValidationException::withMessages([
                    'business_type_ids' => ['Remove or reassign services that use a business type before you can remove that type.'],
                ]);
            }
        }

        $salon->businessTypes()->sync($ids);

        if (! in_array((int) $salon->business_type_id, $ids, true)) {
            $data['business_type_id'] = $ids[0];
        }

        $salon->update($data);

        SalonSetting::updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'booking_time_display'],
            ['value' => $request->input('booking_time_display', 'business'), 'type' => 'string']
        );

        return back()->with('success', 'Salon profile updated.');
    }

    public function updateHours(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'hours' => ['required', 'array'],
        ]);

        $salon->update(['opening_hours' => $data['hours']]);

        return back()->with('success', 'Opening hours updated.');
    }

    public function updateNotifications(Request $request)
    {
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

        return back()->with('success', 'Notification settings updated.')->with('tab', 'notifications');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->merge([
            'locale' => $request->filled('locale') ? $request->input('locale') : null,
            'timezone' => $request->filled('timezone') ? $request->input('timezone') : null,
        ]);

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:100'],
            'email'     => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'     => ['nullable', 'string', 'max:20'],
            'timezone'  => ['nullable', 'string', 'timezone:all'],
            'locale'    => ['nullable', 'string', 'in:' . implode(',', array_keys(\App\Support\DisplayFormatter::localeOptions()))],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated.');
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

        return back()->with('success', 'Password changed successfully.');
    }

    public function updateSocialLinks(Request $request)
    {
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

        return back()->with('success', 'Social links updated.')->with('tab', 'social');
    }
}
