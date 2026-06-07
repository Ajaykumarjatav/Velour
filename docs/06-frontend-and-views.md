# Frontend and views

## Admin app (Blade)

- **Layout:** `resources/views/layouts/app.blade.php` — sidebar, header, dark mode, alerts.
- **Auth layout:** `resources/views/layouts/auth.blade.php`
- **Styling:** Tailwind CSS (Vite build from project root `package.json`).
- **Interactivity:** Alpine.js (`x-data`, `x-show`) on calendar, attendance hub, POS, dashboard widgets.

### View folders

See [README.md](README.md) table. Each feature folder maps to a `Web\*Controller`:

| Folder | UI |
|--------|-----|
| `appointments/` | Create, edit, show, list |
| `calendar/` | Index + `partials/staff-sidebar-grid.blade.php` |
| `clients/` | CRM list/show |
| `availability/` | Tabs: resources, leave, **attendance** partial |
| `pos/` | Checkout flow |
| `reports/` | Per-type report blades (`revenue.blade.php`, etc.) |
| `marketing/` | Campaign builder |
| `settings/` | Salon configuration |
| `partials/` | Sidebar, chatbot, shared snippets |

### Blade components

`resources/views/components/`:

| Component | Purpose |
|-----------|---------|
| `staff-avatar` | Avatar with initials fallback + `onerror` |
| `searchable-select` | Remote/local searchable dropdown |
| Others | Buttons, cards matching design system |

Use `<x-staff-avatar :staff="$member" />` instead of raw `<img>` for staff photos.

### CSS conventions (layouts/app.blade.php)

- `.form-input`, `.form-select`, `.form-label` — forms
- `.btn-primary`, `.btn-outline` — actions
- `.stat-card`, `.data-table`, `.table-wrap` — data UI
- `.alert-info`, `.alert-success`, `.alert-warning` — banners (`alert-info` is **flex** — use `flex-col` when stacking multiple paragraphs)

### Assets

- Built to `public/build/` via Vite.
- User uploads: `storage/app/public/` → public URL via `storage:link`.

## Public salon website (React)

**Path:** `salon-website/`

| File | Role |
|------|------|
| `src/main.jsx` | Entry |
| `src/App.jsx` | SalonProvider; marketing vs `#book` |
| `src/context/SalonContext.jsx` | Fetch `GET /api/v1/salon/{slug}/website` |
| `src/lib/bookingApi.js` | Public booking API |
| `src/components/BookingFlow.jsx` | Booking UX |

**Build:** `npm run build` → `public/website/`  
**Served by:** `StorefrontController` at `/s/{slug}`

**Dev proxy:** Vite proxies `/api` to Laravel (`VITE_API_PROXY_TARGET`).

## API consumers

Mobile or third-party apps use Sanctum tokens + `routes/api.php` JSON endpoints. Resources in `app/Http/Resources/` shape responses.

## JavaScript patterns

- **Attendance:** `availability/partials/attendance.blade.php` — Alpine `attendanceHub`, `fetch()` JSON to `availability.*` routes.
- **Calendar:** Server-rendered grid + optional client scripts in `calendar/index.blade.php`.
- **POS:** Large inline Alpine state in `pos/create.blade.php`.

## Localization

- Salon `locale` and user display locale fields on `users` / `staff`.
- Helpers: `CurrencyHelper`, `TimezoneHelper`, `@money`, `@currencyLabel` Blade directives.

## Adding a new screen

1. Controller returns `view('feature.action', compact(...))`.
2. Extend `layouts.app`.
3. Use existing form/table classes.
4. Register route and `SidebarNav` if needed.
