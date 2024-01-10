# BILOQUENT

BI for Eloquent models.


## UDF Infusion

Some of aggregator needs UDF Infusion extensions for MySQL:

- kurtosis
- median
- percentile
- skewness

## Sample raport

```php
declare(strict_types=1);

namespace Modules\Order\Reports;

use Webard\Biloquent\Aggregators\Avg;
use Webard\Biloquent\Aggregators\Count;
use Webard\Biloquent\Report;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Order;

/**
 * @property float $total
 * @property float $average
 * @property int $currency_id
 */
class OrderReport extends Report
{
    public static string $model = Order::class;
   
    // Defaults if not defined
    public array $grouping = ['year'];
    public array $columns = ['total_orders'];

    // Define needed relations, they are not taken from source model (yet)
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // Define aggregators
    public function aggregators(): array
    {
        return [
            'total_orders' => Count::field('total_orders', 'id'),
            'average_amount' => Avg::field('average_amount', 'total'),
        ];
    }

    public function groups(): array
    {
        return [
            'day' => ['aggregator' => 'DAY(created_at)'],
            'month' => ['aggregator' => 'MONTH(created_at)'],
            'year' => ['aggregator' => 'YEAR(created_at)'],
            'date' => ['aggregator' => 'DATE(created_at)'],
            'customer_id' => ['field' => 'customer_id', 'aggregator' => 'customer_id'],
        ];
    }
}
```

Now you can use report:

```php
$report = (new $class())->grouping(['month','year'])->aggregate(['total_orders', 'average_amount'])->en;
```