<?php
namespace App\Policies;
use App\Models\InventoryItem;
use App\Models\Tenant;
use App\Models\User;

class InventoryPolicy
{
    /** Matches {@see TenantScope} when a tenant is current; else API request attributes. */
    private function expectedSalonId(): ?int
    {
        if (Tenant::checkCurrent()) {
            return (int) Tenant::current()->getKey();
        }

        $fromRequest = request()->attributes->get('salon_id');

        return $fromRequest !== null ? (int) $fromRequest : null;
    }

    public function viewAny(User $user): bool { return true; }
    public function view(User $user, InventoryItem $item): bool
    {
        $sid = $this->expectedSalonId();

        return $sid !== null && (int) $item->salon_id === $sid;
    }
    public function create(User $user): bool { return true; }
    public function update(User $user, InventoryItem $item): bool
    {
        return $this->view($user, $item);
    }
    public function delete(User $user, InventoryItem $item): bool {
        return $this->view($user, $item)
            && in_array(request()->attributes->get('access_level'), ['owner','manager'], true);
    }
}
