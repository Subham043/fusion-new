<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) FusionInvoice, LLC <jessedterry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Quotes\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Groups\Models\Group;
use FI\Modules\Quotes\Models\Quote;
use FI\Modules\Quotes\Support\QuoteToInvoice;
use FI\Modules\Quotes\Requests\QuoteToInvoiceRequest;
use FI\Support\DateFormatter;

use Addons\Scheduler\Requests\ReportRequest;
use Addons\Workorders\Models\Employee;
use Addons\Workorders\Models\Resource;
use Addons\Scheduler\Models\Schedule;
use Addons\Scheduler\Models\ScheduleReminder;
use Addons\Scheduler\Models\ScheduleOccurrence;
use Addons\Scheduler\Models\ScheduleResource;
use Addons\Scheduler\Models\Category;
use Addons\Scheduler\Models\Setting;
use Recurr;
use Recurr\Transformer;
use Recurr\Exception;
use Carbon\Carbon;
use DB;
use Auth;
use Session;
use Response;
use Illuminate\Http\Request;

//for FusionInvoice
use FI\Modules\CompanyProfiles\Models\CompanyProfile;
use Addons\Scheduler\Requests\EventRequest;

class QuoteToInvoiceController extends Controller
{
    private $quoteToInvoice;

    public function __construct(QuoteToInvoice $quoteToInvoice)
    {
        $this->quoteToInvoice = $quoteToInvoice;
    }

    public function create()
    {
        return view('quotes._modal_quote_to_invoice')
            ->with('quote_id', request('quote_id'))
            ->with('client_id', request('client_id'))
            ->with('groups', Group::getList())
            ->with('user_id', auth()->user()->id)
            ->with('invoice_date', DateFormatter::format());
    }

    public function store(QuoteToInvoiceRequest $request)
    {
        
        $quote = Quote::find($request->input('quote_id'));

        $invoice = $this->quoteToInvoice->convert(
            $quote,
            DateFormatter::unformat($request->input('invoice_date')),
            DateFormatter::incrementDateByDays(DateFormatter::unformat($request->input('invoice_date')), config('fi.invoicesDueAfter')),
            $request->input('group_id')
        );
        
        
        
        return response()->json(['redirectTo' => route('invoices.edit', ['invoice' => $invoice->id])], 200);
    }
}