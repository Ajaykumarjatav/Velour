<?php
namespace App\Jobs;
use App\Models\InventoryItem;
use App\Models\SalonNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CheckLowStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function handle(): void
    {
        $lowBysalon = InventoryItem::whereColumn('stock_quantity', '<', 'min_stock_level')
            ->where('is_active', true)
            ->select('salon_id', DB::raw('count(*) as count'))
            ->groupBy('salon_id')
            ->get();

        foreach ($lowBysalon as $row) {
            $existing = SalonNotification::where('salon_id', $row->salon_id)
                ->where('type', 'stock_alert')
                ->where('is_read', false)
                ->where('created_at', '>=', now()->subHours(20))
                ->exists();

            if (! $existing) {
                SalonNotification::create([
                    'salon_id' => $row->salon_id,
                    'type'     => 'stock_alert',
                    'title'    => 'Low Stock Alert',
                    'body'     => "{$row->count} product(s) are below minimum stock level.",
                    'data'     => ['count' => $row->count],
                    'action_url' => '/inventory?filter=low_stock',
                ]);
            }
        }
    }
}
