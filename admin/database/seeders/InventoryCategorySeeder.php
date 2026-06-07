<?php

namespace Database\Seeders;

use App\Models\Salon;
use App\Support\DefaultInventoryCatalog;
use Illuminate\Database\Seeder;

class InventoryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $salons = Salon::withoutGlobalScopes()->orderBy('id')->get();
        foreach ($salons as $salon) {
            DefaultInventoryCatalog::ensureCategoriesForSalon((int) $salon->id);
        }

        $this->command->info('   ✓  Default inventory categories ensured for '.$salons->count().' store(s).');
    }
}
