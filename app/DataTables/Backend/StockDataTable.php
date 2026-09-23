<?php

namespace App\DataTables\Backend;

use App\Models\Backend\ProductVariation;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->filterColumn('reference', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('product_variations.sku', 'like', "%{$keyword}%")
                      ->orWhereHas('product', function ($pq) use ($keyword) {
                          $pq->where('sku', 'like', "%{$keyword}%");
                      });
                });
            })
            ->orderColumn('reference', function ($query, $order) {
                $query->orderBy('product_variations.sku', $order);
            })
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->whereHas('product', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('sku', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->whereHas('product.category', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('brand', function ($query, $keyword) {
                $query->whereHas('product.brand', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('color', function ($query, $keyword) {
                $query->whereHas('color', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('size', function ($query, $keyword) {
                $query->whereHas('size', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('id', function (ProductVariation $v) {
                return '<span class="font-mono text-xs font-semibold text-slate-700">' . $v->id . '</span>';
            })
            ->addColumn('reference', function (ProductVariation $v) {
                $ref = $v->sku ?: optional($v->product)->sku ?: '-';
                return '<span class="font-mono text-xs font-bold text-indigo-700">' . e($ref) . '</span>';
            })
            ->addColumn('product_name', function (ProductVariation $v) {
                $prod = $v->product;
                return '<span class="font-medium text-slate-800 text-xs">' . e($prod ? $prod->name : 'Unknown Product') . '</span>';
            })
            ->addColumn('category', function (ProductVariation $v) {
                $cat = optional(optional($v->product)->category)->name ?? 'General';
                return '<span class="text-xs text-slate-700">' . e($cat) . '</span>';
            })
            ->addColumn('brand', function (ProductVariation $v) {
                $brand = optional(optional($v->product)->brand)->name ?? '-';
                return '<span class="text-xs text-slate-700">' . e($brand) . '</span>';
            })
            ->addColumn('price', function (ProductVariation $v) {
                $price = $v->effective_price;
                return '<span class="text-xs font-mono text-slate-700">$' . number_format($price, 2) . '</span>';
            })
            ->addColumn('color', function (ProductVariation $v) {
                $colorName = optional($v->color)->name ?? '-';
                return '<span class="text-xs text-slate-700">' . e($colorName) . '</span>';
            })
            ->addColumn('size', function (ProductVariation $v) {
                $sizeName = optional($v->size)->code ?? '-';
                return '<span class="text-xs text-slate-700">' . e($sizeName) . '</span>';
            })
            ->editColumn('stock', function (ProductVariation $v) {
                $qty = (int) $v->stock;
                if ($qty <= 0) {
                    return '<span class="text-xs font-bold text-rose-600 font-mono">0 </span>';
                } elseif ($qty <= 5) {
                    return '<span class="text-xs font-bold text-amber-600 font-mono">' . number_format($qty) . ' </span>';
                }
                return '<span class="text-xs font-bold text-slate-800 font-mono">' . number_format($qty) . ' </span>';
            })
            ->addColumn('cost', function (ProductVariation $v) {
                $cost = (float) (optional($v->product)->cost_price ?? 0);
                return '<span class="text-xs font-mono text-slate-700">$' . number_format($cost, 2) . '</span>';
            })
            ->addColumn('status', function (ProductVariation $v) {
                $qty = (int) $v->stock;
                if ($qty <= 0) {
                    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-rose-700 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Out of Stock
                            </span>';
                } elseif ($qty <= 5) {
                    return '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-amber-700 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Low Stock
                            </span>';
                }
                return '<span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-semibold text-emerald-700 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> In Stock
                        </span>';
            })
            ->addColumn('action', function (ProductVariation $v) {
                return '<div class="flex items-center">
                            <button type="button" onclick="viewVariationHistory(' . $v->id . ')" 
                                    class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition inline-flex items-center gap-1">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </div>';
            })
            ->rawColumns(['id', 'reference', 'product_name', 'category', 'brand', 'price', 'color', 'size', 'stock', 'cost', 'status', 'action']);
    }

    public function query(ProductVariation $model)
    {
        $query = $model->newQuery()
            ->with(['product.category', 'product.brand', 'color', 'size'])
            ->whereHas('product', function ($q) {
                $q->where('status', 1);
            })
            ->where(function ($q) {
                $q->whereHas('stockInDetails')
                  ->orWhereHas('stockMovements', function ($sm) {
                      $sm->where('type', 'stock_in');
                  })
                  ->orWhere('stock', '>', 0);
            });

        // Filter by Stock Status if requested
        $statusFilter = request()->get('stock_status');
        if ($statusFilter === 'out_of_stock') {
            $query->where('stock', '<=', 0);
        } elseif ($statusFilter === 'low_stock') {
            $query->where('stock', '>', 0)->where('stock', '<=', 5);
        } elseif ($statusFilter === 'in_stock') {
            $query->where('stock', '>', 5);
        }

        // Filter by Category
        if ($catId = request()->get('category_id')) {
            $query->whereHas('product', function ($q) use ($catId) {
                $q->where('category_id', $catId);
            });
        }

        // Filter by Brand
        if ($brandId = request()->get('brand_id')) {
            $query->whereHas('product', function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
        }

        return $query;
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('stock-inventory-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->dom('Blfrtip')
            ->buttons([
                Button::make('excel')->text('Excel')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]]),
                Button::make('csv')->text('CSV')->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]]),
                Button::make('pdf')
                    ->text('PDF')
                    ->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]])
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
                    ->exportOptions(['columns' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10]])
                    ->customize("function(win) {
                        $(win.document.body).css('font-family', 'sans-serif').css('padding', '15px');
                        $(win.document.body).find('h1').css('text-align', 'center').css('font-size', '18px').css('margin-bottom', '15px');
                        $(win.document.head).append('<style>' +
                            'table.dataTable { border-collapse: collapse !important; width: 100% !important; border: 1px solid #000000 !important; margin: 0 !important; }' +
                            'table.dataTable th, table.dataTable td { border: 1px solid #000000 !important; padding: 6px 8px !important; text-align: left !important; color: #000000 !important; font-size: 12px !important; }' +
                            'table.dataTable thead th { background-color: #f1f5f9 !important; font-weight: bold !important; border: 1px solid #000000 !important; color: #000000 !important; border-bottom: 1px solid #000000 !important; }' +
                            'table.dataTable tbody tr:first-child td { border-top: 1px solid #000000 !important; }' +
                        '</style>');
                        $(win.document.body).find('table')
                            .css('border-collapse', 'collapse')
                            .css('width', '100%')
                            .css('border', '1px solid #000000');
                        $(win.document.body).find('th, td')
                            .css('border', '1px solid #000000')
                            .css('padding', '6px 8px')
                            .css('text-align', 'left')
                            .css('color', '#000000');
                        $(win.document.body).find('th')
                            .css('background-color', '#f1f5f9')
                            .css('font-weight', 'bold')
                            .css('color', '#000000')
                            .css('border', '1px solid #000000');
                        $(win.document.body).find('tbody tr:first-child td')
                            .css('border-top', '1px solid #000000');
                        
                        var now = new Date();
                        var pad = function(n) { return n < 10 ? '0' + n : n; };
                        var dateStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                        $(win.document.body).append(
                            '<div style=\"position: fixed; bottom: 5px; right: 20px; font-size: 10px; font-weight: 600; color: #555; text-align: right; font-family: sans-serif;\">' +
                                'Exported Date: ' + dateStr +
                            '</div>'
                        );
                    }"),
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('id')->title('ID')->width(40),
            Column::computed('reference')->title('Reference')->name('reference')->orderable(true)->searchable(true),
            Column::computed('product_name')->title('Product Name')->name('product_name'),
            Column::computed('category')->title('Category')->name('category'),
            Column::computed('brand')->title('Brand')->name('brand'),
            Column::computed('price')->title('Price')->orderable(false)->searchable(false),
            Column::computed('color')->title('Color')->name('color'),
            Column::computed('size')->title('Size')->name('size'),
            Column::make('stock')->title('On Hand'),
            Column::computed('cost')->title('Cost')->orderable(false)->searchable(false),
            Column::computed('status')->title('Status')->orderable(false)->searchable(false),
            Column::computed('action')->title('Action')->exportable(false)->printable(false)->searchable(false)->orderable(false)->width(75),
        ];
    }
}
