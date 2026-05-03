<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Salon;
use App\Models\SalonSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CustomizationController extends Controller
{
    use ResolvesActiveSalon;

    private function salon(): Salon
    {
        return $this->activeSalon();
    }

    public function index(): View
    {
        $salon = $this->salon();
        $user = Auth::user();
        $settings = SalonSetting::withoutGlobalScopes()->where('salon_id', $salon->id)->pluck('value', 'key');
        $plan = $user->currentPlan();
        $savedForms = $this->decodeJsonList((string) ($settings['custom_forms_json'] ?? ''));
        $savedFeatureRequests = $this->decodeJsonList((string) ($settings['custom_feature_requests_json'] ?? ''));
        $liveFeatures = collect($savedFeatureRequests)->where('status', 'live')->count();

        $data = [
            'tagline' => (string) ($settings['custom_tagline'] ?? ''),
            'support_email' => (string) ($settings['custom_support_email'] ?? ($salon->email ?? '')),
            'primary_color' => (string) ($settings['custom_primary_color'] ?? '#A81A46'),
            'secondary_color' => (string) ($settings['custom_secondary_color'] ?? '#C88860'),
            'accent_color' => (string) ($settings['custom_accent_color'] ?? '#BD9850'),
            'languages' => array_values(array_filter(explode(',', (string) ($settings['custom_languages'] ?? 'English,Hindi')))),
            'custom_forms' => $savedForms === [] ? ['Client Intake', 'Consent Form', 'Patch Test Record'] : $savedForms,
            'custom_forms_count' => count($savedForms === [] ? ['Client Intake', 'Consent Form', 'Patch Test Record'] : $savedForms),
            'custom_feature_requests' => $savedFeatureRequests,
            'custom_features_live' => (int) $liveFeatures,
            'plan_label' => $plan->name,
            'plan_key' => $plan->key,
            'plan_features' => $plan->features,
            'plan_limits' => $plan->limits,
            'wl_remove_branding' => ($settings['wl_remove_branding'] ?? '1') === '1',
            'wl_custom_email_sender' => ($settings['wl_custom_email_sender'] ?? '1') === '1',
            'wl_custom_sms_sender' => ($settings['wl_custom_sms_sender'] ?? '0') === '1',
            'wl_custom_booking_url' => ($settings['wl_custom_booking_url'] ?? '1') === '1',
            'wl_mobile_app' => ($settings['wl_mobile_app'] ?? '0') === '1',
            'wl_custom_invoice_footer' => ($settings['wl_custom_invoice_footer'] ?? '1') === '1',
            'white_label_enabled' => (bool) $user->planAllows('white_label'),
            'custom_domain_enabled' => (bool) $user->planAllows('custom_domain'),
        ];

        return view('customization.index', compact('salon', 'data'));
    }

    public function updateBrand(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:150'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'custom_domain' => ['nullable', 'string', 'max:120'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:4096'],
        ]);

        $salon->name = $validated['business_name'];
        $salon->domain = $validated['custom_domain'] ?? $salon->domain;
        if (! empty($validated['support_email'])) {
            $salon->email = $validated['support_email'];
        }

        if ($request->hasFile('logo')) {
            if ($salon->logo && str_starts_with($salon->logo, 'salons/')) {
                Storage::disk('public')->delete($salon->logo);
            }
            $path = $request->file('logo')->store('salons/' . $salon->id . '/branding', 'public');
            $salon->logo = $path;
        }
        $salon->save();

        $toSave = [
            'custom_tagline' => $validated['tagline'] ?? '',
            'custom_support_email' => $validated['support_email'] ?? '',
            'custom_primary_color' => $validated['primary_color'] ?? '#A81A46',
            'custom_secondary_color' => $validated['secondary_color'] ?? '#C88860',
            'custom_accent_color' => $validated['accent_color'] ?? '#BD9850',
        ];
        foreach ($toSave as $key => $value) {
            SalonSetting::withoutGlobalScopes()->updateOrCreate(
                ['salon_id' => $salon->id, 'key' => $key],
                ['value' => (string) $value, 'type' => 'string']
            );
        }

        return redirect()->route('customization.index')->with('success', 'Brand identity updated.');
    }

    public function updateOptions(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $user = Auth::user();
        $validated = $request->validate([
            'wl_remove_branding' => ['nullable', 'boolean'],
            'wl_custom_email_sender' => ['nullable', 'boolean'],
            'wl_custom_sms_sender' => ['nullable', 'boolean'],
            'wl_custom_booking_url' => ['nullable', 'boolean'],
            'wl_mobile_app' => ['nullable', 'boolean'],
            'wl_custom_invoice_footer' => ['nullable', 'boolean'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'max:40'],
        ]);

        $boolKeys = [
            'wl_remove_branding',
            'wl_custom_email_sender',
            'wl_custom_sms_sender',
            'wl_custom_booking_url',
            'wl_mobile_app',
            'wl_custom_invoice_footer',
        ];
        foreach ($boolKeys as $key) {
            $isWhiteLabelKey = in_array($key, ['wl_custom_email_sender', 'wl_custom_sms_sender', 'wl_custom_booking_url', 'wl_mobile_app', 'wl_custom_invoice_footer'], true);
            if ($isWhiteLabelKey && ! $user->planAllows('white_label')) {
                continue;
            }
            if ($key === 'wl_remove_branding' && ! $user->planAllows('remove_branding')) {
                continue;
            }
            SalonSetting::withoutGlobalScopes()->updateOrCreate(
                ['salon_id' => $salon->id, 'key' => $key],
                ['value' => $request->boolean($key) ? '1' : '0', 'type' => 'boolean']
            );
        }

        $langs = $validated['languages'] ?? [];
        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'custom_languages'],
            ['value' => implode(',', $langs), 'type' => 'string']
        );

        return redirect()->route('customization.index')->with('success', 'Customization options updated.');
    }

    public function updateForms(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $validated = $request->validate([
            'forms' => ['nullable', 'array', 'max:20'],
            'forms.*' => ['nullable', 'string', 'max:120'],
        ]);

        $forms = collect($validated['forms'] ?? [])
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->values()
            ->all();

        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'custom_forms_json'],
            ['value' => json_encode($forms), 'type' => 'json']
        );
        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'custom_forms_count'],
            ['value' => (string) count($forms), 'type' => 'integer']
        );

        return redirect()->route('customization.index', ['tab' => 'forms'])->with('success', 'Custom forms updated.');
    }

    public function requestFeature(Request $request): RedirectResponse
    {
        $salon = $this->salon();
        $validated = $request->validate([
            'feature_title' => ['required', 'string', 'max:150'],
            'feature_description' => ['nullable', 'string', 'max:2000'],
        ]);

        $settings = SalonSetting::withoutGlobalScopes()->where('salon_id', $salon->id)->pluck('value', 'key');
        $requests = $this->decodeJsonList((string) ($settings['custom_feature_requests_json'] ?? ''));
        $requests[] = [
            'id' => (string) Str::uuid(),
            'title' => trim($validated['feature_title']),
            'description' => trim((string) ($validated['feature_description'] ?? '')),
            'status' => 'pending',
            'requested_at' => now()->toIso8601String(),
        ];

        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => 'custom_feature_requests_json'],
            ['value' => json_encode(array_values($requests)), 'type' => 'json']
        );

        return redirect()->route('customization.index', ['tab' => 'features'])->with('success', 'Feature request submitted.');
    }

    private function decodeJsonList(string $raw): array
    {
        if ($raw === '') {
            return [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? array_values($decoded) : [];
    }
}

