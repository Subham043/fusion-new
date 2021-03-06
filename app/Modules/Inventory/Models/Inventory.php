<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) FusionInvoice, LLC <jessedterry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Inventory\Models;

use FI\Support\CurrencyFormatter;
use FI\Support\NumberFormatter;
use FI\Traits\Sortable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
    use Sortable;

    /**
     * Guarded properties
     * @var array
     */
     
    protected $table = 'item_lookups';
    
    protected $guarded = ['id'];

    protected $sortable = ['name', 'description', 'price'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function taxRate()
    {
        return $this->belongsTo('FI\Modules\TaxRates\Models\TaxRate');
    }

    public function taxRate2()
    {
        return $this->belongsTo('FI\Modules\TaxRates\Models\TaxRate', 'tax_rate_2_id');
    }
    
    public function quoteItem()
    {
        return $this->hasMany('FI\Modules\Quotes\Models\QuoteItem');
    }
    
    public function invoiceItem()
    {
        return $this->hasMany('FI\Modules\Invoices\Models\InvoiceItem');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedPriceAttribute()
    {
        return CurrencyFormatter::format($this->attributes['price']);
    }

    public function getFormattedNumericPriceAttribute()
    {
        return NumberFormatter::format($this->attributes['price']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeKeywords($query, $keywords)
    {
        if ($keywords)
        {
            $keywords = explode(' ', $keywords);

            foreach ($keywords as $keyword)
            {
                if ($keyword)
                {
                    $keyword = strtolower($keyword);

                    $query->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(description),price)"), 'LIKE', "%$keyword%");
                }
            }
        }

        return $query;
    }
    
    public static function getList()
    {
        return self::orderBy('name')->pluck('name', 'name')->all();
    }
}