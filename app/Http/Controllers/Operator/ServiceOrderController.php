<?php

namespace App\Http\Controllers\Operator;

use App\Enums\ServiceOrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Models\Note;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ServiceOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceOrderController extends Controller
{
    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;
        $status = $request->string('status')->toString();
        $search = trim($request->string('q')->toString());

        $orders = ServiceOrder::query()
            ->with(['products:id,name', 'payments:id,service_order_id,method,amount'])
            ->where('company_id', $companyId)
            ->when($status && in_array($status, array_column(ServiceOrderStatus::cases(), 'value'), true), fn ($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('code', 'ilike', "%{$search}%")
                        ->orWhere('license_plate', 'ilike', "%{$search}%")
                        ->orWhere('customer_name', 'ilike', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $counts = ServiceOrder::where('company_id', $companyId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('operator.orders-index', compact('orders', 'counts', 'status', 'search'));
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        $base = Product::where('company_id', $companyId)->where('active', true)->with('prices:id,product_id,vehicle_type,price');
        $washes = (clone $base)->where('type', 'wash')->orderBy('name')->get();
        $products = (clone $base)->whereIn('type', ['drink', 'snack', 'other'])->orderBy('type')->orderBy('name')->get();

        return view('operator.create-order', compact('washes', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_type' => 'required|in:pf,pj',
            'customer_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_document' => 'nullable|string|max:18',
            'contact_name' => 'nullable|string|max:255',
            'license_plate' => 'required|string|max:20',
            'vehicle_type' => 'required|in:normal,suv,pickup',
            'initial_status' => ['required', Rule::in([ServiceOrderStatus::WAITING->value, ServiceOrderStatus::FINISHED->value])],
            'items' => 'required|array|min:1|max:150',
            'items.*' => 'required|integer|min:1|max:100',
            'payments' => 'nullable|string',
        ]);

        // A interface envia um mapa produto=>quantidade; todos os valores e preços
        // são validados novamente no servidor, nunca a partir do navegador.
        $quantities = [];
        foreach ($validated['items'] as $id => $qty) {
            if (!ctype_digit((string) $id) || (int) $id < 1) {
                throw ValidationException::withMessages(['items' => 'Produto inválido na ordem.']);
            }
            $quantities[(int) $id] = (int) $qty;
        }

        $companyId = auth()->user()->company_id;
        $products = Product::query()
            ->where('company_id', $companyId)
            ->where('active', true)
            ->whereIn('id', array_keys($quantities))
            ->with('prices:id,product_id,vehicle_type,price')
            ->get()->keyBy('id');

        if ($products->count() !== count($quantities)) {
            throw ValidationException::withMessages(['items' => 'Um ou mais produtos não estão disponíveis nesta empresa.']);
        }
        $washCount = 0;
        $pivot = [];
        $totalCents = 0;
        foreach ($quantities as $id => $qty) {
            $product = $products[$id];
            if ($product->type === ProductType::WASH) {
                $washCount++;
                if ($qty !== 1) {
                    throw ValidationException::withMessages(['items' => 'A lavagem deve ter quantidade 1.']);
                }
            }
            $unitCents = (int) round($product->getPriceForVehicleType($validated['vehicle_type']) * 100);
            if ($unitCents <= 0) {
                throw ValidationException::withMessages(['items' => "O produto {$product->name} não possui preço válido para o veículo selecionado."]);
            }
            $lineCents = $unitCents * $qty;
            $totalCents += $lineCents;
            $pivot[$id] = [
                'quantity' => $qty,
                'unit_price' => $unitCents / 100,
                'total_price' => $lineCents / 100,
            ];
        }
        if ($washCount !== 1) {
            throw ValidationException::withMessages(['items' => 'Selecione uma única lavagem para a ordem.']);
        }

        $rawPayments = json_decode($validated['payments'] ?? '[]', true);
        if (!is_array($rawPayments) || !array_is_list($rawPayments) || count($rawPayments) > 10) {
            throw ValidationException::withMessages(['payments' => 'Formato dos pagamentos inválido.']);
        }
        $payments = [];
        $paidCents = 0;
        foreach ($rawPayments as $entry) {
            if (!is_array($entry) || !isset($entry['method'], $entry['amount'])
                || !is_string($entry['method']) || !PaymentMethod::tryFrom($entry['method'])
                || !is_numeric($entry['amount'])) {
                throw ValidationException::withMessages(['payments' => 'Forma de pagamento inválida.']);
            }
            $amount = (float) $entry['amount'];
            $cents = (int) round($amount * 100);
            if (!is_finite($amount) || $cents < 1 || abs($amount * 100 - $cents) > 0.0001) {
                throw ValidationException::withMessages(['payments' => 'Valor do pagamento inválido. Use duas casas decimais.']);
            }
            $payments[] = ['method' => $entry['method'], 'amount' => $cents / 100];
            $paidCents += $cents;
        }
        if ($paidCents > $totalCents) {
            throw ValidationException::withMessages(['payments' => 'O total de pagamentos não pode superar o valor da ordem.']);
        }

        $customerName = $validated['customer_type'] === 'pj'
            ? ($validated['company_name'] ?? null)
            : ($validated['customer_name'] ?? null);
        $customerDocument = $validated['customer_type'] === 'pj' ? ($validated['company_document'] ?? null) : null;
        $contactName = $validated['customer_type'] === 'pj' ? ($validated['contact_name'] ?? null) : null;
        $initialStatus = ServiceOrderStatus::from($validated['initial_status']);

        $order = DB::transaction(function () use ($validated, $companyId, $customerName, $customerDocument, $contactName, $initialStatus, $pivot, $totalCents, $payments) {
            $order = ServiceOrder::create([
                'company_id' => $companyId,
                'customer_name' => $customerName,
                'customer_type' => $validated['customer_type'],
                'customer_document' => $customerDocument,
                'contact_name' => $contactName,
                'license_plate' => strtoupper(trim($validated['license_plate'])),
                'code' => ServiceOrder::generateCode(),
                'status' => $initialStatus,
                'queued_at' => null,
                'finished_at' => $initialStatus === ServiceOrderStatus::FINISHED ? now() : null,
                'vehicle_type' => $validated['vehicle_type'],
                'total' => $totalCents / 100,
            ]);
            $order->products()->attach($pivot);
            if ($payments) {
                $now = now();
                Payment::insert(array_map(fn ($payment) => [
                    'service_order_id' => $order->id,
                    'method' => $payment['method'],
                    'amount' => $payment['amount'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $payments));
            }
            return $order;
        });

        return redirect()->route('operator.orders.index')->with('success', "Ordem {$order->code} criada com sucesso.");
    }

    public function updateStatus(Request $request, ServiceOrder $order)
    {
        $this->authorizeCompany($order);
        $request->validate(['status' => ['required', Rule::enum(ServiceOrderStatus::class)]]);
        $next = ServiceOrderStatus::from($request->status);
        $current = $order->status;

        $allowed = [
            ServiceOrderStatus::WAITING->value => [ServiceOrderStatus::QUEUED, ServiceOrderStatus::FINISHED],
            ServiceOrderStatus::QUEUED->value => [ServiceOrderStatus::WAITING, ServiceOrderStatus::FINISHED],
            ServiceOrderStatus::FINISHED->value => [ServiceOrderStatus::DELIVERED],
            ServiceOrderStatus::DELIVERED->value => [],
            ServiceOrderStatus::CANCELLED->value => [],
        ];
        abort_unless(in_array($next, $allowed[$current->value] ?? [], true), 422, 'Transição de status inválida.');

        $data = ['status' => $next];
        if ($next === ServiceOrderStatus::QUEUED) $data['queued_at'] = now();
        if ($next === ServiceOrderStatus::FINISHED) $data['finished_at'] = now();
        if ($next === ServiceOrderStatus::DELIVERED) $data['delivered_at'] = now();
        $order->update($data);

        return back()->with('success', "Ordem {$order->code}: {$next->label()}.");
    }

    public function finish(ServiceOrder $order)
    {
        request()->merge(['status' => ServiceOrderStatus::FINISHED->value]);
        return $this->updateStatus(request(), $order);
    }

    public function requestCancel(ServiceOrder $order, Request $request)
    {
        $this->authorizeCompany($order);
        $request->validate(['reason' => 'required|string|max:1000']);
        Note::create([
            'company_id' => $order->company_id, 'user_id' => auth()->id(),
            'content' => "Solicitação de cancelamento - Ordem {$order->code}: {$request->reason}",
            'type' => 'cancel_request', 'related_id' => $order->id,
        ]);
        return back()->with('success', 'Solicitação enviada para o gerente.');
    }

    public function receipt(ServiceOrder $order)
    {
        $this->authorizeCompany($order);
        $order->loadMissing(['products', 'payments']);
        return view('operator.receipt', ['order' => $order, 'company' => auth()->user()->company]);
    }

    public function deliver(ServiceOrder $order)
    {
        $this->authorizeCompany($order);
        abort_unless($order->status === ServiceOrderStatus::FINISHED, 422, 'A ordem ainda não está pronta para entrega.');
        $order->update(['status' => ServiceOrderStatus::DELIVERED, 'delivered_at' => now()]);
        return redirect()->route('operator.orders.index')->with('success', "Veículo da ordem {$order->code} entregue com sucesso.");
    }

    public function showDeliverForm(ServiceOrder $order)
    {
        $this->authorizeCompany($order);
        abort_unless($order->status === ServiceOrderStatus::FINISHED, 422, 'A ordem ainda não está pronta para entrega.');
        $order->loadMissing(['products', 'payments']);
        return view('operator.deliver-order', compact('order'));
    }

    public function servicesToDeliver()
    {
        $finishedOrders = ServiceOrder::with(['products:id,name', 'payments:id,service_order_id,method,amount'])
            ->where('company_id', auth()->user()->company_id)
            ->where('status', ServiceOrderStatus::FINISHED)
            ->orderByDesc('finished_at')->paginate(18);
        return view('operator.services-to-deliver', compact('finishedOrders'));
    }


    public function addPayment(Request $request, ServiceOrder $order)
    {
        $this->authorizeCompany($order);
        $data = $request->validate([
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'amount' => 'required|numeric|min:0.01',
        ]);
        $paid = (float) $order->payments()->sum('amount');
        $remaining = max(0, (float) $order->total - $paid);
        abort_if((float) $data['amount'] > $remaining + 0.01, 422, 'O pagamento excede o saldo pendente.');

        $order->payments()->create($data);
        return back()->with('success', 'Pagamento registrado com sucesso.');
    }

    private function normalizePaymentMethod(string $method): string
    {
        return match (mb_strtolower(trim($method))) {
            'dinheiro', 'cash' => PaymentMethod::CASH->value,
            'crédito', 'credito', 'credit' => PaymentMethod::CREDIT->value,
            'débito', 'debito', 'debit' => PaymentMethod::DEBIT->value,
            'pix' => PaymentMethod::PIX->value,
            default => PaymentMethod::PIX->value,
        };
    }

    private function authorizeCompany(ServiceOrder $order): void
    {
        abort_unless($order->company_id === auth()->user()->company_id, 403);
    }
}
