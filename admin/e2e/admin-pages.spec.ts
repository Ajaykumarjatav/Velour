import { test } from '@playwright/test';
import { E2E, loginAs, visitOk } from './helpers';

const salonId = Number(process.env.E2E_SALON_ID || 2); // ak-salon

const ADMIN_PAGES: { path: string; label: string }[] = [
  { path: '/admin', label: 'admin.dashboard' },
  { path: '/admin/facilities', label: 'admin.facilities' },
  { path: '/admin/tenants', label: 'admin.tenants' },
  { path: `/admin/tenants/stores/${salonId}`, label: 'admin.tenants.show' },
  { path: `/admin/tenants/${salonId}/clients`, label: 'admin.tenant.clients' },
  { path: `/admin/tenants/${salonId}/appointments`, label: 'admin.tenant.appointments' },
  { path: `/admin/tenants/${salonId}/staff`, label: 'admin.tenant.staff' },
  { path: `/admin/tenants/${salonId}/pos`, label: 'admin.tenant.pos' },
  { path: `/admin/tenants/${salonId}/services`, label: 'admin.tenant.services' },
  { path: `/admin/tenants/${salonId}/inventory`, label: 'admin.tenant.inventory' },
  { path: `/admin/tenants/${salonId}/expenses`, label: 'admin.tenant.expenses' },
  { path: `/admin/tenants/${salonId}/reviews`, label: 'admin.tenant.reviews' },
  { path: `/admin/tenants/${salonId}/marketing`, label: 'admin.tenant.marketing' },
  { path: `/admin/tenants/${salonId}/leave`, label: 'admin.tenant.leave' },
  { path: `/admin/tenants/${salonId}/attendance`, label: 'admin.tenant.attendance' },
  { path: `/admin/tenants/${salonId}/settings`, label: 'admin.tenant.settings' },
  { path: `/admin/tenants/${salonId}/audit`, label: 'admin.tenant.audit' },
  { path: `/admin/tenants/${salonId}/deleted`, label: 'admin.tenant.deleted' },
  { path: '/admin/explorer', label: 'admin.explorer' },
  { path: '/admin/users', label: 'admin.users' },
  { path: '/admin/revenue', label: 'admin.revenue' },
  { path: '/admin/plans', label: 'admin.plans' },
  { path: '/admin/support', label: 'admin.support' },
  { path: '/admin/analytics', label: 'admin.analytics' },
  { path: '/admin/billing', label: 'admin.billing' },
  { path: '/admin/billing/webhooks', label: 'admin.billing.webhooks' },
  { path: '/admin/audit', label: 'admin.audit' },
  { path: '/admin/user-activity', label: 'admin.user-activity' },
];

test.describe('Super-admin pages', () => {
  test.beforeEach(async ({ page }) => {
    await loginAs(page, E2E.adminEmail, E2E.adminPassword);
  });

  for (const p of ADMIN_PAGES) {
    test(`GET ${p.label} → ${p.path}`, async ({ page }) => {
      await visitOk(page, p.path, p.label);
    });
  }
});
