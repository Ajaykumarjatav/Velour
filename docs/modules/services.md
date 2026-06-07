# Services and packages

## Purpose

Service catalog: categories, pricing, duration, variants/add-ons, dynamic pricing rules, staff assignment, service packages (bundles).

## Key files

| Layer | Path |
|-------|------|
| Services | `Web\ServiceController`, `Api\ServiceController` |
| Categories | `Web\ServiceCategoryController` |
| Packages | `Web\ServicePackageController` |
| Models | `Service`, `ServiceCategory`, `ServicePackage`, `DynamicPricingRule` |
| Support | `StaffServiceEligibility`, `ServicePlaceholderGlyph` |
| Views | `resources/views/services/*`, `service-packages/*` |

## Routes

- `services` resource + `PUT services/pricing-rules`, `PUT services/{id}/variants`
- `service-categories.*` — CRUD
- `service-packages` resource (no show)
- API: reorder, duplicate, toggle, assignStaff

## Features

- **Variants/add-ons** — JSON on `services` table (`2026_04_04_000001_...`)
- **Dynamic pricing** — `dynamic_pricing_rules` table
- **Business types** — filter services/categories by `business_type_id`
- **Allowed roles** — which staff roles can perform service
- **Images** — `image` column; display in catalog and website payload

## Plan limits

`plan.limit:services` on create (web + quick-create).

## Permissions

`services.view`, create/update/delete variants per policy.

## Website

Services appear in `SalonWebsitePayloadService` → API `v1/salon/{slug}/website` → React `ServicesSection`.
