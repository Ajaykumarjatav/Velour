<?php
namespace App\Console\Commands;

use App\Models\InventoryItem;
use App\Models\Salon;
use App\Models\SalonNotification;
use Illuminate\Console\Command;

class AlertLowStock extends Command
{
    protected $signature   = 'velour:alert-low-stock';
    protected $description = 'Send low stock alerts to salon owners';

    public function handle(): int
    {
        $salons = Salon::where('is_active', true)->get();

        foreach ($salons as $salon) {
            $lowStockItems = InventoryItem::where('salon_id', $salon->id)
                ->where('is_active', true)
                ->whereColumn('stock_quantity', '<', 'min_stock_level')
                ->get();

            if ($lowStockItems->isEmpty()) continue;

            $count = $lowStockItems->count();
            $names = $lowStockItems->take(3)->pluck('name')->join(', ');
            $extra  = $count - 3;
            $suffix = $count > 3 ? " and {$extra} more" : '';

            SalonNotification::create([
                'salon_id' => $salon->id,
                'type'     => 'stock_alert',
                'title'    => "{$count} item(s) below minimum stock",
                'body'     => "Low stock: {$names}{$suffix}. Consider placing a purchase order.",
                'data'     => ['count' => $count, 'item_ids' => $lowStockItems->pluck('id')],
                'action_url' => '/inventory?filter=low_stock',
            ]);

            $this->line("  ✓ Alert sent for salon: {$salon->name} ({$count} items)");
        }

        $this->info('Low stock alerts complete.');
        return Command::SUCCESS;
    }
}
