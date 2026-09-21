<?php

namespace App\DataTables\Backend;

use App\Models\Backend\Brands;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BrandDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('logo', function (Brands $brand) {
                if ($brand->logo && file_exists(public_path($brand->logo))) {
                    return '<img src="' . asset($brand->logo) . '" alt="' . e($brand->name) . '" class="w-10 h-10 object-contain rounded border border-slate-200 p-0.5 bg-white shadow-2xs">';
                }
                return '<div class="w-10 h-10 rounded bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 text-xs font-semibold">' . strtoupper(substr($brand->name, 0, 2)) . '</div>';
            })
            ->editColumn('status', function (Brands $brand) {
                if ($brand->status) {
                    return '<span class="px-2 py-0.5 text-xs font-semibold text-emerald-700    rounded-full">Active</span>';
                }
                return '<span class="px-2 py-0.5 text-xs font-semibold text-rose-700  rounded-full">Inactive</span>';
            })
            ->addColumn('action', function (Brands $brand) {
                return '
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                onclick="openEditModal(' . htmlspecialchars(json_encode($brand), ENT_QUOTES, 'UTF-8') . ')" 
                                class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                            Edit
                        </button>
                        <form method="POST" action="' . route('backend.brands.destroy', $brand->id) . '" onsubmit="return confirm(\'Are you sure you want to delete this brand?\')" style="display:inline-block;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="px-2.5 py-1 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                                Delete
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['logo', 'status', 'action']);
    }

    public function query(Brands $model)
    {
        return $model->newQuery();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('brands-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->dom('Blfrtip')
            ->buttons([
                Button::make('excel')->text('Excel')->exportOptions(['columns' => [0, 2, 3, 4]]),
                Button::make('csv')->text('CSV')->exportOptions(['columns' => [0, 2, 3, 4]]),
                Button::make('pdf')
                    ->text('PDF')
                    ->exportOptions(['columns' => [0, 2, 3, 4]])
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
                    ->exportOptions(['columns' => [0, 2, 3, 4]])
                    ->customize("function(win) {
                        $(win.document.body).css('font-family', 'sans-serif').css('padding', '20px');
                        $(win.document.body).find('h1').css('text-align', 'center').css('font-size', '18px').css('margin-bottom', '20px');
                        $(win.document.body).find('table')
                            .css('border-collapse', 'collapse')
                            .css('width', '100%')
                            .css('font-size', '13px')
                            .css('border', '1px solid #000000');
                        $(win.document.body).find('th, td')
                            .css('border', '1px solid #000000')
                            .css('padding', '8px 12px')
                            .css('text-align', 'left')
                            .css('color', '#000000');
                        $(win.document.body).find('th')
                            .css('background-color', '#f1f5f9')
                            .css('font-weight', 'bold')
                            .css('color', '#000000')
                            .css('border', '1px solid #000000');
                        
                        var now = new Date();
                        var pad = function(n) { return n < 10 ? '0' + n : n; };
                        var dateStr = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                        
                        $(win.document.body).append(
                            '<div style=\"position: fixed; bottom: 5px; right: 20px; font-size: 11px; font-weight: 600; color: #000000; text-align: right; font-family: sans-serif;\">' +
                                'Exported Date: ' + dateStr +
                            '</div>'
                        );
                    }"),
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('id')->title('ID')->width(50),
            Column::computed('logo')->title('Logo')->width(70)->addClass('text-center'),
            Column::make('name')->title('Brand Name'),
            Column::make('slug')->title('Slug'),
            Column::make('status')->title('Status')->width(90)->addClass('text-center'),
            Column::computed('action')
                  ->title('Action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(130)
                  ->addClass('text-center'),
        ];
    }

    protected function filename()
    {
        return 'Brand_' . date('YmdHis');
    }
}
