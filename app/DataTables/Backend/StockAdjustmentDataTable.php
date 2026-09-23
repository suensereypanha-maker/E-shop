<?php

namespace App\DataTables\Backend;

use App\Models\Backend\StockAdjustment;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockAdjustmentDataTable extends DataTable
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
            ->editColumn('id', function (StockAdjustment $adj) {
                return '<span class="font-mono text-xs font-semibold text-slate-700">' . $adj->id . '</span>';
            })
            ->editColumn('reference_no', function (StockAdjustment $adj) {
                return '<a href="' . route('backend.stock-adjustments.show', $adj->id) . '" class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-mono font-bold text-indigo-700  transition  ">
                            ' . e($adj->reference_no) . '
                        </a>';
            })
            ->editColumn('date', function (StockAdjustment $adj) {
                return '<span class="text-xs text-slate-700 font-medium">' . ($adj->date ? $adj->date->format('Y-m-d') : '-') . '</span>';
            })
            ->editColumn('reason', function (StockAdjustment $adj) {
                return match ($adj->reason) {
                    'damage' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-amber-700">
                                    <i class="bi bi-exclamation-triangle"></i> Damage
                                </span>',
                    'lost_theft' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-rose-700">
                                        <i class="bi bi-shield-x"></i> Lost / Theft
                                    </span>',
                    'count_mismatch' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-700">
                                            <i class="bi bi-clipboard-check"></i> Count Mismatch
                                        </span>',
                    'expired' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-orange-700">
                                    <i class="bi bi-calendar-x"></i> Expired
                                </span>',
                    'found_stock' => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                                        <i class="bi bi-plus-circle"></i> Found Stock
                                    </span>',
                    default => '<span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-700">
                                    ' . e(ucfirst(str_replace('_', ' ', $adj->reason ?: 'Other'))) . '
                                </span>',
                };
            })
            ->editColumn('total_items', function (StockAdjustment $adj) {
                return '<span class="text-xs font-semibold text-slate-700">' . $adj->total_items . ' items</span>';
            })
            ->editColumn('total_qty_adjusted', function (StockAdjustment $adj) {
                $qty = $adj->total_qty_adjusted;
                if ($qty > 0) {
                    return '<span class="text-xs font-mono font-bold text-emerald-700">+' . number_format($qty) . ' pcs</span>';
                } elseif ($qty < 0) {
                    return '<span class="text-xs font-mono font-bold text-rose-700">' . number_format($qty) . ' pcs</span>';
                }
                return '<span class="text-xs font-mono text-slate-500">0 pcs</span>';
            })
            ->editColumn('total_cost_impact', function (StockAdjustment $adj) {
                $impact = (float) $adj->total_cost_impact;
                if ($impact < 0) {
                    return '<span class="text-xs font-bold font-mono text-rose-700">-$' . number_format(abs($impact), 2) . '</span>';
                } elseif ($impact > 0) {
                    return '<span class="text-xs font-bold font-mono text-emerald-700">+$' . number_format($impact, 2) . '</span>';
                }
                return '<span class="text-xs font-bold font-mono text-slate-600">$0.00</span>';
            })
            ->editColumn('created_by', function (StockAdjustment $adj) {
                $name = optional($adj->creator)->name ?? (auth()->user()?->name ?? 'Administrator');
                return '<div class="inline-flex items-center gap-1.5">
                            <span class="text-xs font-medium text-slate-700">' . e($name) . '</span>
                        </div>';
            })
            ->addColumn('action', function (StockAdjustment $adj) {
                $viewUrl = route('backend.stock-adjustments.show', $adj->id);
                return '<div class="flex items-center gap-2">
                            <a href="' . $viewUrl . '" class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition inline-flex items-center gap-1">
                                <i class="bi bi-eye"></i> View
                            </a>
                        </div>';
            })
            ->rawColumns(['id', 'reference_no', 'date', 'reason', 'total_items', 'total_qty_adjusted', 'total_cost_impact', 'created_by', 'action']);
    }

    public function query(StockAdjustment $model)
    {
        return $model->newQuery()
            ->with(['creator', 'items.product', 'items.variation'])
            ->select('stock_adjustments.*');
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('stock-adjustments-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'desc')
            ->dom('Blfrtip')
            ->buttons([
                Button::make('excel')->text('Excel')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7]]),
                Button::make('csv')->text('CSV')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7]]),
                Button::make('pdf')->text('PDF')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7]]),
                Button::make('print')->text('Print')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7]]),
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('id')->title('ID')->width(50)->addClass('text-center'),
            Column::make('reference_no')->title('Reference No'),
            Column::make('date')->title('Date'),
            Column::make('reason')->title('Reason')->addClass('text-center'),
            Column::make('total_items')->title('Items')->addClass('text-center'),
            Column::make('total_qty_adjusted')->title('Net Qty Impact')->addClass('text-center'),
            Column::make('total_cost_impact')->title('Cost Impact ($)')->addClass('text-right'),
            Column::make('created_by')->title('Created By')->orderable(false),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->orderable(false)
                ->width(90)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'Stock_Adjustments_' . date('YmdHis');
    }
}
