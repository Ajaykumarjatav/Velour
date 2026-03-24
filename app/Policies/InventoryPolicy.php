<?php
namespace App\Policies;
use App\Models\InventoryItem;
use App\Models\User;

class InventoryPolicy
{
    private function salonId(): int { return request()->attributes->get('salon_id'); }
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, InventoryItem $item): bool { return $item->salon_id === $this->salonId(); }
    public function create(User $user): bool { return true; }
    public function update(User $user, InventoryItem $item): bool { return $item->salon_id === $this->salonId(); }
    public function delete(User $user, InventoryItem $item): bool {
        return $item->salon_id === $this->salonId()
            && in_array(request()->attributes->get('access_level'), ['owner','manager']);
    }
}
