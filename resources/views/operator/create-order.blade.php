@extends('layouts.app')

@section('title', 'Nova Ordem')
@section('header', 'Nova Ordem de Serviço')
@section('subheader', 'Preencha os dados abaixo para criar uma nova ordem')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        
        {{-- Header com gradiente --}}
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-white text-xl"></i>
                </div>
                <div>
                    <h2 class="text-white text-lg font-semibold">Nova Ordem de Serviço</h2>
                    <p class="text-blue-100 text-sm">Informe os dados do cliente e selecione os serviços</p>
                </div>
            </div>
        </div>

        <form action="{{ route('operator.orders.store') }}" method="POST" id="orderForm">
            @csrf
            
            <div class="p-6 space-y-8">
                
                {{-- ========== SEÇÃO 1: DADOS DO CLIENTE ========== --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <h3 class="text-md font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-user-circle text-blue-500 text-lg"></i>
                        Dados do Cliente
                    </h3>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Tipo de Cliente --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Cliente</label>
                            <div class="flex gap-3">
                                <button type="button" id="btn-pf" onclick="setCustomerType('pf')" 
                                        class="flex-1 py-2.5 rounded-lg font-medium transition-all duration-200
                                               bg-blue-600 text-white shadow-md">
                                    <i class="fas fa-user mr-2"></i> Pessoa Física
                                </button>
                                <button type="button" id="btn-pj" onclick="setCustomerType('pj')" 
                                        class="flex-1 py-2.5 rounded-lg font-medium transition-all duration-200
                                               bg-gray-100 text-gray-600 hover:bg-gray-200">
                                    <i class="fas fa-building mr-2"></i> Pessoa Jurídica
                                </button>
                            </div>
                            <input type="hidden" name="customer_type" id="customer_type" value="pf">
                        </div>
                        
                        {{-- Placa do Veículo --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-car text-blue-500 mr-1"></i> Placa do Veículo <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <i class="fas fa-id-card absolute left-3 top-3 text-gray-400"></i>
                                <input type="text" name="license_plate" required
                                       class="w-full pl-10 pr-3 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase" 
                                       placeholder="ABC-1234">
                            </div>
                            <p class="text-xs text-gray-400 mt-1">Obrigatório. Ex: ABC-1234 ou ABC1D23</p>
                        </div>
                    </div>
                    
                    {{-- Campos Pessoa Física --}}
                    <div id="pf-fields" class="mt-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-blue-500 mr-1"></i> Nome do Cliente
                        </label>
                        <div class="relative">
                            <i class="fas fa-user absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" name="customer_name" id="customer_name"
                                   class="w-full pl-10 pr-3 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                   placeholder="Digite o nome do cliente">
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Opcional</p>
                    </div>
                    
                    {{-- Campos Pessoa Jurídica --}}
                    <div id="pj-fields" class="hidden space-y-4 mt-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-building text-blue-500 mr-1"></i> Razão Social
                            </label>
                            <input type="text" name="company_name" id="company_name"
                                   class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                   placeholder="Nome da empresa">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-id-card text-blue-500 mr-1"></i> CNPJ
                                </label>
                                <input type="text" name="company_document" id="company_document"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="00.000.000/0001-00">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-user-tie text-blue-500 mr-1"></i> Nome do Contato
                                </label>
                                <input type="text" name="contact_name" id="contact_name"
                                       class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="Nome do responsável">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ========== SEÇÃO 2: TIPO DO VEÍCULO ========== --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <h3 class="text-md font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-truck-pickup text-blue-500 text-lg"></i>
                        Tipo do Veículo
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <button type="button" onclick="selectType('normal', this)" 
                                class="type-btn group p-5 rounded-xl transition-all duration-200
                                       bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg">
                            <div class="flex flex-col items-center gap-2">
                                <img src="{{ asset('images/vehicles/car.png') }}" alt="Carro" class="w-24 h-24 object-contain">
                                <span class="font-bold text-lg">Carro</span>
                                <span class="text-xs opacity-80">Preço base</span>
                            </div>
                        </button>
                        
                        <button type="button" onclick="selectType('suv', this)" 
                                class="type-btn group p-5 rounded-xl transition-all duration-200
                                       bg-white border-2 border-gray-200 text-gray-700 hover:border-green-400 hover:shadow-md">
                            <div class="flex flex-col items-center gap-2">
                                <img src="{{ asset('images/vehicles/suv.png') }}" alt="SUV" class="w-24 h-24 object-contain grayscale group-hover:grayscale-0 transition">
                                <span class="font-bold text-lg">SUV / Picape</span>
                                <span class="text-xs text-green-600">Preço diferenciado</span>
                            </div>
                        </button>
                        
                        <button type="button" onclick="selectType('pickup', this)" 
                                class="type-btn group p-5 rounded-xl transition-all duration-200
                                       bg-white border-2 border-gray-200 text-gray-700 hover:border-orange-400 hover:shadow-md">
                            <div class="flex flex-col items-center gap-2">
                                <img src="{{ asset('images/vehicles/pickup.png') }}" alt="Caminhonete" class="w-24 h-24 object-contain">
                                <span class="font-bold text-lg">Caminhonete</span>
                                <span class="text-xs text-orange-600">Preço + alto</span>
                            </div>
                        </button>
                    </div>
                    <input type="hidden" name="vehicle_type" id="vehicle_type" value="normal">
                </div>

                {{-- ========== SEÇÃO 3: SERVIÇOS ========== --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-md font-semibold text-gray-700 flex items-center gap-2">
                            <i class="fas fa-soap text-blue-500 text-lg"></i>
                            Serviços
                        </h3>
                        <span class="text-xs text-gray-400 bg-white px-2 py-1 rounded-full">Selecione os serviços desejados</span>
                    </div>
                    
                    {{-- Lavagens --}}
                    <div class="mb-6">
                        <label class="text-sm font-semibold text-gray-600 mb-3 block">
                            <i class="fas fa-car-wash text-blue-500 mr-1"></i> Lavagens
                            <span class="text-xs text-red-500 ml-1">* obrigatório</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($washes as $wash)
                            <label class="wash-card group relative flex flex-col items-center p-4 rounded-xl cursor-pointer transition-all duration-200
                                          bg-white border-2 border-gray-200 hover:border-blue-400 hover:shadow-lg"
                                   data-product-id="{{ $wash->id }}">
                                <div class="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mb-3 group-hover:bg-blue-100 transition">
                                    <i class="fas fa-car-wash text-blue-500 text-3xl"></i>
                                </div>
                                <span class="font-semibold text-gray-800 text-center">{{ $wash->name }}</span>
                                <span class="product-price text-xl font-bold text-green-600 mt-2">
                                    R$ {{ number_format($wash->getPriceForVehicleType('normal'), 2, ',', '.') }}
                                </span>
                                <div class="absolute top-3 right-3">
                                    <input type="checkbox" name="products[]" value="{{ $wash->id }}" 
                                           class="product-checkbox w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    
                    {{-- Produtos Adicionais --}}
                    <div>
                        <label class="text-sm font-semibold text-gray-600 mb-3 block">
                            <i class="fas fa-cart-shopping text-green-500 mr-1"></i> Produtos Adicionais
                            <span class="text-xs text-gray-400 ml-1">(opcional)</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                            @foreach($products as $product)
                            <label class="product-card group relative flex flex-col items-center p-3 rounded-xl cursor-pointer transition-all duration-200
                                          bg-white border-2 border-gray-200 hover:border-green-400 hover:shadow-lg"
                                   data-product-id="{{ $product->id }}">
                                <div class="w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-2 group-hover:bg-green-100 transition">
                                    <i class="fas fa-bottle-water text-green-500 text-2xl"></i>
                                </div>
                                <span class="font-semibold text-gray-800 text-center text-sm">{{ $product->name }}</span>
                                <span class="product-price text-md font-bold text-green-600 mt-1">
                                    R$ {{ number_format($product->getPriceForVehicleType('normal'), 2, ',', '.') }}
                                </span>
                                <div class="absolute top-2 right-2">
                                    <input type="checkbox" name="products[]" value="{{ $product->id }}" 
                                           class="product-checkbox w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500">
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- ========== SEÇÃO 4: TOTAL ========== --}}
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-5 border border-blue-200">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-calculator text-white text-2xl"></i>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Total do serviço</span>
                                <p class="text-xs text-gray-400">Valor já com acréscimo do tipo de veículo</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span id="totalAmount" class="text-4xl font-bold text-blue-600">R$ 0,00</span>
                        </div>
                    </div>
                </div>

                {{-- ========== SEÇÃO 5: PAGAMENTO ========== --}}
                <div class="bg-gray-50 rounded-xl p-5">
                    <h3 class="text-md font-semibold text-gray-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-credit-card text-purple-500 text-lg"></i>
                        Pagamento
                        <span class="text-xs text-gray-400 bg-white px-2 py-0.5 rounded-full">Split payment</span>
                    </h3>
                    
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-500 mb-2">Adicionar pagamento</label>
                        <div class="flex flex-wrap gap-3">
                            <button type="button" onclick="addPaymentMethod('Dinheiro')" 
                                    class="payment-btn px-5 py-2.5 bg-white border-2 border-gray-200 rounded-lg text-sm hover:border-green-400 hover:shadow-md transition flex items-center gap-2">
                                <i class="fas fa-money-bill-wave text-green-600 text-lg"></i> Dinheiro
                            </button>
                            <button type="button" onclick="addPaymentMethod('Pix')" 
                                    class="payment-btn px-5 py-2.5 bg-white border-2 border-gray-200 rounded-lg text-sm hover:border-blue-400 hover:shadow-md transition flex items-center gap-2">
                                <i class="fas fa-qrcode text-blue-600 text-lg"></i> Pix
                            </button>
                            <button type="button" onclick="addPaymentMethod('Crédito')" 
                                    class="payment-btn px-5 py-2.5 bg-white border-2 border-gray-200 rounded-lg text-sm hover:border-purple-400 hover:shadow-md transition flex items-center gap-2">
                                <i class="fas fa-credit-card text-purple-600 text-lg"></i> Crédito
                            </button>
                            <button type="button" onclick="addPaymentMethod('Débito')" 
                                    class="payment-btn px-5 py-2.5 bg-white border-2 border-gray-200 rounded-lg text-sm hover:border-purple-400 hover:shadow-md transition flex items-center gap-2">
                                <i class="fas fa-credit-card text-purple-400 text-lg"></i> Débito
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-2">Pagamentos registrados</label>
                        <div id="payments-list" class="space-y-2 min-h-[80px]">
                            <div class="text-center py-6 text-gray-400 text-sm bg-white rounded-lg border-2 border-dashed border-gray-200" id="no-payments-msg">
                                <i class="fas fa-plus-circle mr-1"></i> Nenhum pagamento adicionado
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 pt-3 border-t border-gray-200">
                        <div class="flex justify-between text-sm py-1">
                            <span class="text-gray-600">Total do serviço:</span>
                            <span class="font-bold" id="display-total">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between text-sm py-1">
                            <span class="text-gray-600">Total pago:</span>
                            <span class="font-bold text-green-600" id="total-paid">R$ 0,00</span>
                        </div>
                        <div class="flex justify-between text-sm py-1 pt-2 border-t border-gray-200">
                            <span class="text-gray-600">Restante:</span>
                            <span class="font-bold" id="remaining-amount">R$ 0,00</span>
                        </div>
                    </div>
                    
                    <input type="hidden" name="payments" id="payments-input" value="">
                </div>

                {{-- ========== BOTÕES ========== --}}
                <div class="flex justify-end gap-3 pt-4 border-t">
                    <a href="{{ route('operator.orders.create') }}" 
                       class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2 font-medium">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition flex items-center gap-2 font-medium shadow-md">
                        <i class="fas fa-save"></i> Criar Ordem de Serviço
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    let currentType = 'normal';
    
    // Pré-carregar os preços dos produtos
    const productPrices = {};
    
    @foreach($washes as $wash)
    productPrices[{{ $wash->id }}] = {
        normal: {{ $wash->getPriceForVehicleType('normal') }},
        suv: {{ $wash->getPriceForVehicleType('suv') }},
        pickup: {{ $wash->getPriceForVehicleType('pickup') }}
    };
    @endforeach
    
    @foreach($products as $product)
    productPrices[{{ $product->id }}] = {
        normal: {{ $product->getPriceForVehicleType('normal') }},
        suv: {{ $product->getPriceForVehicleType('suv') }},
        pickup: {{ $product->getPriceForVehicleType('pickup') }}
    };
    @endforeach
    
    function selectType(type, btn) {
        currentType = type;
        document.getElementById('vehicle_type').value = type;
        
        document.querySelectorAll('.type-btn').forEach(button => {
            button.classList.remove('bg-gradient-to-br', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-lg');
            button.classList.add('bg-white', 'border-2', 'border-gray-200', 'text-gray-700');
            const img = button.querySelector('img');
            if (img) img.classList.add('grayscale');
        });
        
        btn.classList.remove('bg-white', 'border-2', 'border-gray-200', 'text-gray-700');
        btn.classList.add('bg-gradient-to-br', 'from-blue-500', 'to-blue-600', 'text-white', 'shadow-lg');
        const btnImg = btn.querySelector('img');
        if (btnImg) btnImg.classList.remove('grayscale');
        
        updatePrices();
    }
    
    function updatePrices() {
        document.querySelectorAll('.product-checkbox').forEach(checkbox => {
            const productId = checkbox.value;
            const price = productPrices[productId] ? productPrices[productId][currentType] : 0;
            const priceSpan = checkbox.closest('label').querySelector('.product-price');
            if (priceSpan) {
                priceSpan.textContent = 'R$ ' + price.toFixed(2).replace('.', ',');
            }
            checkbox.dataset.currentPrice = price;
        });
        calculateTotal();
    }
    
    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
            const price = parseFloat(checkbox.dataset.currentPrice) || 0;
            total += price;
        });
        const totalElement = document.getElementById('totalAmount');
        if (totalElement) {
            totalElement.textContent = 'R$ ' + total.toFixed(2).replace('.', ',');
        }
    }
    
    document.querySelectorAll('.product-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('label');
            if (this.checked) {
                card.classList.add('border-blue-400', 'bg-blue-50');
            } else {
                card.classList.remove('border-blue-400', 'bg-blue-50');
            }
            calculateTotal();
        });
    });
    
    // ========== SPLIT PAYMENT ==========
    let payments = [];
    let orderTotal = 0;

    function addPaymentMethod(method) {
        const remaining = orderTotal - getTotalPaid();
        if (remaining <= 0) {
            alert('O valor total já foi pago!');
            return;
        }
        
        let amount = prompt(`Valor em ${method}:`, remaining.toFixed(2).replace('.', ','));
        if (amount === null) return;
        
        amount = parseFloat(amount.replace(',', '.'));
        if (isNaN(amount) || amount <= 0) {
            alert('Valor inválido');
            return;
        }
        
        if (amount > remaining) {
            alert(`Valor excede o restante de R$ ${remaining.toFixed(2).replace('.', ',')}`);
            return;
        }
        
        payments.push({ method: method, amount: amount });
        updatePaymentsList();
    }

    function removePayment(index) {
        payments.splice(index, 1);
        updatePaymentsList();
    }

    function updatePaymentsList() {
        const container = document.getElementById('payments-list');
        const totalPaid = getTotalPaid();
        
        if (payments.length === 0) {
            container.innerHTML = '<div class="text-center py-6 text-gray-400 text-sm bg-white rounded-lg border-2 border-dashed border-gray-200" id="no-payments-msg"><i class="fas fa-plus-circle mr-1"></i> Nenhum pagamento adicionado</div>';
        } else {
            container.innerHTML = '';
            payments.forEach((payment, index) => {
                const div = document.createElement('div');
                div.className = 'flex justify-between items-center p-3 bg-white rounded-lg border border-gray-200';
                div.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full ${payment.method === 'Dinheiro' ? 'bg-green-100' : payment.method === 'Pix' ? 'bg-blue-100' : 'bg-purple-100'} flex items-center justify-center">
                            <i class="fas ${payment.method === 'Dinheiro' ? 'fa-money-bill-wave' : payment.method === 'Pix' ? 'fa-qrcode' : 'fa-credit-card'} ${payment.method === 'Dinheiro' ? 'text-green-600' : payment.method === 'Pix' ? 'text-blue-600' : 'text-purple-600'}"></i>
                        </div>
                        <span class="font-medium">${payment.method}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-bold text-green-600">R$ ${payment.amount.toFixed(2).replace('.', ',')}</span>
                        <button type="button" onclick="removePayment(${index})" class="text-red-500 hover:text-red-700">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                `;
                container.appendChild(div);
            });
        }
        
        document.getElementById('total-paid').textContent = 'R$ ' + totalPaid.toFixed(2).replace('.', ',');
        const remaining = orderTotal - totalPaid;
        document.getElementById('remaining-amount').textContent = 'R$ ' + remaining.toFixed(2).replace('.', ',');
        
        const remainingSpan = document.getElementById('remaining-amount');
        remainingSpan.className = remaining > 0 ? 'font-bold text-red-600' : 'font-bold text-green-600';
        
        document.getElementById('payments-input').value = JSON.stringify(payments);
    }

    function getTotalPaid() {
        return payments.reduce((sum, p) => sum + p.amount, 0);
    }

    const originalCalculateTotal = calculateTotal;
    calculateTotal = function() {
        originalCalculateTotal();
        orderTotal = 0;
        document.querySelectorAll('.product-checkbox:checked').forEach(checkbox => {
            const price = parseFloat(checkbox.dataset.currentPrice) || 0;
            orderTotal += price;
        });
        document.getElementById('display-total').textContent = 'R$ ' + orderTotal.toFixed(2).replace('.', ',');
        updatePaymentsList();
    };

    function setCustomerType(type) {
        document.getElementById('customer_type').value = type;
        
        const pfBtn = document.getElementById('btn-pf');
        const pjBtn = document.getElementById('btn-pj');
        const pfFields = document.getElementById('pf-fields');
        const pjFields = document.getElementById('pj-fields');
        
        if (type === 'pf') {
            pfBtn.classList.remove('bg-gray-100', 'text-gray-600');
            pfBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md');
            pjBtn.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
            pjBtn.classList.add('bg-gray-100', 'text-gray-600');
            pfFields.classList.remove('hidden');
            pjFields.classList.add('hidden');
            
            document.getElementById('company_name').value = '';
            document.getElementById('company_document').value = '';
            document.getElementById('contact_name').value = '';
        } else {
            pjBtn.classList.remove('bg-gray-100', 'text-gray-600');
            pjBtn.classList.add('bg-blue-600', 'text-white', 'shadow-md');
            pfBtn.classList.remove('bg-blue-600', 'text-white', 'shadow-md');
            pfBtn.classList.add('bg-gray-100', 'text-gray-600');
            pjFields.classList.remove('hidden');
            pfFields.classList.add('hidden');
            
            document.getElementById('customer_name').value = '';
        }
    }
    
    updatePrices();
</script>

<style>
    .type-btn {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .type-btn:hover {
        transform: translateY(-4px);
    }
    .wash-card, .product-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .wash-card:hover, .product-card:hover {
        transform: translateY(-4px);
    }
    .grayscale {
        filter: grayscale(100%);
        opacity: 0.6;
    }
    .payment-btn {
        transition: all 0.2s ease;
    }
    .payment-btn:hover {
        transform: translateY(-2px);
    }
</style>
@endsection