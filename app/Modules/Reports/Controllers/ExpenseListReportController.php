<?php

/**
 * This file is part of FusionInvoice.
 *
 * (c) FusionInvoice, LLC <jessedterry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FI\Modules\Reports\Controllers;

use FI\Http\Controllers\Controller;
use FI\Modules\Expenses\Models\ExpenseCategory;
use FI\Modules\Expenses\Models\ExpenseVendor;
use FI\Modules\Reports\Reports\ExpenseListReport;
use FI\Modules\Reports\Requests\DateRangeRequest;
use FI\Support\PDF\PDFFactory;

class ExpenseListReportController extends Controller
{
    private $report;

    public function __construct(ExpenseListReport $report)
    {
        $this->report = $report;
    }

    public function index()
    {
        return view('reports.options.expense_list')
            ->with('categories', ['' => trans('fi.all_categories')] + ExpenseCategory::getList())
            ->with('vendors', ['' => trans('fi.all_vendors')] + ExpenseVendor::getList());
    }

    public function validateOptions(DateRangeRequest $request)
    {

    }

    public function html()
    {
        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('category_id'),
            request('vendor_id')
        );

        return view('reports.output.expense_list')
            ->with('results', $results);
    }

    public function pdf()
    {
        $pdf = PDFFactory::create();
        $pdf->setPaperOrientation('landscape');

        $results = $this->report->getResults(
            request('from_date'),
            request('to_date'),
            request('company_profile_id'),
            request('category_id'),
            request('vendor_id')
        );

        $html = view('reports.output.expense_list')
            ->with('results', $results)->render();

        $pdf->download($html, trans('fi.expense_list') . '.pdf');
    }
}