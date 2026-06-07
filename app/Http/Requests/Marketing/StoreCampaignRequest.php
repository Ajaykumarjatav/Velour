<?php
namespace App\Http\Requests\Marketing;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\MarketingCampaign::class);
    }
    public function rules(): array
    {
        return [
            'name'           => ['required','string','max:255'],
            'subject'        => ['nullable','string','max:255'],
            'type'           => ['required','in:email,sms,push,offer,recall,birthday,win_back'],
            'content'        => ['nullable','string','max:50000'],
            'template'       => ['nullable','string','max:100'],
            'offer_details'  => ['nullable','array'],
            'target'         => ['required','in:all,vip,lapsed,new,birthday,custom,segment'],
            'target_filters' => ['nullable','array'],
            'scheduled_at'   => ['nullable','date','after:now'],
        ];
    }
}
