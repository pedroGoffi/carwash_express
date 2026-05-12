@extends('layouts.app')

@section('title', 'Folha de Pagamento')
@section('header', 'Folha de Pagamento')
@section('subheader', 'Calcule os salários e descontos dos funcionários')

@section('content')
<div class="space-y-6">
    
    {{-- Cards de Resumo --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl shadow-lg p-5 text-white">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-calculator text-2xl text-white/80"></i>
                <p class="text-white/80 text-sm">Total de Salários</p>
            </div>
            <p class="text-3xl font-bold">R$ {{ number_format($totalSalary, 2, ',', '.') }}</p>
        </div>
        
        <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-2xl shadow-lg p-5 text-white">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-hand-holding-usd text-2xl text-white/80"></i>
                <p class="text-white/80 text-sm">Total em Vales</p>
            </div>
            <p class="text-3xl font-bold">R$ {{ number_format($totalAdvances, 2, ',', '.') }}</p>
        </div>
        
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-2xl shadow-lg p-5 text-white">
            <div class="flex items-center gap-3 mb-3">
                <i class="fas fa-money-bill-wave text-2xl text-white/80"></i>
                <p class="text-white/80 text-sm">Total a Pagar</p>
            </div>
            <p class="text-3xl font-bold">R$ {{ number_format($totalToReceive, 2, ',', '.') }}</p>
        </div>
    </div>

    {{-- Tabela de Funcionários --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800">
                <i class="fas fa-users text-blue-500 mr-2"></i> Cálculo de Salários
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
    <tr>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Funcionário</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Salário</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dias/Mês</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Vales (R$)</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Consumo (R$)</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Descontos</th>
        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">A Receber</th>
        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
    </tr>
</thead>
<tbody class="divide-y divide-gray-100">
    @foreach($workers as $worker)
    <tr>
        <td class="px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                </div>
                <div>
                    <p class="font-semibold">{{ $worker->name }}</p>
                    <p class="text-xs text-gray-400">Admitido: {{ $worker->hired_at ? $worker->hired_at->format('d/m/Y') : '-' }}</p>
                </div>
            </div>
        </td>
        <td class="px-6 py-4 text-right">
            <span class="font-semibold">R$ {{ number_format($worker->monthly_salary, 2, ',', '.') }}</span>
            @if($worker->daily_rate > 0)
                <p class="text-xs text-gray-400">(R$ {{ number_format($worker->daily_rate, 2, ',', '.') }}/dia)</p>
            @endif
        </td>
        <td class="px-6 py-4 text-right">
            @if($worker->daily_rate > 0)
                <span class="font-medium">{{ $worker->days_worked }} dias</span>
            @else
                <span class="text-gray-400">Mensal</span>
            @endif
        </td>
        <td class="px-6 py-4 text-right text-red-600">
            - R$ {{ number_format($worker->total_advances, 2, ',', '.') }}
        </td>
        <td class="px-6 py-4 text-right text-orange-600">
            - R$ {{ number_format($worker->total_consumptions, 2, ',', '.') }}
        </td>
        <td class="px-6 py-4 text-right text-red-600 font-semibold">
            - R$ {{ number_format($worker->total_advances + $worker->total_consumptions, 2, ',', '.') }}
        </td>
        <td class="px-6 py-4 text-right text-green-600 font-bold text-lg">
            R$ {{ number_format($worker->total_to_receive, 2, ',', '.') }}
        </td>
        <td class="px-6 py-4 text-center">
            <button onclick="openSalaryModal({{ $worker->id }}, '{{ $worker->name }}', {{ $worker->monthly_salary }}, {{ $worker->daily_rate }}, {{ $worker->days_worked }})" 
                    class="text-blue-600 hover:text-blue-800">
                <i class="fas fa-edit"></i>
            </button>
        </td>
    </tr>
    @endforeach
</tbody>
<tfoot class="bg-gray-50 font-bold">
    <tr>
        <td class="px-6 py-4">TOTAL</td>
        <td class="px-6 py-4 text-right">R$ {{ number_format($totalSalary, 2, ',', '.') }}</td>
        <td class="px-6 py-4 text-right">-</td>
        <td class="px-6 py-4 text-right text-red-600">- R$ {{ number_format($totalAdvances, 2, ',', '.') }}</td>
        <td class="px-6 py-4 text-right text-orange-600">- R$ {{ number_format($totalConsumptions, 2, ',', '.') }}</td>
        <td class="px-6 py-4 text-right text-red-600">- R$ {{ number_format($totalAdvances + $totalConsumptions, 2, ',', '.') }}</td>
        <td class="px-6 py-4 text-right text-green-600">R$ {{ number_format($totalToReceive, 2, ',', '.') }}</td>
        <td class="px-6 py-4 text-center">-</td>
    </tr>
</tfoot>                
            </table>
        </div>
    </div>
</div>

{{-- Modal Editar Salário --}}
<div id="salaryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-800">Editar Salário</h3>
            <button onclick="closeSalaryModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <form id="salaryForm" method="POST">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Salário Mensal</label>
                    <input type="number" name="monthly_salary" id="monthly_salary" step="0.01" class="w-full border rounded-lg px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">Deixe 0 se for diarista</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor por Dia (Diarista)</label>
                    <input type="number" name="daily_rate" id="daily_rate" step="0.01" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dias Trabalhados no Mês</label>
                    <input type="number" name="days_worked" id="days_worked" min="0" max="31" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div class="flex justify-end gap-3 pt-3">
                    <button type="button" onclick="closeSalaryModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Salvar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function openSalaryModal(id, name, monthlySalary, dailyRate, daysWorked) {
        document.getElementById('salaryForm').action = `/manager/payroll/${id}`;
        document.getElementById('monthly_salary').value = monthlySalary;
        document.getElementById('daily_rate').value = dailyRate;
        document.getElementById('days_worked').value = daysWorked;
        document.getElementById('salaryModal').classList.remove('hidden');
    }

    function closeSalaryModal() {
        document.getElementById('salaryModal').classList.add('hidden');
    }
</script>
@endsection