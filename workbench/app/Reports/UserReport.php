<?php

namespace Workbench\App\Reports;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webard\Biloquent\Aggregators\Avg;
use Webard\Biloquent\Aggregators\Count;
use Webard\Biloquent\Aggregators\Sum;
use Webard\Biloquent\Report;
use Workbench\App\Models\Channel;
use Workbench\App\Models\Order;

class UserReport extends Report
{
    public $casts = [
        'total_leads' => 'integer',
    ];

    public function dataset(): Builder
    {
        return Order::query();
    }

    public function aggregators(): array
    {
        return [
            'total_leads' => Count::field('total_leads', 'leads.id'),
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function groups(): array
    {
        return [
            'day' => ['aggregator' => 'DAY(orders_created_at)', 'field' => 'orders.created_at as orders_created_at'],
            'month' => ['aggregator' => 'MONTH(orders_created_at)', 'field' => 'orders.created_at as orders_created_at'],
            'year' => ['aggregator' => 'YEAR(orders_created_at)', 'field' => 'orders.created_at as orders_created_at'],
            'date' => ['aggregator' => 'DATE(orders_created_at)', 'field' => 'orders.created_at as orders_created_at'],
            'lead_campaign_id' => ['field' => 'users.leads.lead_campaign_id as lead_campaign_id', 'aggregator' => 'lead_campaign_id'],
        ];
    }
}
