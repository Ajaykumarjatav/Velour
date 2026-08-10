# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: auth-flow.spec.ts >> Auth flows >> owner can log in
- Location: e2e\auth-flow.spec.ts:15:3

# Error details

```
Test timeout of 120000ms exceeded.
```

```
Error: page.waitForLoadState: Test timeout of 120000ms exceeded.
```

# Test source

```ts
  1  | import { expect, type Page } from '@playwright/test';
  2  | 
  3  | export const E2E = {
  4  |   ownerEmail: process.env.E2E_OWNER_EMAIL || 'ajayjatav439@gmail.com',
  5  |   ownerPassword: process.env.E2E_OWNER_PASSWORD || 'password',
  6  |   adminEmail: process.env.E2E_ADMIN_EMAIL || 'admin@velour.app',
  7  |   adminPassword: process.env.E2E_ADMIN_PASSWORD || 'password',
  8  |   store: process.env.E2E_STORE || 'ak',
  9  |   slug: process.env.E2E_SLUG || 'ak-salon',
  10 |   reviewToken:
  11 |     process.env.E2E_REVIEW_TOKEN ||
  12 |     'zOsdZgvYKq8KsukIIR8hY6d51Yv9BA5zwuU9WfAdWCNrdLnw',
  13 |   ids: {
  14 |     client: Number(process.env.E2E_CLIENT_ID || 155),
  15 |     staff: Number(process.env.E2E_STAFF_ID || 7),
  16 |     service: Number(process.env.E2E_SERVICE_ID || 90),
  17 |     appointment: Number(process.env.E2E_APPOINTMENT_ID || 303),
  18 |     inventory: Number(process.env.E2E_INVENTORY_ID || 59),
  19 |     pos: Number(process.env.E2E_POS_ID || 188),
  20 |     package: Number(process.env.E2E_PACKAGE_ID || 1),
  21 |     marketing: Number(process.env.E2E_MARKETING_ID || 9),
  22 |   },
  23 | };
  24 | 
  25 | /** Paths must NOT start with "/" — baseURL includes /vellor/admin/. */
  26 | export function path(p: string): string {
  27 |   return p.replace(/^\//, '');
  28 | }
  29 | 
  30 | export async function expectPageOk(page: Page, label: string) {
  31 |   const body = await page.locator('body').innerText().catch(() => '');
  32 |   const lowered = body.toLowerCase();
  33 |   const apache404 = lowered.includes('the requested url was not found on this server');
  34 |   const crashed =
  35 |     apache404 ||
  36 |     lowered.includes('internal server error') ||
  37 |     lowered.includes('whoops, something went wrong') ||
  38 |     (lowered.includes('illuminate\\') && lowered.includes('exception'));
  39 | 
  40 |   expect(crashed, `${label} looks like an error page (${page.url()})`).toBeFalsy();
  41 |   expect(body.trim().length, `${label} rendered empty body`).toBeGreaterThan(20);
  42 | }
  43 | 
  44 | export async function loginAs(page: Page, email: string, password: string) {
  45 |   await page.goto(path('login'));
  46 |   await page.locator('#login-email').fill(email);
  47 |   await page.locator('#login-password').fill(password);
  48 |   await page.locator('button[type="submit"], input[type="submit"]').first().click();
> 49 |   await page.waitForLoadState('domcontentloaded');
     |              ^ Error: page.waitForLoadState: Test timeout of 120000ms exceeded.
  50 |   await expect(page).not.toHaveURL(/\/login\/?$/);
  51 | }
  52 | 
  53 | export async function visitOk(page: Page, routePath: string, label?: string) {
  54 |   const response = await page.goto(path(routePath), { waitUntil: 'domcontentloaded', timeout: 90_000 });
  55 |   const status = response?.status() ?? 0;
  56 |   expect(status, `${label || routePath} returned ${status}`).toBeLessThan(400);
  57 |   await expectPageOk(page, label || routePath);
  58 | }
  59 | 
```