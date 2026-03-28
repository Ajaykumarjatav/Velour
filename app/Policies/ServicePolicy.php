<?php
namespace App\Policies;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    private function salonId(): int { return request()->attributes->get('salon_id'); }
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Service $service): bool { return $service->salon_id === $this->salonId(); }
    public function create(User $user): bool {
        return in_array(request()->attributes->get('access_level'), ['owner','manager']);
    }
    public function update(User $user, Service $service): bool {
        return $service->salon_id === $this->salonId()
            && in_array(request()->attributes->get('access_level'), ['owner','manager']);
    }
    public function delete(User $user, Service $service): bool {
        return $service->salon_id === $this->salonId()
            && in_array(request()->attributes->get('access_level'), ['owner','manager']);
    }
}
