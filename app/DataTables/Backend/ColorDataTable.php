<?php

namespace App\DataTables\Backend;

use App\Models\Backend\Color;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ColorDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('code', function (Color $color) {
                if (!$color->code) {
                    return '<span class="text-slate-400 italic">None</span>';
                }
                return '<div class="flex items-center gap-2">
                    <span class="w-4 h-4 rounded-full border border-slate-300 shadow-xs inline-block" style="background-color: ' . e($color->code) . ';"></span>
                    <span class="font-mono text-xs">' . e($color->code) . '</span>
                </div>';
            })
            ->editColumn('status', function (Color $color) {
                if ($color->status) {
                    return '<span class="px-2 py-0.5 text-xs font-semibold text-emerald-700  rounded-full ">Active</span>';
                }
                return '<span class="px-2 py-0.5 text-xs font-semibold text-red-700 rounded-full ">Inactive</span>';
            })
            ->addColumn('action', function (Color $color) {
                return '
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                onclick="openEditModal(' . htmlspecialchars(json_encode($color), ENT_QUOTES, 'UTF-8') . ')" 
                                class="px-2.5 py-1 text-xs font-medium text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition">
                            Edit
                        </button>
                        <form method="POST" action="' . route('backend.colors.destroy', $color->id) . '" onsubmit="return confirm(\'Are you sure you want to delete this color?\')" style="display:inline-block;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="px-2.5 py-1 text-xs font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg transition">
                                Delete
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['code', 'status', 'action']);
    }

    public function query(Color $model)
    {
        return $model->newQuery();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('colors-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0, 'asc')
            ->dom('Blfrtip')
            ->buttons([
                Button::make('excel')
                    ->text('Excel')
                    ->exportOptions(['columns' => [0, 1, 2, 3]]),
                Button::make('csv')
                    ->text('CSV')
                    ->exportOptions(['columns' => [0, 1, 2, 3]]),
                Button::make('pdf')
                    ->text('PDF')
                    ->exportOptions(['columns' => [0, 1, 2, 3]])
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
                    ->exportOptions(['columns' => [0, 1, 2, 3]])
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
            Column::make('name')->title('Color Name'),
            Column::make('code')->title('Color / Code'),
            Column::make('status')->title('Status')->width(100),
            Column::computed('action')
                  ->title('Action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(140)
                  ->addClass('text-center'),
        ];
    }

    protected function filename()
    {
        return 'Color_' . date('YmdHis');
    }
}
