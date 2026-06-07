# EasyGrox Marketing Site

React marketing website for **EasyGrox** — salon management software.

## Source of truth: `velour.jsx`

All components, pages, and design tokens are defined in **`velour.jsx`** at the project root. The `src/` folder is generated from that file.

**Workflow:**

1. Edit `velour.jsx` (add or change functions, pages, tokens).
2. Run `npm run sync` to regenerate `src/`.
3. Run `npm run dev` to preview.

Do not hand-edit generated files under `src/` unless you plan to copy changes back into `velour.jsx`.

## Module map

| `velour.jsx` | Generated path |
|--------------|----------------|
| `C`, `F`, `R` | `src/constants/theme.js` |
| `useW` | `src/hooks/useWindowWidth.js` |
| `I`, `Ic` | `src/components/icons/` |
| `Chip`, `Btn`, `H1`… | `src/components/ui/` |
| `DashMock`, `BookingMock` | `src/components/mockups/` |
| `Nav`, `Footer`, `FeaturePageShell` | `src/components/layout/` |
| `StatBadge`, `PainGain`, mocks… | `src/components/shared/marketingBlocks.jsx` |
| `Home`, `Pricing`, `Signup`, … | `src/pages/` |
| `AppointmentsPage` … `WebsitePage` | `src/pages/features/` |
| `export default function App` | *(not synced)* — use `src/App.jsx` + `src/constants/routes.js` |

## URLs (React Router)

Each page has its own path under the Vite base (`/velour-store/` in production). Examples:

| Page | Path |
|------|------|
| Home | `/` |
| Pricing | `/pricing` |
| Features overview | `/features` |
| Appointments | `/features/appointments` |
| Barber Shop (business) | `/business/barber-shop` |
| Help Centre | `/help-centre` |
| Contact | `/contact` |

Routing is configured in `src/App.jsx`. `npm run sync` does **not** overwrite `App.jsx`.

Hostinger: `public/.htaccess` rewrites unknown paths to `index.html` so deep links work.

## Commands

```bash
npm install
npm run sync    # after editing velour.jsx
npm run dev     # http://localhost:5173
npm run build
```
