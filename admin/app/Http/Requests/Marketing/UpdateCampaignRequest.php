<?php

namespace App\Http\Requests\Marketing;

use App\Models\MarketingCampaign;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        $campaign = $this->route('campaign') ?? $this->route('marketing');
        return $campaign instanceof MarketingCampaign
            ? $this->user()->can('update', $campaign)
            : $this->user()->can('create', MarketingCampaign::class);
    }

    public function rules(): array
    {
        return [
            'name'           => ['sometimes', 'string', 'max:255'],
            'subject'        => ['nullable', 'string', 'max:255'],
            'content'        => ['nullable', 'string', 'max:50000'],
            'template'       => ['nullable', 'string', 'max:100'],
            'offer_details'  => ['nullable', 'array'],
            'target'         => ['sometimes', 'in:all,vip,lapsed,new,birthday,custom,segment'],
            'target_filters' => ['nullable', 'array'],
            'scheduled_at'   => ['nullable', 'date', 'after:now'],
            'status'         => ['sometimes', 'in:draft,scheduled,sending,sent,cancelled'],
        ];
    }
}
