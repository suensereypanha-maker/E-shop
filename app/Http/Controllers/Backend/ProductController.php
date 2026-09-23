<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Backend\Product;
use App\Models\Backend\ProductVariation;
use App\Models\Backend\Category;
use App\Models\Backend\Brands;
use App\Models\Backend\Color;
use App\Models\Size;
use App\Models\Backend\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    /**
     * Store newly created product along with its variations.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'category_id'     => 'nullable|exists:categories,id',
            'brand_id'        => 'nullable|exists:brands,id',
            'sku'             => 'nullable|string|max:100|unique:products,sku',
            'cost_price'      => 'required|numeric|min:0',
            'base_price'      => 'nullable|numeric|min:0',
            'sale_price'      => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'image'           => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
            'description'     => 'nullable|string',
            'color_id'        => 'nullable|exists:colors,id',
            'color_price'     => 'nullable|numeric|min:0',
            'colors'          => 'nullable|array',
            'sizes'           => 'nullable',
            'initial_stock'   => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $baseSku = !empty($validated['sku']) 
                ? strtoupper(trim($validated['sku'])) 
                : 'PRD-' . strtoupper(Str::random(6));

            $slug = Str::slug($validated['name']);
            if (Product::where('slug', $slug)->exists()) {
                $slug .= '-' . time();
            }

            $product = Product::create([
                'name'            => $validated['name'],
                'slug'            => $slug,
                'sku'             => $baseSku ?? null,
                'category_id'     => !empty($validated['category_id']) ? $validated['category_id'] : null,
                'brand_id'        => !empty($validated['brand_id']) ? $validated['brand_id'] : null,
                'cost_price'      => (isset($validated['cost_price']) && is_numeric($validated['cost_price'])) ? $validated['cost_price'] : 0.00,
                'base_price'      => (isset($validated['base_price']) && is_numeric($validated['base_price'])) ? $validated['base_price'] : null,
                'sale_price'      => (isset($validated['sale_price']) && is_numeric($validated['sale_price'])) ? $validated['sale_price'] : null,
                'wholesale_price' => (isset($validated['wholesale_price']) && is_numeric($validated['wholesale_price'])) ? $validated['wholesale_price'] : null,
                'description'     => $validated['description'] ?? null,
                'status'          => 1,
                'is_new'          => 1,
                'created_by'      => auth()->id() ?? 1,
            ]);

            // Handle Image Upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = 'product_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $destinationPath = public_path('uploads/products');
                
                if (!File::isDirectory($destinationPath)) {
                    File::makeDirectory($destinationPath, 0755, true, true);
                }
                
                $image->move($destinationPath, $imageName);
                $product->image = 'uploads/products/' . $imageName;
                $product->save();
            }
            $colorsInput = $request->input('colors');
            $topSizesInput = $request->input('sizes');
            $initialStock = (int) ($validated['initial_stock'] ?? 0);

            $colorGroups = [];

            if (!empty($colorsInput) && is_array($colorsInput)) {
                foreach ($colorsInput as $cKey => $cVal) {
                    if (is_array($cVal)) {
                        $cId = $cVal['color_id'] ?? $cVal['id'] ?? (is_numeric($cKey) && $cKey > 0 ? $cKey : null);
                        $cPrice = (float) ($cVal['color_price'] ?? $cVal['price'] ?? 0);
                        $cSizes = isset($cVal['sizes']) && is_array($cVal['sizes']) ? $cVal['sizes'] : $topSizesInput;
                    } else {
                        $cId = $cVal;
                        $cPrice = 0.0;
                        $cSizes = $topSizesInput;
                    }

                    $colorGroups[] = [
                        'color_id'    => $cId ?: null,
                        'color_price' => $cPrice,
                        'sizes'       => $cSizes,
                    ];
                }
            } else {
                // Single color or standard/no color
                $cId = $request->input('color_id') ?: null;
                $cPrice = (float) ($request->input('color_price') ?: 0);
                $colorGroups[] = [
                    'color_id'    => $cId,
                    'color_price' => $cPrice,
                    'sizes'       => $topSizesInput,
                ];
            }

            foreach ($colorGroups as $group) {
                $cId = $group['color_id'];
                $cPrice = $group['color_price'];
                $colorModel = $cId ? Color::find($cId) : null;
                $colorSuffix = $colorModel ? '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $colorModel->name), 0, 3)) : '';

                $sizesList = $group['sizes'];

                if (!empty($sizesList) && is_array($sizesList)) {
                    // This 1 color has multiple sizes
                    foreach ($sizesList as $key => $val) {
                        $sizeId = is_array($val) ? ($val['id'] ?? $val['size_id'] ?? (is_numeric($key) && $key > 0 ? $key : null)) : $val;
                        $sizePrice = is_array($val) ? (float) ($val['size_price'] ?? $val['price'] ?? 0) : 0.00;
                        $varStock = is_array($val) && isset($val['stock']) ? (int) $val['stock'] : $initialStock;

                        if (!$sizeId) continue;
                        $sizeModel = Size::find($sizeId);
                        if (!$sizeModel) continue;

                        $sizeCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $sizeModel->name), 0, 3));
                        $varSku = $baseSku . $colorSuffix . '-' . $sizeCode;

                        // Ensure unique SKU
                        if (ProductVariation::where('sku', $varSku)->exists()) {
                            $varSku .= '-' . strtoupper(Str::random(3));
                        }

                        $createdVar = ProductVariation::create([
                            'product_id'  => $product->id,
                            'color_id'    => $cId,
                            'size_id'     => $sizeModel->id,
                            'sku'         => $varSku,
                            'color_price' => $cPrice,
                            'size_price'  => $sizePrice,
                            'stock'       => $varStock,
                            'status'      => 1,
                        ]);

                        if ($varStock > 0) {
                            StockMovement::create([
                                'product_id'     => $product->id,
                                'variation_id'   => $createdVar->id,
                                'type'           => 'stock_in',
                                'reference_type' => Product::class,
                                'reference_id'   => $product->id,
                                'quantity'       => $varStock,
                                'stock_before'   => 0,
                                'stock_after'    => $varStock,
                                'unit_cost'      => $product->cost_price ?? 0,
                                'note'           => 'Initial opening stock upon product creation',
                                'created_by'     => auth()->id() ?? 1,
                            ]);
                        }
                    }
                } else {
                    $varSku = $baseSku . ($colorSuffix ?: '-STD');
                    if (ProductVariation::where('sku', $varSku)->exists()) {
                        $varSku .= '-' . strtoupper(Str::random(3));
                    }

                    $createdVar = ProductVariation::create([
                        'product_id'  => $product->id,
                        'color_id'    => $cId,
                        'size_id'     => null,
                        'sku'         => $varSku,
                        'color_price' => $cPrice,
                        'size_price'  => 0.00,
                        'stock'       => $initialStock,
                        'status'      => 1,
                    ]);

                    if ($initialStock > 0) {
                        StockMovement::create([
                            'product_id'     => $product->id,
                            'variation_id'   => $createdVar->id,
                            'type'           => 'stock_in',
                            'reference_type' => Product::class,
                            'reference_id'   => $product->id,
                            'quantity'       => $initialStock,
                            'stock_before'   => 0,
                            'stock_after'    => $initialStock,
                            'unit_cost'      => $product->cost_price ?? 0,
                            'note'           => 'Initial opening stock upon product creation',
                            'created_by'     => auth()->id() ?? 1,
                        ]);
                    }
                }
            }

            DB::commit();

            $product->load(['variations.color', 'variations.size', 'category', 'brand']);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'success',
                    'message' => "Product  variation(s)!",
                    'product' => [
                        'id'         => $product->id,
                        'name'       => $product->name,
                        'sku'        => $product->sku,
                        'cost_price' => $product->cost_price,
                        'base_price' => $product->base_price,
                        'variations' => $product->variations->map(function ($v) {
                            $cName = optional($v->color)->name ?? 'Standard';
                            $sName = optional($v->size)->name ?? 'Standard';
                            return [
                                'id'          => $v->id,
                                'sku'         => $v->sku,
                                'color'       => $cName,
                                'size'        => $sName,
                                'stock'       => $v->stock,
                                'color_price' => $v->color_price,
                                'size_price'  => $v->size_price,
                                'label'       => "{$cName} / {$sName}",
                            ];
                        }),
                    ],
                ]);
            }

            return redirect()->back()->with('success', "Product  created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Failed to create product: ' . $e->getMessage(),
                ], 422);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to create product: ' . $e->getMessage());
        }
    }
}
