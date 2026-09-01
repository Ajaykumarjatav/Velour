<?php

use App\Models\Staff;
use App\Support\StaffJobRoles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (StaffJobRoles::LEGACY_MAP as $from => $to) {
            Staff::withoutGlobalScopes()
                ->where('role', $from)
                ->update(['role' => $to]);
        }

        $this->migrateAllowedRolesJson('services', 'allowed_roles');
        $this->migrateAllowedRolesJson('service_packages', 'allowed_roles');
    }

    public function down(): void
    {
        $reverse = array_flip(StaffJobRoles::LEGACY_MAP);

        foreach ($reverse as $from => $to) {
            Staff::withoutGlobalScopes()
                ->where('role', $from)
                ->update(['role' => $to]);
        }

        $this->migrateAllowedRolesJson('services', 'allowed_roles', true);
        $this->migrateAllowedRolesJson('service_packages', 'allowed_roles', true);
    }

    private function migrateAllowedRolesJson(string $table, string $column, bool $reverse = false): void
    {
        $map = $reverse ? array_flip(StaffJobRoles::LEGACY_MAP) : StaffJobRoles::LEGACY_MAP;

        DB::table($table)->whereNotNull($column)->orderBy('id')->chunkById(100, function ($rows) use ($table, $column, $map) {
            foreach ($rows as $row) {
                $decoded = json_decode($row->{$column} ?? '[]', true);
                if (! is_array($decoded) || $decoded === []) {
                    continue;
                }

                $next = [];
                foreach ($decoded as $role) {
                    $slug = strtolower(trim((string) $role));
                    if ($slug === '') {
                        continue;
                    }
                    $mapped = $map[$slug] ?? $slug;
                    $next[$mapped] = true;
                }

                DB::table($table)->where('id', $row->id)->update([
                    $column => json_encode(array_keys($next)),
                ]);
            }
        });
    }
};
