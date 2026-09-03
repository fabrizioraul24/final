<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LogsAudit;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use LogsAudit;

    protected function getLaPazWarehouse(): ?\App\Models\Warehouse
    {
        return \App\Models\Warehouse::where('name', 'like', '%La Paz%')->first();
    }

    protected function normalizeItems(Request $request): array
    {
        $items = [];
        $raw = $request->input('cart');
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    $qty = max(0, (int)($item['qty'] ?? 0));
                    $price = (float)($item['price'] ?? 0);
                    $items[] = [
                        'id' => (int)($item['id'] ?? 0),
                        'name' => $item['name'] ?? 'Producto',
                        'qty' => $qty,
                        'price' => $price,
                    ];
                }
            }
        }
        return $items;
    }

    public function show(Request $request)
    {
        $shipping = 0;
        $items = $this->normalizeItems($request);

        // Historial y recomendaciones básicas
        $history = \App\Models\BuyerOrder::with('items')
            ->where('user_id', optional($request->user())->id)
            ->latest()->take(5)->get();

        $recommended = $history->flatMap->items->pluck('product_name')->unique()->take(3)->values();

        return view('dashboard.pago', [
            'cartItems' => $items,
            'shipping' => $shipping,
            'history' => $history,
            'recommended' => $recommended,
            'paymentSuccess' => session('payment_success'),
            'receiptNumber' => session('receipt_number'),
        ]);
    }

    public function process(Request $request)
    {
        $request->validate([
            'payment_method' => ['required', 'string', 'max:50'],
            'cart' => ['required', 'string'],
        ]);

        $items = $this->normalizeItems($request);
        if (empty($items)) {
            return back()->withErrors(['cart' => 'El carrito está vacío.']);
        }

        $warehouse = $this->getLaPazWarehouse() ?? \App\Models\Warehouse::first();
        if (!$warehouse) {
            return back()->withErrors(['warehouse' => 'No se encontró bodega para descontar.']);
        }

        $paymentMethod = $request->input('payment_method');
        $paymentStatus = in_array(strtolower($paymentMethod), ['efectivo']) ? 'pendiente' : 'completado';
        $subtotal = collect($items)->sum(fn($i) => ($i['qty'] ?? 0) * ($i['price'] ?? 0));
        $shipping = 0;
        $total = $subtotal + $shipping;

        $userId = optional($request->user())->id;
        $receipt = 'RC-' . now()->format('YmdHis') . '-' . rand(100, 999);

        try {
            $order = null;

            \DB::transaction(function () use ($items, $warehouse, $paymentMethod, $paymentStatus, $subtotal, $shipping, $total, $userId, $receipt, &$order) {
                $order = \App\Models\BuyerOrder::create([
                    'user_id' => $userId,
                    'receipt_number' => $receipt,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'status' => 'procesado',
                    'subtotal' => $subtotal,
                    'shipping' => $shipping,
                    'total' => $total,
                    'issued_at' => now(),
                ]);

                foreach ($items as $item) {
                    $productId = (int)($item['id'] ?? 0);
                    $qty = (int)($item['qty'] ?? 0);
                    if ($productId <= 0 || $qty <= 0) {
                        continue;
                    }

                    $available = \App\Models\ProductLot::available($productId, $warehouse->id);
                    if ($available < $qty) {
                        throw new \RuntimeException("Stock insuficiente para {$item['name']} (disponible {$available}).");
                    }

                    \App\Models\ProductLot::where('product_id', $productId)->lockForUpdate()->get();
                    \App\Models\ProductLot::consumeFefo($productId, $warehouse->id, $qty, 'venta', $userId, 'Checkout comprador');

                    \App\Models\BuyerOrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $productId,
                        'product_name' => $item['name'],
                        'quantity' => $qty,
                        'unit_price' => $item['price'],
                    ]);
                }
            });

            if ($order) {
                $order->load(['items.product', 'user']);
                $this->logAudit($order, 'payment', [], [
                    'receipt_number' => $order->receipt_number,
                    'buyer' => $order->user?->name,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'status' => $order->status,
                    'subtotal' => (float) $order->subtotal,
                    'shipping' => (float) $order->shipping,
                    'total' => (float) $order->total,
                    'items' => $order->items->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'product' => $item->product_name,
                        'quantity' => (int) $item->quantity,
                        'unit_price' => (float) $item->unit_price,
                    ])->values()->all(),
                ], 'Pago de comprador procesado. Recibo: ' . $order->receipt_number);
            }
        } catch (\Throwable $e) {
            return back()->withErrors(['cart' => $e->getMessage()]);
        }

        return redirect()
            ->back()
            ->with('payment_success', true)
            ->with('receipt_number', $receipt);
    }

    public function receipt(string $number)
    {
        $order = \App\Models\BuyerOrder::with('items', 'user')->where('receipt_number', $number)->firstOrFail();

        return view('dashboard.recibo', [
            'order' => $order,
        ]);
    }

    public function download(string $number)
    {
        $order = \App\Models\BuyerOrder::with('items', 'user')->where('receipt_number', $number)->firstOrFail();

        if (class_exists(\Dompdf\Dompdf::class)) {
            $html = view('dashboard.recibo_pdf', ['order' => $order])->render();
            $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=recibo-{$number}.pdf",
            ]);
        }

        if (app()->bound('dompdf.wrapper')) {
            $pdf = app('dompdf.wrapper')->loadView('dashboard.recibo_pdf', ['order' => $order]);
            return $pdf->download("recibo-{$number}.pdf");
        }

        // Fallback: HTML si no hay soporte PDF
        $html = view('dashboard.recibo_pdf', ['order' => $order])->render();
        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, "recibo-{$number}.html");
    }
}
