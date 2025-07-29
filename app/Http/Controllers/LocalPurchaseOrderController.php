<?php

namespace App\Http\Controllers;

use App\Models\LocalPurchaseOrder;
use App\Models\LocalPurchaseOrderItem;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocalPurchaseOrderController extends Controller
{
    public function index(): Response
    {
        $lpos = LocalPurchaseOrder::with(['vendor', 'items'])->latest()->get();
        return Inertia::render('lpos/Index', [
            'lpos' => $lpos
        ]);
    }

    public function create(): Response
    {
        $vendors = Vendor::select('id', 'name')->get();
        $products = Product::select('id', 'name', 'price')->get();
        $units = $this->getPredefinedUnits();
        return Inertia::render('lpos/Create', [
            'vendors' => $vendors,
            'products' => $products,
            'units' => $units,
            'nextLpoNumber' => $this->generateLpoNumber(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'lpo_number' => 'required|string|unique:local_purchase_order',
            'issue_date' => 'required|date',
            'trn_number' => 'nullable|string',
            'subject' => 'nullable|string',
            'terms' => 'nullable|string',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $subtotal = 0;
        $totalTax = 0;
        $totalBeforeTax = 0;
        $totalAfterTax = 0;
        foreach ($validated['items'] as $item) {
            $base = $item['quantity'] * $item['unit_price'];
            $tax = $base * (($item['tax_rate'] ?? 0) / 100);
            $subtotal += $base;
            $totalTax += $tax;
            $totalBeforeTax += $base;
            $totalAfterTax += $base + $tax;
        }

        $lpo = LocalPurchaseOrder::create([
            'vendor_id' => $validated['vendor_id'],
            'lpo_number' => $validated['lpo_number'],
            'issue_date' => $validated['issue_date'],
            'trn_number' => $validated['trn_number'],
            'subject' => $validated['subject'],
            'terms' => $validated['terms'],
            'description' => $validated['description'],
            'subtotal' => $subtotal,
            'total_tax' => $totalTax,
            'total_before_tax' => $totalBeforeTax,
            'total_after_tax' => $totalAfterTax,
        ]);

        foreach ($validated['items'] as $item) {
            $base = $item['quantity'] * $item['unit_price'];
            $tax = $base * (($item['tax_rate'] ?? 0) / 100);
            LocalPurchaseOrderItem::create([
                'local_purchase_order_id' => $lpo->id,
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'unit' => $item['unit'] ?? 'pcs',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'total_price_before_tax' => $base,
                'total_price_after_tax' => $base + $tax,
            ]);
        }

        return redirect()->route('lpos.index')->with('message', 'LPO created successfully.');
    }

    public function show(LocalPurchaseOrder $lpo): Response
    {
        $lpo->load(['vendor', 'items.product']);
        return Inertia::render('lpos/Show', [
            'lpo' => $lpo
        ]);
    }

    public function edit(LocalPurchaseOrder $lpo): Response
    {
        $vendors = Vendor::select('id', 'name')->get();
        $products = Product::select('id', 'name', 'price')->get();
        $units = $this->getPredefinedUnits();
        $lpo->load(['items']);
        return Inertia::render('lpos/Edit', [
            'lpo' => $lpo,
            'vendors' => $vendors,
            'products' => $products,
            'units' => $units
        ]);
    }

    public function update(Request $request, LocalPurchaseOrder $lpo)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'issue_date' => 'required|date',
            'trn_number' => 'nullable|string',
            'subject' => 'nullable|string',
            'terms' => 'nullable|string',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.description' => 'required|string',
            'items.*.unit' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $subtotal = 0;
        $totalTax = 0;
        $totalBeforeTax = 0;
        $totalAfterTax = 0;
        foreach ($validated['items'] as $item) {
            $base = $item['quantity'] * $item['unit_price'];
            $tax = $base * (($item['tax_rate'] ?? 0) / 100);
            $subtotal += $base;
            $totalTax += $tax;
            $totalBeforeTax += $base;
            $totalAfterTax += $base + $tax;
        }

        $lpo->update([
            'vendor_id' => $validated['vendor_id'],
            'issue_date' => $validated['issue_date'],
            'trn_number' => $validated['trn_number'],
            'subject' => $validated['subject'],
            'terms' => $validated['terms'],
            'description' => $validated['description'],
            'subtotal' => $subtotal,
            'total_tax' => $totalTax,
            'total_before_tax' => $totalBeforeTax,
            'total_after_tax' => $totalAfterTax,
        ]);

        $lpo->items()->delete();
        foreach ($validated['items'] as $item) {
            $base = $item['quantity'] * $item['unit_price'];
            $tax = $base * (($item['tax_rate'] ?? 0) / 100);
            LocalPurchaseOrderItem::create([
                'local_purchase_order_id' => $lpo->id,
                'product_id' => $item['product_id'],
                'description' => $item['description'],
                'unit' => $item['unit'] ?? 'pcs',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'tax_rate' => $item['tax_rate'] ?? 0,
                'total_price_before_tax' => $base,
                'total_price_after_tax' => $base + $tax,
            ]);
        }

        return redirect()->route('lpos.index')->with('message', 'LPO updated successfully.');
    }

    public function destroy(LocalPurchaseOrder $lpo)
    {
        $lpo->delete();
        return redirect()->route('lpos.index')->with('message', 'LPO deleted successfully.');
    }

    public function downloadPdf(LocalPurchaseOrder $lpo)
    {
        $lpo->load(['vendor', 'items.product']);
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.lpo', [
            'lpo' => $lpo,
        ]);
        
        return $pdf->download("LPO-{$lpo->lpo_number}.pdf");
    }

    private function generateLpoNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastLpo = LocalPurchaseOrder::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
        return sprintf('LPO-%s%s-%04d', $year, $month, $lastLpo + 1);
    }

    private function getPredefinedUnits(): array
    {
        return [
            'pcs' => 'Pieces',
            'kg' => 'Kilogram',
            'gm' => 'Gram',
            'ltr' => 'Liter',
            'ml' => 'Milliliter',
            'mtr' => 'Meter',
            'cm' => 'Centimeter',
            'mm' => 'Millimeter',
            'sq_mtr' => 'Square Meter',
            'cu_mtr' => 'Cubic Meter',
            'ft' => 'Feet',
            'inch' => 'Inch',
            'box' => 'Box',
            'pack' => 'Pack',
            'set' => 'Set',
            'pair' => 'Pair',
            'doz' => 'Dozen',
            'roll' => 'Roll',
            'bundle' => 'Bundle',
            'lot' => 'Lot',
            'unit' => 'Unit',
            'hour' => 'Hour',
            'day' => 'Day',
            'month' => 'Month',
            'year' => 'Year',
        ];
    }
}
