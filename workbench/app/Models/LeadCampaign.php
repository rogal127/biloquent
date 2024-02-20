<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadCampaign extends Model
{
    protected $fillable = ['name'];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
