<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ServiceOrder;
use App\Models\Product;
use App\Models\Payment;
use App\Enums\ServiceOrderStatus;
use Illuminate\Http\Request;

class ServiceOrderController extends Controller
{
    public function index()
    {
        $companyId = auth()->user()->company_id;
        
        $queued = ServiceOrder::where('company_id', $companyId)
            ->where('status', ServiceOrderStatus::QUEUED)
            ->orderBy('created_at', 'asc')
            ->get();
            
        $finished = ServiceOrder::where('company_id', $companyId)
            ->where('status', ServiceOrderStatus::FINISHED)
            ->whereDate('finished_at', today())
            ->latest()
            ->get();
            
        $products = Product::where('company_id', $companyId)
            ->where('active', true)
            ->get();
            
        return view('operator.dashboard', compact('queued', 'finished', 'products'));
    }

    public function finish(ServiceOrder $order)
    {
        $this->authorizeCompany($order);
        
        $order->update([
            'status' => ServiceOrderStatus::FINISHED,
            'finished_at' => now(),
        ]);
        
        return redirect()->route('operator.dashboard')
            ->with('success', 'Ordem finalizada!');
    }
    
    public function requestCancel(ServiceOrder $order, Request $request)
    {
        $this->authorizeCompany($order);
        
        $request->validate(['reason' => 'required|string']);
        
        // Criar solicitação de cancelamento (usando notes)
        \App\Models\Note::create([
            'company_id' => $order->company_id,
            'user_id' => auth()->id(),
            'content' => "Solicitação de cancelamento - Ordem {$order->code}: {$request->reason}",
            'type' => 'cancel_request',
            'related_id' => $order->id,
        ]);
        
        return redirect()->route('operator.dashboard')
            ->with('success', 'Solicitação enviada para o gerente!');
    }

    private function authorizeCompany($order)
    {
        if ($order->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        
        $washes = Product::where('company_id', $companyId)
            ->where('type', 'wash')
            ->where('active', true)
            ->with('prices')
            ->get();
        
        $products = Product::where('company_id', $companyId)
            ->whereIn('type', ['drink', 'snack'])
            ->where('active', true)
            ->with('prices')
            ->get();
            
        return view('operator.create-order', compact('washes', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_type' => 'required|in:pf,pj',
            'customer_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'company_document' => 'nullable|string|max:18',
            'contact_name' => 'nullable|string|max:255',
            'license_plate' => 'required|string|max:20', // alterado para required
            'vehicle_type' => 'required|in:normal,suv,pickup',
            'products' => 'required|array|min:1',
            'products.*' => 'exists:products,id',
            'payments' => 'nullable|string',
        ]);

        $companyId = auth()->user()->company_id;
        $vehicleType = $request->vehicle_type;
        
        $total = 0;
        
        // Preparar dados do cliente baseado no tipo
        $customerName = null;
        $customerDocument = null;
        $contactName = null;
        
        if ($request->customer_type === 'pj') {
            $customerName = $request->company_name;
            $customerDocument = $request->company_document;
            $contactName = $request->contact_name;
        } else {
            $customerName = $request->customer_name;
        }
        
        $order = ServiceOrder::create([
            'company_id' => $companyId,
            'customer_name' => $customerName,
            'customer_type' => $request->customer_type,
            'customer_document' => $customerDocument,
            'contact_name' => $contactName,
            'license_plate' => strtoupper($request->license_plate),
            'code' => ServiceOrder::generateCode(),
            'status' => ServiceOrderStatus::QUEUED,
            'vehicle_type' => $vehicleType,
            'total' => 0,
        ]);
        
        
        foreach ($request->products as $productId) {
            $product = Product::with('prices')->find($productId);
            $price = $product->getPriceForVehicleType($vehicleType);
            
            $order->products()->attach($productId, [
                'quantity' => 1,
                'unit_price' => $price,
                'total_price' => $price,
            ]);
            $total += $price;
        }
        
        $order->update(['total' => $total]);
        
        // Salvar pagamentos (split payment)
        if ($request->payments) {
            $payments = json_decode($request->payments, true);
            foreach ($payments as $payment) {
                \App\Models\Payment::create([
                    'service_order_id' => $order->id,
                    'method' => strtolower($payment['method']),
                    'amount' => $payment['amount'],
                ]);
            }
        }
        
        return redirect()->route('operator.orders.receipt', $order)
            ->with('success', 'Ordem criada com sucesso!');
    }

    public function receipt(ServiceOrder $order)
    {
        $this->authorizeCompany($order);        
        $company = auth()->user()->company;        
        return view('operator.receipt', compact('order', 'company'));
    }

    public function deliver(ServiceOrder $order)
    {
        $this->authorizeCompany($order);
        
        $order->update([
            'status' => ServiceOrderStatus::FINISHED,
            'delivered_at' => now(),
        ]);
        
        return redirect()->route('operator.dashboard')
            ->with('success', 'Veículo entregue com sucesso!');
    }

    public function showDeliverForm(ServiceOrder $order)
    {
        $this->authorizeCompany($order);
        
        // Verificar se a ordem está finalizada
        if ($order->status !== ServiceOrderStatus::QUEUED) {
            return redirect()->route('operator.orders.create')
                ->with('error', 'Apenas ordens finalizadas podem ser entregues.');
        }
        
        return view('operator.deliver-order', compact('order'));
    }

    public function servicesToDeliver()
    {
        $companyId = auth()->user()->company_id;
        
        $finishedOrders = ServiceOrder::with(['products', 'payments'])
            ->where('company_id', $companyId)
            ->where('status', ServiceOrderStatus::QUEUED)
            ->orderBy('finished_at', 'desc')
            ->get();
        
        return view('operator.services-to-deliver', compact('finishedOrders'));
    }    
}