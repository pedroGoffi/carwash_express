@extends('layouts.app')

@section('title', 'Serviços para Entregar')
@section('header', 'Entregar Veículos')
@section('subheader', 'Selecione o serviço finalizado para devolver as chaves ao cliente')

@section('content')
<div class="space-y-6">
    
    {{-- Serviços Finalizados Aguardando Entrega --}}
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-key text-white text-lg"></i>
                </div>
                <div>
                    <h2 class="text-white font-semibold">Serviços Finalizados</h2>
                    <p class="text-purple-100 text-sm">Aguardando devolução das chaves</p>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if($finishedOrders->count() > 0)
                <div class="grid grid-cols-1 gap-4">
                    @foreach($finishedOrders as $order)
                    <div class="border-2 border-gray-200 rounded-xl p-5 hover:border-purple-300 hover:shadow-lg transition-all">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            
                            {{-- Informações da OS --}}
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="font-mono text-lg font-bold text-purple-600">#{{ $order->code }}</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i> Finalizado
                                    </span>
                                </div>
                                
                                {{-- Cliente --}}
                                <div class="flex items-center gap-2 text-gray-600 mb-1">
                                    <i class="fas fa-user text-gray-400 w-4"></i>
                                    <span class="text-sm">
                                        @if($order->customer_type == 'pj')
                                            {{ $order->customer_name }} ({{ $order->customer_document }})
                                        @else
                                            {{ $order->customer_name ?? 'Cliente não identificado' }}
                                        @endif
                                    </span>
                                </div>
                                
                                {{-- Veículo --}}
                                <div class="flex items-center gap-2 text-gray-600">
                                    <i class="fas fa-car text-gray-400 w-4"></i>
                                    <span class="text-sm uppercase font-mono">{{ $order->license_plate }}</span>
                                    <span class="text-xs text-gray-400">
                                        @if($order->vehicle_type == 'normal') Carro
                                        @elseif($order->vehicle_type == 'suv') SUV
                                        @else Caminhonete
                                        @endif
                                    </span>
                                </div>
                                
                                {{-- Serviços --}}
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($order->products as $item)
                                        <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">
                                            {{ $item->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            
                            {{-- Valor e Botão --}}
                            <div class="text-right">
                                <div class="text-2xl font-bold text-green-600 mb-2">
                                    R$ {{ number_format($order->total, 2, ',', '.') }}
                                </div>
                                <a href="{{ route('operator.orders.deliver-form', $order) }}" 
                                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition font-medium">
                                    <i class="fas fa-key"></i> Entregar Veículo
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="mt-6 pt-4 border-t text-center">
                    <p class="text-sm text-gray-500">
                        Total: <span class="font-bold">{{ $finishedOrders->count() }}</span> serviço(s) aguardando entrega
                    </p>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700 mb-1">Nenhum serviço pendente</h3>
                    <p class="text-gray-400">Todos os serviços finalizados já foram entregues</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection