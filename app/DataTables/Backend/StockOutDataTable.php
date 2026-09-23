<?php

namespace App\DataTables\Backend;

use App\Models\Backend\StockOut;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockOutDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('created_by', function ($query, $keyword) {
                $query->whereHas('creator', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('id', function (StockOut $stockOut) {
                return '<span class="font-mono text-xs font-semibold text-slate-700">' . $stockOut->id . '</span>';
            })
            ->editColumn('reference_no', function (StockOut $stockOut) {
                return '<a href="' . route('backend.stock-outs.show', $stockOut->id) . '" class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-mono font-bold text-indigo-700 transition">
                            ' . e($stockOut->reference_no) . '
                        </a>';
            })
            ->editColumn('date', function (StockOut $stockOut) {
                return '<span class="text-xs text-slate-700 font-medium">' . ($stockOut->date ? $stockOut->date->format('Y-m-d') : '-') . '</span>';
            })
            ->editColumn('reason', function (StockOut $stockOut) {
                return match ($stockOut->reason) {
                    'sale_dispatch' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-700">
                                            <i class="bi bi-cart-check"></i> Sale Dispatch
                                        </span>',
                    'damage_scrap' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-700">
                                            <i class="bi bi-exclamation-octagon"></i> Damage / Scrap
                                       </span>',
                    'internal_use' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-sky-700">
                                            <i class="bi bi-building"></i> Store / Internal Use
                                       </span>',
                    'sample' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700">
                                    <i class="bi bi-gift"></i> Sample / Promo
                                </span>',
                    'return_supplier' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-purple-700">
                                            <i class="bi bi-arrow-return-left"></i> Return to Supplier
                                          </span>',
                    'expired' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-orange-700">
                                    <i class="bi bi-calendar-x"></i> Expired Items
                                  </span>',
                    'loss_theft' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-red-800">
                                        <i class="bi bi-shield-x"></i> Loss / Theft
                                     </span>',
                    default => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700">
                                    ' . e(ucfirst(str_replace('_', ' ', $stockOut->reason ?: 'Other'))) . '
                                </span>',
                };
            })
            ->editColumn('recipient_name', function (StockOut $stockOut) {
                return '<span class="text-xs text-slate-700">' . e($stockOut->recipient_name ?: '-') . '</span>';
            })
            ->editColumn('items_count', function (StockOut $stockOut) {
                return '<span class="text-xs font-semibold text-slate-700">' . $stockOut->items->count() . ' items</span>';
            })
            ->editColumn('total_quantity', function (StockOut $stockOut) {
                return '<span class="inline-flex items-center px-2 py-0.5 text-xs font-bold text-slate-800">' . number_format($stockOut->total_quantity) . '</span>';
            })
            ->editColumn('total_cost', function (StockOut $stockOut) {
                return '<span class="text-xs font-bold text-emerald-700 font-mono">$' . number_format($stockOut->total_cost, 2) . '</span>';
            })
            ->editColumn('status', function (StockOut $stockOut) {
                if ($stockOut->status === 'dispatched') {
                    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-emerald-700 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full"></span> Dispatched
                            </span>';
                }
                return '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-rose-700 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full"></span> Cancelled
                        </span>';
            })
            ->editColumn('created_by', function (StockOut $stockOut) {
                $name = optional($stockOut->creator)->name ?? (auth()->user()?->name ?? 'Administrator');
                return '<span class="text-xs font-medium text-slate-700">' . e($name) . '</span>';
            })
            ->addColumn('action', function (StockOut $stockOut) {
                $viewUrl = route('backend.stock-outs.show', $stockOut->id);
                $html = '<div class="flex items-center gap-2">
                    <a href="' . $viewUrl . '" 
                       class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition inline-flex items-center gap-1">
                        <i class="bi bi-eye"></i> View
                    </a>';

                if ($stockOut->status !== 'cancelled') {
                    $cancelUrl = route('backend.stock-outs.cancel', $stockOut->id);
                    $html .= '
                    <form method="POST" action="' . $cancelUrl . '" onsubmit="return confirm(\'Are you sure you want to cancel and reverse this Stock Out? Stock quantities will be restored back to inventory.\')" style="display:inline-block;">
                        ' . csrf_field() . '
                        <button type="submit" class="px-2 py-1 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition inline-flex items-center gap-1" title="Cancel & Restore Stock">
                            <i class="bi bi-x-circle"></i> Cancel
                        </button>
                    </form>';
                }

                $html .= '</div>';
                return $html;
            })
            ->rawColumns(['id', 'reference_no', 'date', 'reason', 'recipient_name', 'items_count', 'total_quantity', 'total_cost', 'status', 'created_by', 'action']);
    }

    public function query(StockOut $model)
    {
        return $model->newQuery()
            ->with(['creator', 'items'])
            ->select('stock_outs.*');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('stock-outs-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->dom('Blfrtip')
            ->buttons([
                Button::make('excel')->text('Excel')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7, 8]]),
                Button::make('csv')->text('CSV')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7, 8]]),
                Button::make('pdf')
                    ->text('PDF')
                    ->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7, 8]])
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
                    ->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7, 8]])
                    ->customize("function(win) {
                        var now = new Date();
                        var pad = function(n) { return n < 10 ? '0' + n : n; };
                        var dateStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                        $(win.document.body).prepend('<div style=\"text-align:center; margin-bottom: 15px; font-size: 16px; font-weight: bold;\">Stock Out Records</div><div style=\"font-size: 11px; margin-bottom: 15px; text-align: right; color: #555;\">Exported on: ' + dateStr + '</div>');
                        $(win.document.body).find('table').addClass('compact').css('font-size', '11px');
                    }")
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('id')->title('ID')->width(50)->addClass('text-center'),
            Column::make('reference_no')->title('Reference No'),
            Column::make('date')->title('Date'),
            Column::make('reason')->title('Reason'),
            Column::make('recipient_name')->title('Recipient / To'),
            Column::make('total_quantity')->title('Dispatched Qty'),
            Column::make('total_cost')->title('Total Cost'),
            Column::make('status')->title('Status'),
            Column::make('created_by')->title('Created By')->orderable(false),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->searchable(false)->orderable(false)->width(120),
        ];
    }
}
