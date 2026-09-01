<?php

namespace Database\Seeders\Concerns;

use App\Models\Salon;

trait ResolvesDemoSalon
{
    protected function demoSalon(): ?Salon
    {
        return Salon::withoutGlobalScopes()
            ->where('slug', 'maison-lumiere')
            ->first();
    }

    protected function requireDemoSalon(): ?Salon
    {
        $salon = $this->demoSalon();
        if (! $salon && $this->command) {
            $this->command->warn('   ↷ Demo salon (maison-lumiere) not found — skipping '.class_basename(static::class).'.');
        }

        return $salon;
    }
}
