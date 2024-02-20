<?php

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $fillable = ['email'];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
