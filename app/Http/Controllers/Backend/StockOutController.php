<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\DataTables\Backend\StockOutDataTable;
use App\Models\Backend\StockOut;
use App\Models\Backend\StockOutDetail;
use App\Models\Backend\StockMovement;
use App\Models\Backend\Product;
use App\Models\Backend\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    /**
     * Display a listing of stock out records.
     */
    public function index(StockOutDataTable $dataTable)
    {
        $stats = [
            'total_dispatches'     => StockOut::count(),
            'total_dispatched_qty' => StockOut::where('status', 'dispatched')->sum('total_quantity'),
            'total_dispatched_cost'=> StockOut::where('status', 'dispatched')->sum('total_cost'),
            'total_cancelled'      => StockOut::where('status', 'cancelled')->count(),
        ];

        $products = Product::with(['variations.color', 'variations.size', 'category', 'brand'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get();

        $referenceNo = StockOut::generateReferenceNo();

        return $dataTable->render('backend.stock-out.index', compact('stats', 'products', 'referenceNo'));
    }

    /**
     * Store newly created stock out in database with transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reference_no'        => 'nullable|string|max:50',
            'date'                => 'required|date',
            'reason'              => 'required|string|in:sale_dispatch,damage_scrap,internal_use,sample,return_supplier,expired,loss_theft,other',
            'recipient_name'      => 'nullable|string|max:255',
            'note'                => 'nullable|string|max:1000',
            'items'               => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.variation_id' => 'required|exists:product_variations,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.reason'       => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $totalQty = 0;
            $totalCost = 0;

            // 1. First validate stock availability for each item
            foreach ($request->items as $itemData) {
                $variation = ProductVariation::with('product')
                    ->where('id', $itemData['variation_id'])
                    ->firstOrFail();

                $requestedQty = (int) $itemData['quantity'];
                if ($variation->stock < $requestedQty) {
                    $prodName = $variation->product->name ?? 'Product';
                    $varLabel = trim((optional($variation->color)->name ?? '') . ' ' . (optional($variation->size)->name ?? ''));
                    throw new \Exception("Insufficient stock for {$prodName} ({$varLabel}). Available: {$variation->stock}, Requested: {$requestedQty}");
                }

                $unitCost = isset($itemData['unit_cost']) && $itemData['unit_cost'] !== ''
                    ? (float) $itemData['unit_cost']
                    : (float) ($variation->product->cost_price ?? 0);
                $totalQty += $requestedQty;
                $totalCost += ($requestedQty * $unitCost);
            }

            // 2. Create Stock Out Header
            $stockOut = StockOut::create([
                'reference_no'   => $request->reference_no ?: StockOut::generateReferenceNo(),
                'date'           => $request->date,
                'reason'         => $request->reason,
                'recipient_name' => $request->recipient_name,
                'status'         => 'dispatched',
                'total_quantity' => $totalQty,
                'total_cost'     => $totalCost,
                'note'           => $request->note,
                'created_by'     => auth()->id() ?? 1,
            ]);

            // 3. Process items, deduct variation stock & write ledger movements
            foreach ($request->items as $itemData) {
                $qty = (int) $itemData['quantity'];

                $variation = ProductVariation::with('product')
                    ->where('id', $itemData['variation_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentStock = (int) $variation->stock;
                $finalStock = max(0, $currentStock - $qty);
                $unitCost = isset($itemData['unit_cost']) && $itemData['unit_cost'] !== ''
                    ? (float) $itemData['unit_cost']
                    : (float) ($variation->product->cost_price ?? 0);
                $subtotal = $qty * $unitCost;

                // Deduct variation stock
                $variation->stock = $finalStock;
                $variation->save();

                // Record detail item
                StockOutDetail::create([
                    'stock_out_id' => $stockOut->id,
                    'product_id'   => $itemData['product_id'],
                    'variation_id' => $variation->id,
                    'quantity'     => $qty,
                    'stock_before' => $currentStock,
                    'stock_after'  => $finalStock,
                    'unit_cost'    => $unitCost,
                    'total_cost'   => $subtotal,
                    'reason'       => $itemData['reason'] ?? $request->reason,
                ]);

                // Create immutable StockMovement ledger entry
                StockMovement::create([
                    'product_id'     => $itemData['product_id'],
                    'variation_id'   => $variation->id,
                    'type'           => 'stock_out',
                    'reference_type' => StockOut::class,
                    'reference_id'   => $stockOut->id,
                    'quantity'       => -$qty,
                    'stock_before'   => $currentStock,
                    'stock_after'    => $finalStock,
                    'unit_cost'      => $unitCost,
                    'note'           => 'Stock Out Ref: ' . $stockOut->reference_no . ' (' . $stockOut->reason_label . ')',
                    'created_by'     => auth()->id() ?? 1,
                ]);
            }

            DB::commit();

            return redirect()->route('backend.stock-outs.show', $stockOut->id)
                ->with('success', 'Stock Out #' . $stockOut->reference_no . ' recorded successfully! Inventory has been updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process Stock Out: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific stock out voucher / dispatch slip.
     */
    public function show($id)
    {
        $stockOut = StockOut::with([
            'creator',
            'items.product.category',
            'items.product.brand',
            'items.variation.color',
            'items.variation.size',
        ])->findOrFail($id);

        return view('backend.stock-out.show', compact('stockOut'));
    }

    /**
     * Cancel / Reverse a stock out dispatch and restore inventory.
     */
    public function cancel($id)
    {
        $stockOut = StockOut::with('items.variation')->findOrFail($id);

        if ($stockOut->status === 'cancelled') {
            return back()->with('error', 'This Stock Out record is already cancelled.');
        }

        DB::beginTransaction();
        try {
            foreach ($stockOut->items as $item) {
                if ($item->variation_id) {
                    $variation = ProductVariation::where('id', $item->variation_id)
                        ->lockForUpdate()
                        ->first();

                    if ($variation) {
                        $stockBefore = (int) $variation->stock;
                        $stockAfter = $stockBefore + (int) $item->quantity;

                        $variation->stock = $stockAfter;
                        $variation->save();

                        // Reversal movement ledger
                        StockMovement::create([
                            'product_id'     => $item->product_id,
                            'variation_id'   => $variation->id,
                            'type'           => 'adjustment',
                            'reference_type' => StockOut::class,
                            'reference_id'   => $stockOut->id,
                            'quantity'       => +$item->quantity,
                            'stock_before'   => $stockBefore,
                            'stock_after'    => $stockAfter,
                            'unit_cost'      => $item->unit_cost,
                            'note'           => 'Reversed cancellation of Stock Out #' . $stockOut->reference_no,
                            'created_by'     => auth()->id() ?? 1,
                        ]);
                    }
                }
            }

            $stockOut->status = 'cancelled';
            $stockOut->updated_by = auth()->id() ?? 1;
            $stockOut->save();

            DB::commit();

            return redirect()->route('backend.stock-outs.index')
                ->with('success', 'Stock Out #' . $stockOut->reference_no . ' was cancelled and stock was restored successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Could not cancel Stock Out: ' . $e->getMessage());
        }
    }
}
