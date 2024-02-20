<?php

declare(strict_types=1);

use Workbench\App\Models\Customer;
use Workbench\App\Models\Lead;
use Workbench\App\Models\LeadCampaign;
use Workbench\App\Models\Order;
use Workbench\App\Reports\OrderReport;
use Workbench\App\Reports\UserReport;

test('count summary', function () {
    Order::create(['no' => '#001', 'value' => 2000, 'created_at' => '2023-11-01']);
    Order::create(['no' => '#002', 'value' => 1000, 'created_at' => '2023-12-01']);
    Order::create(['no' => '#003', 'value' => 1000, 'created_at' => '2024-01-01']);

    $report = OrderReport::query()
        ->grouping(['year'])
        ->summary(['total_orders'])
        ->enhance(function ($q) {
            $q->whereYear('orders.created_at', 2023);
        })
        ->prepare();

    expect($report->toRawSql())->toBe('with `order_reports` as (select `orders`.`created_at` as `orders_created_at`, `orders`.`id` as `total_orders` from `orders` where year(`orders`.`created_at`) = 2023) select YEAR(orders_created_at) as year, COUNT(total_orders) as total_orders from `order_reports` group by YEAR(orders_created_at)');

    expect($report->get()->toArray())->toBe([
        ['year' => 2023, 'total_orders' => 2],
    ]);
});

test('count leads', function () {
    $leadCampaign = LeadCampaign::create(['name' => 'Campaign 1']);
    $user1 = Customer::create(['email' => 'a@savicki.pl', 'created_at' => '2023-11-01']);
    $user2 = Customer::create(['email' => 'b@savicki.pl', 'created_at' => '2023-11-01']);
    Lead::create(['email' => $user1->email, 'customer_id' => $user1->id, 'lead_campaign_id' => $leadCampaign->id, 'created_at' => '2023-11-01']);
    Lead::create(['email' => $user2->email, 'customer_id' => $user2->id, 'lead_campaign_id' => $leadCampaign->id, 'created_at' => '2023-11-01']);

    $report = UserReport::query()
        ->grouping(['year', 'lead_campaign_id'])
        ->summary(['total_users'])
        ->enhance(function ($q) {
            $q->whereYear('customers.created_at', 2023)->whereRelation('leads', 'leads.customer_id', 'customers.id');
        })
        ->with(['leads' => fn ($q) => $q->select('id', 'customer_id', 'lead_campaign_id')])
        ->prepare();

    expect($report->toRawSql())->toBe("with `user_reports` as (select `customers`.`created_at` as `customers_created_at`, `leads`.`lead_campaign_id` as `lead_campaign_id`, `customers`.`id` as `total_users` from `customers` where year(`customers`.`created_at`) = 2023 and exists (select * from `leads` where `customers`.`id` = `leads`.`customer_id` and `leads`.`customer_id` = 'customers.id')) select YEAR(customers_created_at) as year, lead_campaign_id as lead_campaign_id, COUNT(total_users) as total_users from `user_reports` group by YEAR(customers_created_at), lead_campaign_id");

    expect($report->get()->toArray())->toBe([
        ['year' => 2023, 'total_users' => 2],
    ]);
});

test('sum summary', function () {
    Order::create(['no' => '#001', 'value' => 2000, 'created_at' => '2023-11-01']);
    Order::create(['no' => '#002', 'value' => 1000, 'created_at' => '2023-12-01']);
    Order::create(['no' => '#003', 'value' => 1000, 'created_at' => '2024-01-01']);

    $report = OrderReport::query()
        ->grouping(['year'])
        ->summary(['total_value'])
        ->enhance(function ($q) {
            $q->whereYear('orders.created_at', 2023);
        })
        ->prepare();

    expect($report->toRawSql())->toBe('with `order_reports` as (select `orders`.`created_at` as `orders_created_at`, `orders`.`value` as `total_value` from `orders` where year(`orders`.`created_at`) = 2023) select YEAR(orders_created_at) as year, SUM(total_value) as total_value from `order_reports` group by YEAR(orders_created_at)');

    expect($report->get()->toArray())->toBe([
        ['year' => 2023, 'total_value' => '3000.00'],
    ]);
});

test('avg summary', function () {
    Order::create(['no' => '#001', 'value' => 2000, 'created_at' => '2023-11-01']);
    Order::create(['no' => '#002', 'value' => 1000, 'created_at' => '2023-12-01']);
    Order::create(['no' => '#003', 'value' => 1000, 'created_at' => '2024-01-01']);

    $report = OrderReport::query()
        ->grouping(['year'])
        ->summary(['average_value'])
        ->enhance(function ($q) {
            $q->whereYear('orders.created_at', 2023);
        })
        ->prepare();

    expect($report->toRawSql())->toBe('with `order_reports` as (select `orders`.`created_at` as `orders_created_at`, `orders`.`value` as `average_value` from `orders` where year(`orders`.`created_at`) = 2023) select YEAR(orders_created_at) as year, AVG(average_value) as average_value from `order_reports` group by YEAR(orders_created_at)');

    expect($report->get()->toArray())->toBe([
        ['year' => 2023, 'average_value' => '1500.00'],
    ]);
});

test('with relation', function () {
    $channel1 = \Workbench\App\Models\Channel::create(['name' => 'Channel 1']);
    $channel2 = \Workbench\App\Models\Channel::create(['name' => 'Channel 2']);

    Order::create(['no' => '#001', 'value' => 2000, 'created_at' => '2023-11-01', 'channel_id' => $channel1->id]);
    Order::create(['no' => '#002', 'value' => 3000, 'created_at' => '2023-12-01', 'channel_id' => $channel2->id]);
    Order::create(['no' => '#003', 'value' => 1000, 'created_at' => '2023-01-01', 'channel_id' => $channel1->id]);
    Order::create(['no' => '#004', 'value' => 1000, 'created_at' => '2024-01-01', 'channel_id' => $channel1->id]);

    $report = OrderReport::query()
        ->grouping(['year', 'channel_id'])
        ->summary(['average_value'])
        ->enhance(function ($q) use ($channel1) {
            $q->whereYear('orders.created_at', 2023)->whereRelation('channel', 'id', $channel1->id);
        })
        ->prepare()
        ->with(['channel' => fn ($q) => $q->select('id', 'name')]);

    expect($report->toRawSql())->toBe('with `order_reports` as (select `orders`.`created_at` as `orders_created_at`, `orders`.`channel_id` as `orders_channel_id`, `orders`.`value` as `average_value` from `orders` where year(`orders`.`created_at`) = 2023 and exists (select * from `channels` where `orders`.`channel_id` = `channels`.`id` and `id` = 1)) select YEAR(orders_created_at) as year, orders_channel_id as channel_id, AVG(average_value) as average_value from `order_reports` group by YEAR(orders_created_at), orders_channel_id');

    expect($report->get()->toArray())->toBe([
        ['year' => 2023,
            'channel_id' => 1,
            'average_value' => '1500.00',
            'channel' => [
                'id' => 1,
                'name' => 'Channel 1',
            ]],
    ],
    );
});
