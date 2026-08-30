<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * velour:tenants — Landlord-context tenant management command.
 *
 * Intentionally runs OUTSIDE any tenant scope (withoutGlobalScopes) so that
 * it can see all salons regardless of whether a tenant is "current".
 *
 * Usage:
 *   php artisan velour:tenants list
 *   php artisan velour:tenants show {id}
 *   php artisan velour:tenants activate {id}
 *   php artisan velour:tenants suspend {id}
 *   php artisan velour:tenants set-domain {id} {domain}
 *   php artisan velour:tenants set-subdomain {id} {subdomain}
 */
class TenantsCommand extends Command
{
    protected $signature = 'velour:tenants
                            {action : list|show|activate|suspend|set-domain|set-subdomain}
                            {id?    : Tenant (Salon) ID — required for all actions except list}
                            {value? : Domain or subdomain value for set-domain / set-subdomain}';

    protected $description = 'Landlord tenant management (list, activate, suspend, domain mapping)';

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'list'         => $this->listTenants(),
            'show'         => $this->showTenant(),
            'activate'     => $this->setActive(true),
            'suspend'      => $this->setActive(false),
            'set-domain'   => $this->setDomain(),
            'set-subdomain'=> $this->setSubdomain(),
            default        => $this->unknownAction(),
        };
    }

    // -------------------------------------------------------------------------

    private function listTenants(): int
    {
        $tenants = Tenant::withoutGlobalScopes()
            ->orderBy('id')
            ->get(['id', 'name', 'subdomain', 'domain', 'is_active', 'created_at']);

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Subdomain', 'Custom Domain', 'Active', 'Created'],
            $tenants->map(fn ($t) => [
                $t->id,
                $t->name,
                $t->subdomain ?? '—',
                $t->domain    ?? '—',
                $t->is_active ? '<fg=green>✓</>' : '<fg=red>✗</>',
                $t->created_at->format('Y-m-d'),
            ])->all()
        );

        $this->line('Total: ' . $tenants->count() . ' tenant(s)');

        return self::SUCCESS;
    }

    private function showTenant(): int
    {
        $tenant = $this->resolveTenant();
        if (! $tenant) return self::FAILURE;

        $this->info("Tenant #{$tenant->id}: {$tenant->name}");
        $this->line('');

        $rows = [
            ['ID',            $tenant->id],
            ['Name',          $tenant->name],
            ['Slug',          $tenant->slug],
            ['Subdomain',     $tenant->subdomain    ?? '—'],
            ['Custom Domain', $tenant->domain       ?? '—'],
            ['Email',         $tenant->email        ?? '—'],
            ['Phone',         $tenant->phone        ?? '—'],
            ['Plan',          $tenant->owner?->plan ?? '—'],
            ['Active',        $tenant->is_active ? 'Yes' : 'No'],
            ['Timezone',      $tenant->timezone],
            ['Currency',      $tenant->currency],
            ['Created',       $tenant->created_at->format('Y-m-d H:i:s')],
            ['Subdomain URL', $tenant->subdomainUrl()],
        ];

        if ($tenant->domain) {
            $rows[] = ['Custom URL', 'https://' . $tenant->domain];
        }

        $this->table(['Attribute', 'Value'], $rows);

        // Stats
        $appts   = \App\Models\Appointment::withoutGlobalScopes()->where('salon_id', $tenant->id)->count();
        $clients = \App\Models\Client::withoutGlobalScopes()->where('salon_id', $tenant->id)->count();
        $staff   = \App\Models\Staff::withoutGlobalScopes()->where('salon_id', $tenant->id)->count();

        $this->line('');
        $this->line("  Appointments: {$appts}  |  Clients: {$clients}  |  Staff: {$staff}");

        return self::SUCCESS;
    }

    private function setActive(bool $active): int
    {
        $tenant = $this->resolveTenant();
        if (! $tenant) return self::FAILURE;

        $word = $active ? 'activate' : 'suspend';

        if (! $this->confirm("Are you sure you want to {$word} \"{$tenant->name}\"?")) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        $tenant->update(['is_active' => $active]);

        $status = $active ? '<fg=green>activated</>' : '<fg=red>suspended</>';
        $this->line("Tenant \"{$tenant->name}\" has been {$status}.");

        return self::SUCCESS;
    }

    private function setDomain(): int
    {
        $tenant = $this->resolveTenant();
        if (! $tenant) return self::FAILURE;

        $domain = $this->argument('value');

        if (! $domain) {
            $domain = $this->ask('Enter the custom domain (e.g. bookings.mysalon.com)');
        }

        // Basic validation
        if (! preg_match('/^[a-z0-9\.\-]+\.[a-z]{2,}$/', strtolower($domain))) {
            $this->error("Invalid domain: {$domain}");
            return self::FAILURE;
        }

        // Check uniqueness
        $existing = Tenant::withoutGlobalScopes()->where('domain', $domain)->where('id', '!=', $tenant->id)->first();
        if ($existing) {
            $this->error("Domain \"{$domain}\" is already in use by \"{$existing->name}\" (ID: {$existing->id}).");
            return self::FAILURE;
        }

        $tenant->update(['domain' => strtolower($domain)]);

        $this->info("Custom domain set: {$domain}");
        $this->line("CNAME record required: {$domain} → " . config('app.base_domain', 'velour.app'));

        return self::SUCCESS;
    }

    private function setSubdomain(): int
    {
        $tenant = $this->resolveTenant();
        if (! $tenant) return self::FAILURE;

        $subdomain = $this->argument('value');

        if (! $subdomain) {
            $subdomain = $this->ask('Enter the subdomain slug (e.g. mysalon)');
        }

        $subdomain = Str::slug($subdomain);

        // Check uniqueness
        $existing = Tenant::withoutGlobalScopes()->where('subdomain', $subdomain)->where('id', '!=', $tenant->id)->first();
        if ($existing) {
            $this->error("Subdomain \"{$subdomain}\" is already in use by \"{$existing->name}\".");
            return self::FAILURE;
        }

        $tenant->update(['subdomain' => $subdomain]);

        $base = config('app.base_domain', 'velour.app');
        $this->info("Subdomain updated: https://{$subdomain}.{$base}");

        return self::SUCCESS;
    }

    private function unknownAction(): int
    {
        $this->error('Unknown action: ' . $this->argument('action'));
        $this->line('Available: list, show, activate, suspend, set-domain, set-subdomain');
        return self::FAILURE;
    }

    private function resolveTenant(): ?Tenant
    {
        $id = $this->argument('id');

        if (! $id) {
            $this->error('Tenant ID is required for this action.');
            return null;
        }

        $tenant = Tenant::withoutGlobalScopes()->find($id);

        if (! $tenant) {
            $this->error("Tenant not found with ID: {$id}");
            return null;
        }

        return $tenant;
    }
}
