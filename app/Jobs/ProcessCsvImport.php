<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessCsvImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300;

    public function __construct(
        public readonly string $type,    // 'clients' | 'inventory'
        public readonly string $filePath,
        public readonly int    $salonId,
        public readonly int    $userId,
    ) {}

    public function handle(): void
    {
        if (! Storage::exists($this->filePath)) {
            Log::error('CSV import file not found', ['path' => $this->filePath]);
            return;
        }

        $content = Storage::get($this->filePath);
        $rows    = array_map('str_getcsv', explode("\n", trim($content)));
        $headers = array_map('trim', array_shift($rows));

        $imported = 0;
        $failed   = 0;

        foreach ($rows as $row) {
            if (empty(array_filter($row))) continue;

            $data = array_combine($headers, $row);
            if (! $data) { $failed++; continue; }

            try {
                match ($this->type) {
                    'clients'   => $this->importClient($data),
                    'inventory' => $this->importInventory($data),
                    default     => throw new \InvalidArgumentException("Unknown import type: {$this->type}"),
                };
                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning("CSV import row failed", ['error' => $e->getMessage(), 'row' => $data]);
            }
        }

        Storage::delete($this->filePath);
        Log::info("CSV import complete", ['type' => $this->type, 'imported' => $imported, 'failed' => $failed]);
    }

    private function importClient(array $data): void
    {
        \App\Models\Client::firstOrCreate(
            ['salon_id' => $this->salonId, 'email' => $data['email'] ?? null],
            [
                'first_name' => $data['first_name'] ?? 'Unknown',
                'last_name'  => $data['last_name']  ?? '',
                'phone'      => $data['phone']       ?? null,
                'source'     => 'import',
                'color'      => '#' . substr(md5($data['email'] ?? rand()), 0, 6),
            ]
        );
    }

    private function importInventory(array $data): void
    {
        \App\Models\InventoryItem::updateOrCreate(
            ['salon_id' => $this->salonId, 'sku' => $data['sku'] ?? null],
            [
                'name'            => $data['name']           ?? 'Unknown',
                'cost_price'      => $data['cost_price']     ?? 0,
                'retail_price'    => $data['retail_price']   ?? 0,
                'stock_quantity'  => $data['stock_quantity'] ?? 0,
                'min_stock_level' => $data['min_stock']      ?? 0,
                'type'            => 'professional',
            ]
        );
    }
}
