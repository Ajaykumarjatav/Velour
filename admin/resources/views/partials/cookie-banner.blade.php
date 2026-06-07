{{--
  Cookie Consent Banner — AUDIT FIX: GDPR Compliance
  Shown on first visit until the user accepts/configures.
  Hides once consent is recorded (both server-side and localStorage).
--}}

@unless(session('cookie_consent') === 'accepted')
<div id="cookie-banner"
     class="fixed bottom-0 left-0 right-0 z-50 p-4 md:p-6"
     style="display:none"
     x-data="cookieBanner()"
     x-init="init()"
     x-show="show"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0">

  <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-xl border border-gray-100 p-5 md:p-6">
    <div class="flex flex-col md:flex-row md:items-center gap-4">

      {{-- Icon + Text --}}
      <div class="flex items-start gap-3 flex-1">
        <span class="text-2xl mt-0.5">🍪</span>
        <div>
          <p class="font-semibold text-gray-800 text-sm mb-0.5">We use cookies</p>
          <p class="text-xs text-gray-500 leading-relaxed">
            We use essential cookies to make Velour work, and optional analytics cookies to help us improve it.
            <a href="{{ route('legal.cookies') }}" class="text-amber-600 underline">Learn more</a>
          </p>
        </div>
      </div>

      {{-- Actions --}}
      <div class="flex items-center gap-2 flex-shrink-0">
        <button @click="openPreferences = true"
          class="text-xs text-gray-500 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
          Preferences
        </button>
        <button @click="accept('essential')"
          class="text-xs text-gray-600 px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition">
          Essential only
        </button>
        <button @click="accept('all')"
          class="text-xs bg-amber-500 text-white px-4 py-2 rounded-lg font-semibold hover:bg-amber-600 transition">
          Accept all
        </button>
      </div>
    </div>

    {{-- Preference panel (expandable) --}}
    <div x-show="openPreferences" x-transition class="mt-4 pt-4 border-t border-gray-100">
      <div class="grid sm:grid-cols-2 gap-3">
        @foreach([
          ['essential', 'Essential', 'Required for login and security. Cannot be disabled.', true],
          ['functional', 'Functional', 'Remember your UI preferences (sidebar, calendar view).', false],
          ['analytics', 'Analytics', 'Anonymised usage data to help us improve Velour.', false],
          ['marketing', 'Marketing', 'Campaign attribution — never shared with third parties.', false],
        ] as [$key, $label, $desc, $required])
        <label class="flex items-start gap-3 p-3 rounded-xl border border-gray-100 hover:bg-gray-50 cursor-pointer"
          :class="{ 'opacity-60 cursor-not-allowed': {{ $required ? 'true' : 'false' }} }">
          <input type="checkbox"
            x-model="prefs.{{ $key }}"
            {{ $required ? 'disabled checked' : '' }}
            class="mt-1 rounded accent-amber-500">
          <div>
            <p class="text-sm font-medium text-gray-800">{{ $label }}
              @if($required)<span class="text-xs text-amber-600 ml-1">(required)</span>@endif
            </p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
          </div>
        </label>
        @endforeach
      </div>
      <div class="flex justify-end mt-3">
        <button @click="savePreferences()"
          class="text-sm bg-gray-900 text-white px-5 py-2 rounded-xl font-semibold hover:bg-gray-700 transition">
          Save preferences
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function cookieBanner() {
  return {
    show: false,
    openPreferences: false,
    prefs: { essential: true, functional: false, analytics: false, marketing: false },

    init() {
      if (! localStorage.getItem('velour_cookie_consent')) {
        this.show = true;
      }
      document.addEventListener('open-cookie-banner', () => { this.show = true; });
    },

    accept(level) {
      if (level === 'all') {
        this.prefs = { essential: true, functional: true, analytics: true, marketing: true };
      }
      this.savePreferences();
    },

    savePreferences() {
      localStorage.setItem('velour_cookie_consent', JSON.stringify(this.prefs));
      fetch('{{ route("legal.cookie-consent") }}', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
        },
        body: JSON.stringify(this.prefs)
      });
      this.show = false;
    }
  }
}
</script>
@endunless
