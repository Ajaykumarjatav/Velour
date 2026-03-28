<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index()
    {
        $salon    = $this->salon();
        $settings = $salon->settings()->pluck('value', 'key');
        $user     = Auth::user();

        return view('settings.index', compact('salon', 'settings', 'user'));
    }

    public function updateSalon(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:150'],
            'email'         => ['nullable', 'email', 'max:150'],
            'phone'         => ['nullable', 'string', 'max:20'],
            'website'       => ['nullable', 'url', 'max:200'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'address_line1' => ['nullable', 'string', 'max:200'],
            'address_line2' => ['nullable', 'string', 'max:200'],
            'city'          => ['nullable', 'string', 'max:100'],
            'county'        => ['nullable', 'string', 'max:100'],
            'postcode'      => ['nullable', 'string', 'max:20'],
            'country'       => ['nullable', 'string', 'max:2'],
            'timezone'      => ['required', 'string', 'timezone'],
            'currency'      => ['required', 'string', 'size:3', 'in:' . implode(',', array_keys(\App\Helpers\CurrencyHelper::all()))],
        ]);

        $salon->update($data);

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

        $settings = [
            'email_appointment_confirmation' => $request->boolean('email_appointment_confirmation'),
            'email_appointment_reminder'     => $request->boolean('email_appointment_reminder'),
            'sms_appointment_reminder'       => $request->boolean('sms_appointment_reminder'),
            'reminder_hours_before'          => (int) $request->get('reminder_hours_before', 24),
            'email_new_client'               => $request->boolean('email_new_client'),
        ];

        foreach ($settings as $key => $value) {
            $salon->settings()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Notification settings updated.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
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
}
