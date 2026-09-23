<?php

namespace App\DataTables\Backend;

use App\Models\Backend\StockIn;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockInDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('supplier', function ($query, $keyword) {
                $query->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('company_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('created_by', function ($query, $keyword) {
                $query->whereHas('creator', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('id', function (StockIn $stockIn) {
                return '<span class="font-mono text-xs font-semibold text-slate-700">' . $stockIn->id . '</span>';
            })
            ->editColumn('reference_no', function (StockIn $stockIn) {
                return '<a href="' . route('backend.stock-ins.show', $stockIn->id) . '" class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-mono font-bold  text-indigo-700  transition ">
                    ' . e($stockIn->reference_no) . '
                        </a>';
            })
            ->editColumn('supplier', function (StockIn $stockIn) {
                if ($stockIn->supplier) {
                    return '<div class="text-xs font-semibold text-slate-800">' . e($stockIn->supplier->name) . '</div>' .
                           ($stockIn->supplier->company_name ? '<div class="text-[11px] text-slate-400">' . e($stockIn->supplier->company_name) . '</div>' : '');
                }
                return '<span class="text-xs text-slate-400 italic">Direct / No Supplier</span>';
            })
            ->editColumn('supplier_invoice_no', function (StockIn $stockIn) {
                return $stockIn->supplier_invoice_no 
                    ? '<span class="text-xs font-mono text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200">' . e($stockIn->supplier_invoice_no) . '</span>'
                    : '<span class="text-xs text-slate-400 italic">-</span>';
            })
            ->editColumn('received_date', function (StockIn $stockIn) {
                return '<span class="text-xs text-slate-700 font-medium">' . ($stockIn->received_date ? $stockIn->received_date->format('Y-m-d') : '-') . '</span>';
            })
            ->editColumn('total_quantity', function (StockIn $stockIn) {
                return '<span class="inline-flex items-center px-2 py-0.5  text-xs font-bold  text-slate-800 ">' . number_format($stockIn->total_quantity) . '</span>';
            })
            ->editColumn('total_cost', function (StockIn $stockIn) {
                return '<span class="text-xs font-bold text-emerald-700 font-mono">$' . number_format($stockIn->total_cost, 2) . '</span>';
            })
            ->editColumn('status', function (StockIn $stockIn) {
                if ($stockIn->status === 'received') {
                    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-emerald-700   rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full "></span> Received
                            </span>';
                } elseif ($stockIn->status === 'draft') {
                    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-amber-700  rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full "></span> Draft
                            </span>';
                }
                return '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-rose-700  rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full "></span> Cancelled
                        </span>';
            })
            ->editColumn('created_by', function (StockIn $stockIn) {
                $name = optional($stockIn->creator)->name ?? (auth()->user()?->name ?? 'Administrator');
                return '<div class="inline-flex items-center gap-1.5">
                          
                            <span class="text-xs font-medium text-slate-700">' . e($name) . '</span>
                        </div>';
            })
            ->addColumn('action', function (StockIn $stockIn) {
                $viewUrl = route('backend.stock-ins.show', $stockIn->id);
                $html = '<div class="flex items-center gap-2">
                    <a href="' . $viewUrl . '" 
                       class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition inline-flex items-center gap-1">
                        <i class="bi bi-eye"></i> View
                    </a>';

                if ($stockIn->status !== 'cancelled') {
                    $cancelUrl = route('backend.stock-ins.cancel', $stockIn->id);
                    $html .= '
                    <form method="POST" action="' . $cancelUrl . '" onsubmit="return confirm(\'Are you sure you want to cancel and reverse this Stock In? Stock quantities will be deducted back.\')" style="display:inline-block;">
                        ' . csrf_field() . '
                        <button type="submit" class="px-2 py-1 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition inline-flex items-center gap-1" title="Cancel & Reverse Stock">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    </form>';
                }

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['id', 'reference_no', 'supplier', 'supplier_invoice_no', 'received_date', 'total_quantity', 'total_cost', 'status', 'created_by', 'action']);
    }

    public function query(StockIn $model)
    {
        return $model->newQuery()
            ->with(['supplier', 'creator'])
            ->select('stock_ins.*');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('stock-ins-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->dom('Blfrtip')
            ->buttons([
                Button::make('excel')->text('Excel')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7]]),
                Button::make('csv')->text('CSV')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7]]),
                Button::make('pdf')
                    ->text('PDF')
                    ->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7]])
                    ->customize("function(doc) {
                        var now = new Date();
                        var pad = function(n) { return n < 10 ? '0' + n : n; };
                        var dateStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                        doc.footer = function(page, pages) {
                            return {
                                columns: [
                                    { text: 'Page ' + page.toString() + ' of ' + pages.toString(), alignment: 'left', margin: [20, 0] },
                                    { text: 'Exported Date: ' + dateStr, alignment: 'right', margin: [0, 0, 20, 0] }
                                ],
                                fontSize: 9,
                                color: '#000000'
                            };
                        };
                    }"),
                Button::make('print')
                    ->text('Print')
                    ->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7]])
                    ->customize("function(win) {
                        var now = new Date();
                        var pad = function(n) { return n < 10 ? '0' + n : n; };
                        var dateStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                        $(win.document.body).prepend('<div style=\"text-align:center; margin-bottom: 15px; font-size: 16px; font-weight: bold;\">Stock In Records</div><div style=\"font-size: 11px; margin-bottom: 15px; text-align: right; color: #555;\">Exported on: ' + dateStr + '</div>');
                        $(win.document.body).find('table').addClass('compact').css('font-size', '11px');
                    }")
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('id')
                ->title('ID')
                ->width(50)
                ->addClass('text-center'),
            Column::make('reference_no')->title('Reference No'),
            Column::make('supplier')->title('Supplier')->orderable(false),
            Column::make('supplier_invoice_no')->title('Supplier Inv No'),
            Column::make('received_date')->title('Date'),
            Column::make('total_quantity')->title('Total Qty')->addClass('text-center'),
            Column::make('total_cost')->title('Total Cost')->addClass('text-right'),
            Column::make('status')->title('Status')->addClass('text-center'),
            Column::make('created_by')->title('Created By')->orderable(false),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->orderable(false)
                ->width(140)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Stock_Ins_' . date('YmdHis');
    }
}
