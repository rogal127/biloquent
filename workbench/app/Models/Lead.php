<?php

namespace Workbench\App\Models;


use Workbench\App\Models\LeadCampaign;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = ['email'];

    public function lead_campaign(): BelongsTo
    {
        return $this->belongsTo(LeadCampaign::class);
    }
}
