<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\DataTables\Backend\StockAdjustmentDataTable;
use App\Models\Backend\StockAdjustment;
use App\Models\Backend\StockAdjustmentDetail;
use App\Models\Backend\StockMovement;
use App\Models\Backend\Product;
use App\Models\Backend\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    /**
     * Display a listing of stock adjustment records.
     */
    public function index(StockAdjustmentDataTable $dataTable)
    {
        $stats = [
            'total_adjustments' => StockAdjustment::count(),
            'total_added_qty'   => StockAdjustmentDetail::where('type', 'addition')->sum('quantity'),
            'total_deducted_qty'=> StockAdjustmentDetail::where('type', 'subtraction')->sum('quantity'),
            'net_cost_impact'   => StockAdjustment::sum('total_cost_impact'),
        ];

        $products = Product::with(['variations.color', 'variations.size', 'category', 'brand'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get();

        $referenceNo = StockAdjustment::generateReferenceNo();

        return $dataTable->render('backend.stock-adjustment.index', compact('stats', 'products', 'referenceNo'));
    }

    /**
     * Store newly created stock adjustment in database with immutable ledger movement.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reference_no'        => 'nullable|string|max:50',
            'date'                => 'required|date',
            'reason'              => 'required|string|in:damage,lost_theft,count_mismatch,expired,found_stock,other',
            'note'                => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.variation_id' => 'required|exists:product_variations,id',
            'items.*.type'         => 'required|in:addition,subtraction',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.reason'       => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $totalItems = count($request->items);
            $netQtyAdjusted = 0;
            $totalCostImpact = 0;

            // 1. Create Stock Adjustment Header
            $adjustment = StockAdjustment::create([
                'reference_no'        => $request->reference_no ?: StockAdjustment::generateReferenceNo(),
                'date'                => $request->date,
                'reason'              => $request->reason,
                'total_items'         => $totalItems,
                'total_qty_adjusted'  => 0, // will update below
                'total_cost_impact'   => 0, // will update below
                'note'                => $request->note,
                'created_by'          => auth()->id() ?? 1,
            ]);

            // 2. Process each adjustment line
            foreach ($request->items as $itemData) {
                $qty = (int) $itemData['quantity'];
                $type = $itemData['type']; // addition or subtraction

                // Lock variation to ensure atomicity
                $variation = ProductVariation::with('product')
                    ->where('id', $itemData['variation_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentStock = (int) $variation->stock;
                $unitCost = (float) ($variation->product->cost_price ?? 0);

                if ($type === 'addition') {
                    $finalStock = $currentStock + $qty;
                    $movementQty = +$qty;
                    $costImpact = $qty * $unitCost;
                    $netQtyAdjusted += $qty;
                    $totalCostImpact += $costImpact;
                } else {
                    $finalStock = max(0, $currentStock - $qty);
                    $movementQty = -$qty;
                    $costImpact = -($qty * $unitCost);
                    $netQtyAdjusted -= $qty;
                    $totalCostImpact += $costImpact;
                }

                // Update variation physical stock
                $variation->stock = $finalStock;
                $variation->save();

                // Record item detail
                StockAdjustmentDetail::create([
                    'stock_adjustment_id' => $adjustment->id,
                    'product_id'          => $itemData['product_id'],
                    'variation_id'        => $variation->id,
                    'type'                => $type,
                    'current_stock'       => $currentStock,
                    'quantity'            => $qty,
                    'final_stock'         => $finalStock,
                    'unit_cost'           => $unitCost,
                    'total_cost'          => abs($costImpact),
                    'reason'              => $itemData['reason'] ?? $request->reason,
                ]);

                // Create immutable StockMovement ledger entry
                StockMovement::create([
                    'product_id'     => $itemData['product_id'],
                    'variation_id'   => $variation->id,
                    'type'           => 'adjustment',
                    'reference_type' => StockAdjustment::class,
                    'reference_id'   => $adjustment->id,
                    'quantity'       => $movementQty,
                    'stock_before'   => $currentStock,
                    'stock_after'    => $finalStock,
                    'unit_cost'      => $unitCost,
                    'note'           => 'Adjustment Ref: ' . $adjustment->reference_no . ' (' . $adjustment->reason_label . ')',
                    'created_by'     => auth()->id() ?? 1,
                ]);
            }

            // Update calculated totals on header
            $adjustment->update([
                'total_qty_adjusted' => $netQtyAdjusted,
                'total_cost_impact'  => $totalCostImpact,
            ]);

            DB::commit();

            return redirect()->route('backend.stock-adjustments.show', $adjustment->id)
                ->with('success', 'Stock Adjustment #' . $adjustment->reference_no . ' recorded successfully! Inventory has been updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process Stock Adjustment: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific stock adjustment details / audit slip.
     */
    public function show($id)
    {
        $adjustment = StockAdjustment::with([
            'creator',
            'items.product.category',
            'items.product.brand',
            'items.variation.color',
            'items.variation.size',
            'movements.creator',
            'movements.product',
            'movements.variation.color',
            'movements.variation.size',
        ])->findOrFail($id);

        

        return view('backend.stock-adjustment.show', compact('adjustment'));
    }
}
