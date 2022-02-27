<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $data)
 */
class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'currency',
        'transaction_date',
        'description',
    ];

    public function getTryAmountAttribute()
    {
        $currencies = Currency::whereIN('currency', ['USD/TRY', 'EUR/TRY'])->get();
        $currencies = $currencies->pluck('rate', 'currency');

        $rate = 1;
        if (in_array($this->currency, ['USD', 'EUR'])) {
            $rate = $currencies[$this->currency . '/TRY'];
        }
        if ($this->category->type == 'expense')
            $rate *= -1;

        return $this->amount * $rate;
    }

    /**
     * @return BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(TransactionCategory::class, 'category_id', 'id');
    }
}
