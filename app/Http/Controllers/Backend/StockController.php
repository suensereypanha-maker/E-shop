<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\DataTables\Backend\StockDataTable;
use App\Models\Backend\Product;
use App\Models\Backend\ProductVariation;
use App\Models\Backend\Category;
use App\Models\Backend\Brands;
use App\Models\Backend\Supplier;
use App\Models\Backend\StockIn;
use App\Models\Backend\StockInDetail;
use App\Models\Backend\StockOut;
use App\Models\Backend\StockAdjustment;
use App\Models\Backend\StockAdjustmentDetail;
use App\Models\Backend\StockMovement;
use Illuminate\Http\Request;

class StockController extends Controller
{
    /**
     * Display unified Stock Overview & Management Hub.
     */
    public function index(StockDataTable $dataTable, Request $request)
    {
        // 1. Calculate overall metrics (only for variations with stock-in history or active stock)
        $variations = ProductVariation::with('product')
            ->whereHas('product', function ($q) {
                $q->where('status', 1);
            })
            ->where(function ($q) {
                $q->whereHas('stockInDetails')
                  ->orWhereHas('stockMovements', function ($sm) {
                      $sm->where('type', 'stock_in');
                  })
                  ->orWhere('stock', '>', 0);
            })
            ->get();

        $totalUnits = $variations->sum('stock');
        $totalCostValuation = $variations->sum(function ($v) {
            return (int) $v->stock * (float) (optional($v->product)->cost_price ?? 0);
        });

        $lowStockCount = $variations->filter(fn($v) => $v->stock > 0 && $v->stock <= 5)->count();
        $outOfStockCount = $variations->filter(fn($v) => $v->stock <= 0)->count();
        $inStockCount = $variations->filter(fn($v) => $v->stock > 5)->count();

        $stats = [
            'total_products'       => Product::where('status', 1)->count(),
            'total_variations'     => $variations->count(),
            'total_units'          => $totalUnits,
            'total_cost_valuation' => $totalCostValuation,
            'in_stock_count'       => $inStockCount,
            'low_stock_count'      => $lowStockCount,
            'out_of_stock_count'   => $outOfStockCount,
            'total_stock_in_qty'   => StockIn::where('status', 'received')->sum('total_quantity'),
            'total_stock_out_qty'  => StockOut::where('status', 'dispatched')->sum('total_quantity'),
            'total_adjustments'    => StockAdjustment::count(),
        ];

        // 2. Catalog for filter dropdowns and quick modal actions
        $categories = Category::where('status', 1)->orderBy('name', 'asc')->get();
        $brands = Brands::where('status', 1)->orderBy('name', 'asc')->get();
        $suppliers = Supplier::where('status', 1)->orderBy('name', 'asc')->get();

        $products = Product::with(['variations.color', 'variations.size', 'category', 'brand'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get();

        // 3. Tab previews
        $recentStockIns = StockIn::with(['supplier', 'creator', 'items'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $recentStockOuts = StockOut::with(['creator', 'items'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $recentAdjustments = StockAdjustment::with(['creator', 'items'])
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get();

        $recentMovements = StockMovement::with(['product', 'variation.color', 'variation.size', 'creator'])
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();

        // 4. Auto generated reference numbers for quick modals
        $stockInRef = StockIn::generateReferenceNo();
        $stockOutRef = StockOut::generateReferenceNo();
        $adjustmentRef = StockAdjustment::generateReferenceNo();

        return $dataTable->render('backend.stock.index', compact(
            'stats',
            'categories',
            'brands',
            'suppliers',
            'products',
            'recentStockIns',
            'recentStockOuts',
            'recentAdjustments',
            'recentMovements',
            'stockInRef',
            'stockOutRef',
            'adjustmentRef'
        ));
    }

    /**
     * AJAX endpoint: return stock movements audit ledger with optional filters.
     */
    public function movements(Request $request)
    {
        $query = StockMovement::with(['product', 'variation.color', 'variation.size', 'creator'])
            ->orderBy('id', 'desc');

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($variationId = $request->get('variation_id')) {
            $query->where('variation_id', $variationId);
        }

        $movements = $query->paginate(25);

        return response()->json([
            'status' => 'success',
            'data'   => $movements,
        ]);
    }

    /**
     * AJAX endpoint: return complete movement timeline for a specific variation.
     */
    public function history($variationId)
    {
        $variation = ProductVariation::with(['product.category', 'product.brand', 'color', 'size'])->findOrFail($variationId);

        $movements = StockMovement::with(['creator', 'reference'])
            ->where('variation_id', $variationId)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($m) {
                return [
                    'id'           => $m->id,
                    'type'         => $m->type,
                    'type_badge'   => match($m->type) {
                        'stock_in'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'stock_out'   => 'bg-rose-50 text-rose-700 border-rose-200',
                        'adjustment'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                        'sale'        => 'bg-blue-50 text-blue-700 border-blue-200',
                        'return'      => 'bg-amber-50 text-amber-700 border-amber-200',
                        default       => 'bg-slate-50 text-slate-700 border-slate-200',
                    },
                    'quantity'     => $m->quantity,
                    'stock_before' => $m->stock_before,
                    'stock_after'  => $m->stock_after,
                    'unit_cost'    => $m->unit_cost ? number_format($m->unit_cost, 2) : '0.00',
                    'reference_no' => $m->reference_no,
                    'reference_url'=> $m->reference_url,
                    'reason_label' => $m->reason_label,
                    'note'         => $m->note,
                    'created_by'   => optional($m->creator)->name ?? 'System',
                    'date'         => $m->created_at ? $m->created_at->format('d M Y, H:i') : '-',
                ];
            });

        return response()->json([
            'status'    => 'success',
            'product'   => [
                'name'     => $variation->product->name ?? 'Product',
                'sku'      => $variation->sku ?: ($variation->product->sku ?? '-'),
                'category' => optional($variation->product->category)->name ?? '-',
                'brand'    => optional($variation->product->brand)->name ?? '-',
                'color'    => optional($variation->color)->name ?? 'Standard',
                'size'     => optional($variation->size)->name ?? 'Standard',
                'stock'    => $variation->stock,
                'cost'     => number_format((float)($variation->product->cost_price ?? 0), 2),
            ],
            'movements' => $movements,
        ]);
    }
}
