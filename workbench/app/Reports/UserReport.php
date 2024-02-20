<?php

namespace Workbench\App\Reports;

use Webard\Biloquent\Report;
use Workbench\App\Models\Lead;
use Workbench\App\Models\Channel;
use Workbench\App\Models\Customer;
use Webard\Biloquent\Aggregators\Avg;
use Webard\Biloquent\Aggregators\Sum;
use Webard\Biloquent\Aggregators\Count;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserReport extends Report
{
    public $casts = [
        'total_leads' => 'integer',
    ];

    public function dataset(): Builder
    {
        return Customer::query();
    }

    public function aggregators(): array
    {
        return [
            'total_users' => Count::field('total_users', 'customers.id'),
        ];
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function groups(): array
    {
        return [
            'day' => ['aggregator' => 'DAY(customers_created_at)', 'field' => 'customers.created_at as customers_created_at'],
            'month' => ['aggregator' => 'MONTH(customers_created_at)', 'field' => 'customers.created_at as customers_created_at'],
            'year' => ['aggregator' => 'YEAR(customers_created_at)', 'field' => 'customers.created_at as customers_created_at'],
            'date' => ['aggregator' => 'DATE(customers_created_at)', 'field' => 'customers.created_at as customers_created_at'],
            'lead_campaign_id' => ['field' => 'leads.lead_campaign_id as lead_campaign_id', 'aggregator' => 'lead_campaign_id'],
        ];
    }
}
