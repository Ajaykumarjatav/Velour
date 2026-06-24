<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\PosTransaction;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

final class AdminPlatformReportExportService
{
    /** @var array<string, string> */
    public const TYPES = [
        'owners' => 'Owner accounts',
        'stores' => 'All stores',
        'clients' => 'Clients',
        'appointments' => 'Appointments',
        'revenue' => 'Revenue / POS',
        'staff' => 'Staff',
        'services' => 'Services',
        'inventory' => 'Inventory',
        'expenses' => 'Expenses',
    ];

    public function supports(string $type): bool
    {
        return array_key_exists($type, self::TYPES);
    }

    public function download(string $type, ?Request $request = null): StreamedResponse
    {
        $filename = $type . '-platform-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($type, $request): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            $this->writeCsv($type, $out, $request);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadZip(?Request $request = null): BinaryFileResponse
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'platform-reports-');
        if ($zipPath === false) {
            abort(500, 'Could not create export archive.');
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::OVERWRITE) !== true) {
            @unlink($zipPath);
            abort(500, 'Could not create export archive.');
        }

        $date = now()->format('Y-m-d');
        foreach (array_keys(self::TYPES) as $type) {
            $stream = fopen('php://temp', 'r+');
            if ($stream === false) {
                continue;
            }
            fprintf($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));
            $this->writeCsv($type, $stream, $request);
            rewind($stream);
            $content = stream_get_contents($stream) ?: '';
            fclose($stream);
            $zip->addFromString("{$type}-{$date}.csv", $content);
        }

        $zip->close();

        return response()->download($zipPath, "platform-reports-{$date}.zip", [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * @param resource $out
     */
    private function writeCsv(string $type, $out, ?Request $request): void
    {
        match ($type) {
            'owners' => $this->writeOwners($out),
            'stores' => $this->writeStores($out),
            'clients' => $this->writeClients($out),
            'appointments' => $this->writeAppointments($out, $request),
            'revenue' => $this->writeRevenue($out, $request),
            'staff' => $this->writeStaff($out),
            'services' => $this->writeServices($out),
            'inventory' => $this->writeInventory($out),
            'expenses' => $this->writeExpenses($out, $request),
            default => abort(404),
        };
    }

    /** @return array<int, array{owner: string, owner_email: string, name: string, slug: string}> */
    private function salonIndex(): array
    {
        static $index = null;
        if ($index !== null) {
            return $index;
        }

        $index = [];
        Salon::withoutGlobalScopes()
            ->with('owner:id,name,email')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'owner_id'])
            ->each(function (Salon $salon) use (&$index): void {
                $index[(int) $salon->id] = [
                    'owner' => (string) ($salon->owner?->name ?? ''),
                    'owner_email' => (string) ($salon->owner?->email ?? ''),
                    'name' => (string) $salon->name,
                    'slug' => (string) ($salon->slug ?? ''),
                ];
            });

        return $index;
    }

    /** @return list<string> */
    private function storeContextHeaders(): array
    {
        return ['Owner', 'Owner email', 'Store', 'Store slug'];
    }

    /**
     * @param array{owner: string, owner_email: string, name: string, slug: string} $ctx
     * @return list<string>
     */
    private function storeContextRow(array $ctx): array
    {
        return [$ctx['owner'], $ctx['owner_email'], $ctx['name'], $ctx['slug']];
    }

    /** @param resource $out */
    private function writeOwners($out): void
    {
        fputcsv($out, ['Owner', 'Email', 'Plan', 'Total stores', 'Active stores', 'Joined']);

        User::query()
            ->whereHas('salons', fn ($q) => $q->withoutGlobalScopes())
            ->withCount([
                'salons as stores_count' => fn ($q) => $q->withoutGlobalScopes(),
                'salons as active_stores_count' => fn ($q) => $q->withoutGlobalScopes()->where('is_active', true),
            ])
            ->orderBy('name')
            ->chunk(100, function ($owners) use ($out): void {
                foreach ($owners as $owner) {
                    fputcsv($out, [
                        $owner->name,
                        $owner->email,
                        $owner->plan ?? '',
                        $owner->stores_count,
                        $owner->active_stores_count,
                        $owner->created_at?->toDateString() ?? '',
                    ]);
                }
            });
    }

    /** @param resource $out */
    private function writeStores($out): void
    {
        fputcsv($out, ['Store', 'Slug', 'City', 'Owner', 'Owner email', 'Plan', 'Staff', 'Clients', 'Appointments', 'Status', 'Created']);

        Salon::withoutGlobalScopes()
            ->with('owner:id,name,email,plan')
            ->withCount(['staff', 'clients', 'appointments'])
            ->orderBy('name')
            ->chunk(50, function ($salons) use ($out): void {
                foreach ($salons as $salon) {
                    fputcsv($out, [
                        $salon->name,
                        $salon->slug,
                        $salon->city ?? '',
                        $salon->owner?->name,
                        $salon->owner?->email,
                        $salon->owner?->plan,
                        $salon->staff_count,
                        $salon->clients_count,
                        $salon->appointments_count,
                        $salon->is_active ? 'Active' : 'Suspended',
                        $salon->created_at?->toDateString() ?? '',
                    ]);
                }
            });
    }

    /** @param resource $out */
    private function writeClients($out): void
    {
        fputcsv($out, array_merge($this->storeContextHeaders(), ['First name', 'Last name', 'Email', 'Phone', 'Marketing consent', 'Created']));

        $index = $this->salonIndex();

        Client::withoutGlobalScopes()
            ->orderBy('salon_id')
            ->orderBy('first_name')
            ->chunk(250, function ($clients) use ($out, $index): void {
                foreach ($clients as $client) {
                    $ctx = $index[(int) $client->salon_id] ?? ['owner' => '', 'owner_email' => '', 'name' => '', 'slug' => ''];
                    fputcsv($out, array_merge($this->storeContextRow($ctx), [
                        $client->first_name,
                        $client->last_name,
                        $client->email,
                        $client->phone,
                        $client->marketing_consent ? 'Yes' : 'No',
                        $client->created_at?->toDateString() ?? '',
                    ]));
                }
            });
    }

    /** @param resource $out */
    private function writeAppointments($out, ?Request $request): void
    {
        fputcsv($out, array_merge($this->storeContextHeaders(), ['Reference', 'Client', 'Staff', 'Starts at', 'Status', 'Payment', 'Total', 'Paid']));

        $index = $this->salonIndex();
        $query = Appointment::withoutGlobalScopes()
            ->with(['client:id,first_name,last_name', 'staff:id,first_name,last_name'])
            ->orderByDesc('starts_at');

        if ($request?->filled('from')) {
            $query->whereDate('starts_at', '>=', $request->string('from'));
        }
        if ($request?->filled('to')) {
            $query->whereDate('starts_at', '<=', $request->string('to'));
        }

        $query->chunk(250, function ($appointments) use ($out, $index): void {
            foreach ($appointments as $apt) {
                $ctx = $index[(int) $apt->salon_id] ?? ['owner' => '', 'owner_email' => '', 'name' => '', 'slug' => ''];
                $clientName = $apt->client
                    ? trim($apt->client->first_name . ' ' . $apt->client->last_name)
                    : '';
                $staffName = $apt->staff
                    ? trim($apt->staff->first_name . ' ' . $apt->staff->last_name)
                    : '';

                fputcsv($out, array_merge($this->storeContextRow($ctx), [
                    $apt->reference,
                    $clientName,
                    $staffName,
                    $apt->starts_at?->toIso8601String() ?? '',
                    $apt->status,
                    $apt->payment_status ?? '',
                    $apt->total_price,
                    $apt->amount_paid,
                ]));
            }
        });
    }

    /** @param resource $out */
    private function writeRevenue($out, ?Request $request): void
    {
        fputcsv($out, array_merge($this->storeContextHeaders(), ['Reference', 'Completed at', 'Total', 'Payment method', 'Staff', 'Client', 'Status']));

        $index = $this->salonIndex();
        $query = PosTransaction::withoutGlobalScopes()
            ->with(['client:id,first_name,last_name', 'staff:id,first_name,last_name'])
            ->orderByRaw('COALESCE(completed_at, created_at) DESC');

        if ($request?->filled('from')) {
            $query->whereDate(DB::raw('COALESCE(completed_at, created_at)'), '>=', $request->string('from'));
        }
        if ($request?->filled('to')) {
            $query->whereDate(DB::raw('COALESCE(completed_at, created_at)'), '<=', $request->string('to'));
        }

        $query->chunk(250, function ($transactions) use ($out, $index): void {
            foreach ($transactions as $tx) {
                $ctx = $index[(int) $tx->salon_id] ?? ['owner' => '', 'owner_email' => '', 'name' => '', 'slug' => ''];
                $at = $tx->completed_at ?? $tx->created_at;
                $clientName = $tx->client
                    ? trim($tx->client->first_name . ' ' . $tx->client->last_name)
                    : 'Walk-in';
                $staffName = $tx->staff
                    ? trim($tx->staff->first_name . ' ' . $tx->staff->last_name)
                    : '';

                fputcsv($out, array_merge($this->storeContextRow($ctx), [
                    $tx->reference,
                    $at?->toIso8601String() ?? '',
                    $tx->total,
                    $tx->payment_method,
                    $staffName,
                    $clientName,
                    $tx->status,
                ]));
            }
        });
    }

    /** @param resource $out */
    private function writeStaff($out): void
    {
        fputcsv($out, array_merge($this->storeContextHeaders(), ['Staff name', 'Role', 'Email', 'Phone', 'Active', 'Commission %']));

        $index = $this->salonIndex();

        Staff::withoutGlobalScopes()
            ->orderBy('salon_id')
            ->orderBy('first_name')
            ->chunk(250, function ($staffRows) use ($out, $index): void {
                foreach ($staffRows as $member) {
                    $ctx = $index[(int) $member->salon_id] ?? ['owner' => '', 'owner_email' => '', 'name' => '', 'slug' => ''];
                    fputcsv($out, array_merge($this->storeContextRow($ctx), [
                        trim($member->first_name . ' ' . $member->last_name),
                        $member->role ?? '',
                        $member->email ?? '',
                        $member->phone ?? '',
                        $member->is_active ? 'Yes' : 'No',
                        $member->commission_rate,
                    ]));
                }
            });
    }

    /** @param resource $out */
    private function writeServices($out): void
    {
        fputcsv($out, array_merge($this->storeContextHeaders(), ['Service', 'Category', 'Duration (min)', 'Price', 'Active']));

        $index = $this->salonIndex();

        Service::withoutGlobalScopes()
            ->with('category:id,name')
            ->orderBy('salon_id')
            ->orderBy('name')
            ->chunk(250, function ($services) use ($out, $index): void {
                foreach ($services as $service) {
                    $ctx = $index[(int) $service->salon_id] ?? ['owner' => '', 'owner_email' => '', 'name' => '', 'slug' => ''];
                    fputcsv($out, array_merge($this->storeContextRow($ctx), [
                        $service->name,
                        $service->category?->name ?? '',
                        $service->duration_minutes,
                        $service->price,
                        $service->is_active ? 'Yes' : 'No',
                    ]));
                }
            });
    }

    /** @param resource $out */
    private function writeInventory($out): void
    {
        fputcsv($out, array_merge($this->storeContextHeaders(), ['Product', 'Category', 'SKU', 'Stock', 'Min', 'Retail price']));

        $index = $this->salonIndex();

        InventoryItem::withoutGlobalScopes()
            ->with('category:id,name')
            ->orderBy('salon_id')
            ->orderBy('name')
            ->chunk(250, function ($items) use ($out, $index): void {
                foreach ($items as $item) {
                    $ctx = $index[(int) $item->salon_id] ?? ['owner' => '', 'owner_email' => '', 'name' => '', 'slug' => ''];
                    fputcsv($out, array_merge($this->storeContextRow($ctx), [
                        $item->name,
                        $item->category?->name ?? '',
                        $item->sku ?? '',
                        $item->stock_quantity,
                        $item->min_stock_level,
                        $item->retail_price,
                    ]));
                }
            });
    }

    /** @param resource $out */
    private function writeExpenses($out, ?Request $request): void
    {
        fputcsv($out, array_merge($this->storeContextHeaders(), ['Date', 'Title', 'Category', 'Vendor', 'Amount', 'Status']));

        $index = $this->salonIndex();
        $query = Expense::withoutGlobalScopes()
            ->with('category:id,name')
            ->orderByDesc('expense_date');

        if ($request?->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->string('from'));
        }
        if ($request?->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->string('to'));
        }

        $query->chunk(250, function ($expenses) use ($out, $index): void {
            foreach ($expenses as $expense) {
                $ctx = $index[(int) $expense->salon_id] ?? ['owner' => '', 'owner_email' => '', 'name' => '', 'slug' => ''];
                fputcsv($out, array_merge($this->storeContextRow($ctx), [
                    $expense->expense_date?->toDateString() ?? '',
                    $expense->title,
                    $expense->category?->name ?? '',
                    $expense->vendor ?? '',
                    $expense->amount,
                    $expense->status,
                ]));
            }
        });
    }
}
