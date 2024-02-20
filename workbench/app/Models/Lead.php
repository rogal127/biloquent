<?php

namespace Workbench\App\Models;


use Workbench\App\Models\LeadCampaign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = ['email', 'customer_id', 'lead_campaign_id'];

    public function lead_campaign(): BelongsTo
    {
        return $this->belongsTo(LeadCampaign::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
