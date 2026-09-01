<?php
namespace App\Policies;
use App\Models\MarketingCampaign;
use App\Models\User;

class MarketingCampaignPolicy
{
    private function salonId(): int { return request()->attributes->get('salon_id'); }
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, MarketingCampaign $campaign): bool { return $campaign->salon_id === $this->salonId(); }
    public function create(User $user): bool {
        return in_array(request()->attributes->get('access_level'), ['owner','manager']);
    }
    public function update(User $user, MarketingCampaign $campaign): bool {
        return $campaign->salon_id === $this->salonId()
            && in_array(request()->attributes->get('access_level'), ['owner','manager']);
    }
    public function delete(User $user, MarketingCampaign $campaign): bool {
        return $campaign->salon_id === $this->salonId()
            && in_array(request()->attributes->get('access_level'), ['owner','manager']);
    }
    public function send(User $user, MarketingCampaign $campaign): bool {
        return $campaign->salon_id === $this->salonId()
            && request()->attributes->get('access_level') === 'owner';
    }
}
