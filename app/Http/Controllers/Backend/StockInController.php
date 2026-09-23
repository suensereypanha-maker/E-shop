<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\DataTables\Backend\StockInDataTable;
use App\Models\Backend\StockIn;
use App\Models\Backend\StockInDetail;
use App\Models\Backend\StockMovement;
use App\Models\Backend\Supplier;
use App\Models\Backend\Product;
use App\Models\Backend\ProductVariation;
use App\Models\Backend\Category;
use App\Models\Backend\Brands;
use App\Models\Backend\Color;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    /**
     * Display a listing of stock-in records.
     */
    public function index(StockInDataTable $dataTable)
    {
        $stats = [
            'total_transactions' => StockIn::count(),
            'total_received_qty' => StockIn::where('status', 'received')->sum('total_quantity'),
            'total_received_cost' => StockIn::where('status', 'received')->sum('total_cost'),
        ];

        $suppliers = Supplier::where('status', 1)->orderBy('name', 'asc')->get();
        $products = Product::with(['variations.color', 'variations.size', 'category', 'brand'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get();

        $categories = Category::where('status', 1)->orderBy('name', 'asc')->get();
        $brands = Brands::where('status', 1)->orderBy('name', 'asc')->get();
        $colors = Color::where('status', 1)->orderBy('name', 'asc')->get();
        $sizes = Size::where('status', 1)->orderBy('name', 'asc')->get();

        $referenceNo = StockIn::generateReferenceNo();

        return $dataTable->render('backend.stock-in.index', compact('stats', 'suppliers', 'products', 'categories', 'brands', 'colors', 'sizes', 'referenceNo'));
    }

    /**
     * Show form to create new stock-in.
     */
    public function create()
    {
        $suppliers = Supplier::where('status', 1)->orderBy('name', 'asc')->get();
        $products = Product::with(['variations.color', 'variations.size', 'category', 'brand'])
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get();

        $referenceNo = StockIn::generateReferenceNo();
        $supplierInvoiceNo = StockIn::generateSupplierInvoiceNo($referenceNo);

        return view('backend.stock-in.create', compact('suppliers', 'products', 'referenceNo', 'supplierInvoiceNo'));
    }

    /**
     * Store newly created stock-in in database with transaction.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'          => 'nullable|exists:suppliers,id',
            'supplier_invoice_no'  => 'nullable|string|max:100',
            'received_date'        => 'required|date',
            'note'                 => 'nullable|string|max:1000',
            'items'                => 'required|array|min:1',
            'items.*.product_id'   => 'required|exists:products,id',
            'items.*.variation_id' => 'required|exists:product_variations,id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.unit_cost'    => 'required|numeric|min:0',
            'items.*.batch_no'     => 'nullable|string|max:100',
            'items.*.expiry_date'  => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $totalQty = 0;
            $totalCost = 0;

            foreach ($request->items as $item) {
                $qty = (int) $item['quantity'];
                $cost = (float) $item['unit_cost'];
                $totalQty += $qty;
                $totalCost += ($qty * $cost);
            }

            $refNo = $request->reference_no ?: StockIn::generateReferenceNo($request->received_date);
            $supplierInvoiceNo = $request->supplier_invoice_no ?: StockIn::generateSupplierInvoiceNo($refNo, $request->received_date);

            // 1. Create Stock In Header
            $stockIn = StockIn::create([
                'reference_no'        => $refNo,
                'supplier_id'         => $request->supplier_id,
                'supplier_invoice_no' => $supplierInvoiceNo,
                'received_date'       => $request->received_date,
                'status'              => 'received',
                'total_quantity'      => $totalQty,
                'total_cost'          => $totalCost,
                'note'                => $request->note,
                'created_by'          => auth()->id() ?? 1,
            ]);

            // 2. Insert items and update variation stock
            foreach ($request->items as $itemData) {
                $qty = (int) $itemData['quantity'];
                $unitCost = (float) $itemData['unit_cost'];
                $subtotal = $qty * $unitCost;

                // Lock & update variation stock
                $variation = ProductVariation::where('id', $itemData['variation_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $stockBefore = (int) $variation->stock;
                $stockAfter = $stockBefore + $qty;

                $variation->stock = $stockAfter;
                $variation->save();

                // Create detail item with stock_before and stock_after
                StockInDetail::create([
                    'stock_in_id'  => $stockIn->id,
                    'product_id'   => $itemData['product_id'],
                    'variation_id' => $itemData['variation_id'],
                    'quantity'     => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after'  => $stockAfter,
                    'unit_cost'    => $unitCost,
                    'total_cost'   => $subtotal,
                    'batch_no'     => $itemData['batch_no'] ?? null,
                    'expiry_date'  => $itemData['expiry_date'] ?? null,
                ]);

                // Update product latest purchase cost_price
                Product::where('id', $itemData['product_id'])->update([
                    'cost_price' => $unitCost
                ]);

                // Create immutable stock movement ledger entry
                StockMovement::create([
                    'product_id'     => $itemData['product_id'],
                    'variation_id'   => $variation->id,
                    'type'           => 'stock_in',
                    'reference_type' => StockIn::class,
                    'reference_id'   => $stockIn->id,
                    'quantity'       => $qty,
                    'stock_before'   => $stockBefore,
                    'stock_after'    => $stockAfter,
                    'unit_cost'      => $unitCost,
                    'note'           => 'Stock In Ref: ' . $stockIn->reference_no,
                    'created_by'     => auth()->id() ?? 1,
                ]);
            }

            DB::commit();

            return redirect()->route('backend.stock-ins.show', $stockIn->id)
                ->with('success', 'Stock In ' . $stockIn->reference_no . ' recorded successfully! Inventory updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to process Stock In: ' . $e->getMessage());
        }
    }

    /**
     * Display a specific stock-in details (Receipt / Invoice).
     */
    public function show($id)
    {
        $stockIn = StockIn::with([
            'supplier',
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

       

       
        return view('backend.stock-in.show', compact('stockIn'));
    }

    
    public function cancel($id)
    {
        $stockIn = StockIn::with('items')->findOrFail($id);

        if ($stockIn->status === 'cancelled') {
            return back()->with('error', 'This Stock In record is already cancelled.');
        }

        DB::beginTransaction();
        try {
            foreach ($stockIn->items as $item) {
                if ($item->variation_id) {
                    $variation = ProductVariation::where('id', $item->variation_id)
                        ->lockForUpdate()
                        ->first();

                    if ($variation) {
                        $stockBefore = (int) $variation->stock;
                        $stockAfter = max(0, $stockBefore - (int) $item->quantity);

                        $variation->stock = $stockAfter;
                        $variation->save();

                        // Write reversal movement log
                        StockMovement::create([
                            'product_id'     => $item->product_id,
                            'variation_id'   => $variation->id,
                            'type'           => 'adjustment',
                            'reference_type' => StockIn::class,
                            'reference_id'   => $stockIn->id,
                            'quantity'       => -$item->quantity,
                            'stock_before'   => $stockBefore,
                            'stock_after'    => $stockAfter,
                            'unit_cost'      => $item->unit_cost,
                            'note'           => 'Reversed cancellation of Stock In #' . $stockIn->reference_no,
                            'created_by'     => auth()->id() ?? 1,
                        ]);
                    }
                }
            }

            $stockIn->status = 'cancelled';
            $stockIn->updated_by = auth()->id() ?? 1;
            $stockIn->save();

            DB::commit();

            return redirect()->route('backend.stock-ins.index')
                ->with('success', 'Stock In #' . $stockIn->reference_no . ' was cancelled and stock was reverted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Could not cancel Stock In: ' . $e->getMessage());
        }
    }

    /**
     * AJAX endpoint to return variations for a chosen product.
     */
    public function getProductVariations($productId)
    {
        $product = Product::with(['variations.color', 'variations.size'])->findOrFail($productId);
        return response()->json([
            'status' => 'success',
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'cost_price' => $product->cost_price,
            ],
            'variations' => $product->variations->map(function ($v) {
                $colorName = optional($v->color)->name ?? 'Standard';
                $sizeName = optional($v->size)->name ?? 'Standard';
                return [
                    'id' => $v->id,
                    'sku' => $v->sku,
                    'color' => $colorName,
                    'size' => $sizeName,
                    'stock' => $v->stock,
                    'label' => "{$colorName} / {$sizeName}",
                ];
            }),
        ]);
    }
}
