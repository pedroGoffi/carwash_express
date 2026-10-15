@extends('layouts.app')

@section('title', 'Nova ordem')
@section('header', 'Nova ordem')
@section('subheader', 'Venda rápida · serviços, produtos e pagamento em uma tela')

@section('content')
@php
    $selectedItems = old('items', []);
    $customerType = old('customer_type', 'pf');
    $vehicleType = old('vehicle_type', 'normal');
    $initialStatus = old('initial_status', 'finished');
    $catalog = $washes->concat($products)->mapWithKeys(fn ($p) => [$p->id => [
        'name' => $p->name,
        'type' => $p->type->value,
        'normal' => round($p->getPriceForVehicleType('normal') * 100),
        'suv' => round($p->getPriceForVehicleType('suv') * 100),
        'pickup' => round($p->getPriceForVehicleType('pickup') * 100),
    ]]);
@endphp
<style>
    /* ERP Order Composer: estilos locais para evitar dependências adicionais e flashes visuais */
    .erp-order { --line:#e5eaf1; --muted:#64748b; --ink:#17253b; --primary:#2563eb; color:var(--ink); }
    .erp-order * { box-sizing:border-box; }
    .erp-order [hidden] { display:none!important; }
    .erp-order .erp-panel { border:1px solid var(--line); border-radius:16px; background:#fff; box-shadow:0 2px 12px rgba(32,48,75,.035); }
    .erp-order .erp-panel-head { padding:18px 20px 14px; border-bottom:1px solid #f0f3f7; display:flex; align-items:center; gap:11px; }
    .erp-order .erp-step { display:grid; place-items:center; width:28px; height:28px; border-radius:9px; background:#eff6ff; color:#1d4ed8; font-weight:800; font-size:12px; flex-shrink:0; }
    .erp-order .erp-section-title { font-weight:800; font-size:14px; letter-spacing:-.01em; }
    .erp-order .erp-hint { font-size:12px; color:var(--muted); }
    .erp-order .erp-input { width:100%; min-width:0; border:1px solid #dce4ef; background:#fff; border-radius:10px; padding:10px 12px; font-size:14px; min-height:42px; color:#0f172a; outline:none; transition:border-color .12s, box-shadow .12s; }
    .erp-order .erp-input:focus { border-color:#3b82f6; box-shadow:0 0 0 3px #dbeafe; }
    .erp-order .erp-label { display:block; font-size:12px; color:#475569; font-weight:700; margin-bottom:5px; }
    .erp-order .erp-choice { cursor:pointer; user-select:none; display:flex; align-items:center; justify-content:center; gap:8px; padding:11px 12px; min-height:42px; border:1px solid #dce4ef; border-radius:10px; color:#475569; background:#fff; font-weight:700; font-size:13px; transition:background .12s,border-color .12s,color .12s; }
    .erp-order .erp-choice:focus-visible,.erp-order .erp-product:focus-visible,.erp-order .erp-wash:focus-visible,.erp-order button:focus-visible { outline:3px solid #bfdbfe; outline-offset:2px; }
    .erp-order .erp-choice[data-active="true"] { background:#eff6ff; border-color:#60a5fa; color:#1d4ed8; }
    .erp-order .erp-wash { display:flex; width:100%; text-align:left; gap:12px; align-items:center; border:1px solid #e2e8f0; border-radius:12px; padding:12px 14px; background:#fff; transition:background .12s,border-color .12s; cursor:pointer; }
    .erp-order .erp-wash[aria-pressed="true"] { background:#eff6ff; border-color:#3b82f6; box-shadow:inset 0 0 0 1px #3b82f6; }
    .erp-order .erp-wash .wash-indicator { display:grid; place-items:center; border:1px solid #cbd5e1; border-radius:50%; height:19px; width:19px; color:transparent; flex-shrink:0; }
    .erp-order .erp-wash[aria-pressed="true"] .wash-indicator { border-color:#2563eb; background:#2563eb; color:white; }
    .erp-order .erp-product { border:1px solid #e2e8f0; background:#fff; border-radius:12px; padding:14px; display:flex; justify-content:space-between; align-items:center; gap:12px; transition:border-color .12s,background .12s; }
    .erp-order .erp-product[data-selected="true"] { border-color:#93c5fd; background:#f8fbff; }
    .erp-order .erp-qty { display:flex; align-items:center; gap:0; border:1px solid #dbe3ef; border-radius:10px; overflow:hidden; background:white; height:36px; flex-shrink:0; }
    .erp-order .erp-qty button { width:35px; height:35px; display:grid; place-items:center; color:#2563eb; font-weight:800; font-size:17px; }
    .erp-order .erp-qty button:hover:not(:disabled) { background:#eff6ff; }
    .erp-order .erp-qty button:disabled { color:#cbd5e1; cursor:not-allowed; }
    .erp-order .erp-qty output { min-width:30px; text-align:center; font-variant-numeric:tabular-nums; font-size:14px; font-weight:800; }
    .erp-order .erp-summary-line { display:flex; justify-content:space-between; align-items:baseline; gap:12px; padding:8px 0; font-size:13px; }
    .erp-order .erp-small-button { background:white; border:1px solid #dbe3ef; border-radius:9px; font-size:12px; font-weight:700; padding:8px 11px; color:#334155; transition:background .12s; }
    .erp-order .erp-small-button:hover { background:#f1f5f9; }
    .erp-order .erp-payment-row { display:grid; grid-template-columns:1fr minmax(100px,140px) 34px; gap:8px; align-items:center; }
    .erp-order .erp-error { border:1px solid #fecaca; border-radius:12px; padding:12px 15px; background:#fef2f2; color:#991b1b; font-size:13px; }
    .erp-order .erp-save { border-radius:11px; background:#2563eb; color:white; padding:13px 18px; font-size:14px; font-weight:800; min-height:46px; display:inline-flex; align-items:center; justify-content:center; gap:9px; transition:background .14s; }
    .erp-order .erp-save:hover:not(:disabled) { background:#1d4ed8; }
    .erp-order .erp-save:disabled { opacity:.55; cursor:not-allowed; }
    .erp-order .erp-filter[aria-pressed="true"] { background:#dbeafe; color:#1d4ed8; border-color:#93c5fd; }
    .erp-order .erp-invalid { border-color:#ef4444 !important; }
    @media(max-width:640px) { .erp-order .erp-panel-head { padding:15px; } .erp-order .erp-product { padding:11px; } .erp-order .erp-payment-row { grid-template-columns:1fr minmax(83px,110px) 30px; } }
    @media(prefers-reduced-motion:reduce) { .erp-order * { transition:none!important; scroll-behavior:auto!important; } }
</style>

<div class="erp-order mx-auto max-w-[1500px]" id="erp-order">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Operação / Ordens de serviço</p>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 mt-1">Registrar atendimento</h2>
            <p class="text-sm text-slate-500 mt-1">Preencha o essencial, escolha a lavagem e adicione produtos com um toque.</p>
        </div>
        <a href="{{ route('operator.orders.index') }}" class="erp-small-button inline-flex items-center gap-2"><i class="fas fa-list"></i> Consultar ordens</a>
    </div>

    @if($errors->any())
        <div class="erp-error mb-5" role="alert">
            <strong>Confira as informações antes de salvar:</strong>
            <ul class="list-disc ml-5 mt-2 space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div id="order-notice" class="erp-error mb-5 hidden" role="alert" aria-live="polite"></div>

    <form action="{{ route('operator.orders.store') }}" method="POST" id="orderForm" novalidate>
        @csrf
        <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_355px] gap-5 items-start">
            <div class="space-y-5 min-w-0">
                <section class="erp-panel" aria-labelledby="title-client">
                    <div class="erp-panel-head"><span class="erp-step">01</span><div><h3 class="erp-section-title" id="title-client">Cliente e veículo</h3><p class="erp-hint">Placa obrigatória · identificação do cliente opcional</p></div></div>
                    <div class="p-4 sm:p-5 space-y-4">
                        <div class="grid sm:grid-cols-[minmax(0,1fr)_minmax(180px,.75fr)] gap-4">
                            <div>
                                <label class="erp-label" for="license_plate">Placa do veículo <span class="text-red-600">*</span></label>
                                <input id="license_plate" name="license_plate" type="text" maxlength="20" value="{{ old('license_plate') }}" placeholder="ABC1D23" class="erp-input font-bold uppercase tracking-wider" required autocomplete="off" autofocus>
                            </div>
                            <div>
                                <span class="erp-label">Tipo de cliente</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" data-customer="pf" class="erp-choice" aria-pressed="false"><i class="far fa-user"></i> Pessoa física</button>
                                    <button type="button" data-customer="pj" class="erp-choice" aria-pressed="false"><i class="far fa-building"></i> Empresa</button>
                                </div>
                                <input type="hidden" name="customer_type" id="customer_type" value="{{ $customerType }}">
                            </div>
                        </div>
                        <div id="pf-fields"><label class="erp-label" for="customer_name">Nome do cliente <span class="font-normal text-slate-400">(opcional)</span></label><input class="erp-input" id="customer_name" name="customer_name" maxlength="255" value="{{ old('customer_name') }}" placeholder="Nome para identificação"></div>
                        <div id="pj-fields" class="grid sm:grid-cols-3 gap-3" hidden>
                            <div><label class="erp-label" for="company_name">Razão social</label><input class="erp-input" id="company_name" name="company_name" value="{{ old('company_name') }}" maxlength="255" placeholder="Nome da empresa"></div>
                            <div><label class="erp-label" for="company_document">CNPJ</label><input class="erp-input" id="company_document" name="company_document" value="{{ old('company_document') }}" maxlength="18" placeholder="00.000.000/0000-00"></div>
                            <div><label class="erp-label" for="contact_name">Contato</label><input class="erp-input" id="contact_name" name="contact_name" value="{{ old('contact_name') }}" maxlength="255" placeholder="Responsável"></div>
                        </div>
                        <div>
                            <span class="erp-label">Categoria do veículo</span>
                            <div class="grid grid-cols-3 gap-2" id="vehicle-options">
                                <button type="button" class="erp-choice" data-vehicle="normal" aria-pressed="false"><i class="fas fa-car-side text-slate-400"></i> Carro</button>
                                <button type="button" class="erp-choice" data-vehicle="suv" aria-pressed="false"><i class="fas fa-car text-slate-400"></i> SUV</button>
                                <button type="button" class="erp-choice" data-vehicle="pickup" aria-pressed="false"><i class="fas fa-truck-pickup text-slate-400"></i> Picape</button>
                            </div>
                            <input type="hidden" id="vehicle_type" name="vehicle_type" value="{{ $vehicleType }}">
                        </div>
                    </div>
                </section>

                <section class="erp-panel" aria-labelledby="title-washes">
                    <div class="erp-panel-head"><span class="erp-step">02</span><div class="flex-1"><h3 class="erp-section-title" id="title-washes">Lavagem</h3><p class="erp-hint">Escolha um serviço principal</p></div><span class="text-xs font-semibold text-slate-400">1 serviço</span></div>
                    <div class="p-4 sm:p-5 grid sm:grid-cols-2 gap-3" id="wash-options">
                        @forelse($washes as $wash)
                        <button type="button" data-wash="{{ $wash->id }}" class="erp-wash" aria-pressed="false">
                            <span class="wash-indicator"><i class="fas fa-check text-[10px]"></i></span>
                            <span class="flex-1 min-w-0"><span class="block font-bold text-sm text-slate-800 truncate">{{ $wash->name }}</span><span class="block text-xs text-slate-500 mt-1">Lavagem</span></span>
                            <span class="font-extrabold text-blue-700 text-sm whitespace-nowrap" data-price-for="{{ $wash->id }}">—</span>
                        </button>
                        @empty
                        <p class="text-sm text-amber-700 sm:col-span-2">Nenhum serviço de lavagem ativo. Cadastre um produto do tipo lavagem antes de criar ordens.</p>
                        @endforelse
                    </div>
                </section>

                <section class="erp-panel" aria-labelledby="title-products">
                    <div class="erp-panel-head"><span class="erp-step">03</span><div class="flex-1"><h3 class="erp-section-title" id="title-products">Bebidas, lanches e adicionais</h3><p class="erp-hint">Aumente a quantidade pelo botão + · até 100 unidades por item</p></div><span class="hidden sm:inline text-xs font-semibold text-slate-400">Opcional</span></div>
                    <div class="p-4 sm:p-5">
                        <div class="flex flex-wrap gap-2 mb-4">
                            <div class="relative flex-1 min-w-[180px]"><i class="fas fa-magnifying-glass absolute left-3 top-3.5 text-slate-400 text-sm"></i><input id="product-search" type="search" autocomplete="off" class="erp-input !pl-9" placeholder="Buscar bebida ou produto..." aria-label="Buscar produto"></div>
                            <button type="button" class="erp-small-button erp-filter" data-filter="all" aria-pressed="true">Todos</button>
                            <button type="button" class="erp-small-button erp-filter" data-filter="drink" aria-pressed="false">Bebidas</button>
                            <button type="button" class="erp-small-button erp-filter" data-filter="snack" aria-pressed="false">Lanches</button>
                        </div>
                        <div id="product-grid" class="grid sm:grid-cols-2 gap-3">
                            @forelse($products as $product)
                            <div class="erp-product" data-product="{{ $product->id }}" data-category="{{ $product->type->value }}" data-name="{{ mb_strtolower($product->name) }}" data-selected="false">
                                <div class="min-w-0 flex-1"><p class="font-bold text-sm text-slate-800 break-words">{{ $product->name }}</p><p class="text-xs text-slate-500 mt-1"><span data-price-for="{{ $product->id }}">—</span> / un.</p></div>
                                <div class="erp-qty" aria-label="Quantidade de {{ $product->name }}"><button type="button" data-dec="{{ $product->id }}" aria-label="Diminuir {{ $product->name }}" disabled>−</button><output id="qty-{{ $product->id }}" aria-live="polite">0</output><button type="button" data-inc="{{ $product->id }}" aria-label="Adicionar {{ $product->name }}">+</button></div>
                            </div>
                            @empty
                            <p class="text-sm text-slate-500 sm:col-span-2">Nenhuma bebida ou produto adicional cadastrado.</p>
                            @endforelse
                        </div>
                        <p class="text-sm text-slate-400 text-center hidden py-8" id="products-empty">Nenhum produto encontrado para a busca.</p>
                    </div>
                </section>
                <section class="erp-panel" aria-labelledby="title-status">
                    <div class="erp-panel-head"><span class="erp-step">04</span><div><h3 class="erp-section-title" id="title-status">Situação do veículo</h3><p class="erp-hint">Define a fila operacional e o controle das chaves</p></div></div>
                    <div class="p-4 sm:p-5 grid sm:grid-cols-2 gap-3">
                        <label class="cursor-pointer"><input class="peer sr-only" type="radio" name="initial_status" value="finished" {{ $initialStatus === 'finished' ? 'checked' : '' }}><span class="block p-4 border border-slate-200 rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-colors"><span class="block font-bold text-sm text-slate-900"><i class="fas fa-circle-check text-blue-600 mr-2"></i>Concluído</span><span class="block text-xs text-slate-500 mt-1">O veículo não ficará em espera</span></span></label>
                        <label class="cursor-pointer"><input class="peer sr-only" type="radio" name="initial_status" value="waiting" {{ $initialStatus === 'waiting' ? 'checked' : '' }}><span class="block p-4 border border-slate-200 rounded-xl peer-checked:border-amber-500 peer-checked:bg-amber-50 transition-colors"><span class="block font-bold text-sm text-slate-900"><i class="fas fa-clock text-amber-600 mr-2"></i>Em espera</span><span class="block text-xs text-slate-500 mt-1">Retenção das chaves até a entrega</span></span></label>
                    </div>
                </section>
            </div>

            <aside class="xl:sticky xl:top-24 space-y-5 min-w-0" aria-labelledby="title-summary">
                <section class="erp-panel overflow-hidden">
                    <div class="erp-panel-head"><span class="erp-step"><i class="fas fa-receipt"></i></span><div><h3 class="erp-section-title" id="title-summary">Resumo da ordem</h3><p class="erp-hint">Atualização imediata, sem recarregar</p></div></div>
                    <div class="p-4 sm:p-5">
                        <div id="summary-items" class="divide-y divide-slate-100 mb-4"><p class="text-sm text-slate-400 py-6 text-center">Escolha uma lavagem para começar.</p></div>
                        <div class="rounded-xl bg-slate-50 border border-slate-100 p-4 flex items-center justify-between gap-2">
                            <div><p class="text-xs text-slate-500 font-medium">Total da ordem</p><p id="summary-count" class="text-xs text-slate-400 mt-1">0 itens</p></div>
                            <strong class="text-2xl tracking-tight text-blue-700 tabular-nums" id="totalAmount">R$ 0,00</strong>
                        </div>
                    </div>
                </section>
                <section class="erp-panel">
                    <div class="erp-panel-head"><span class="erp-step"><i class="fas fa-credit-card"></i></span><div><h3 class="erp-section-title">Pagamento</h3><p class="erp-hint">Opcional no cadastro · aceita divisão</p></div></div>
                    <div class="p-4 sm:p-5 space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <button class="erp-small-button !py-3" type="button" data-add-payment="pix"><i class="fas fa-qrcode text-blue-600 mr-1"></i> Pix</button>
                            <button class="erp-small-button !py-3" type="button" data-add-payment="cash"><i class="fas fa-money-bill-wave text-emerald-600 mr-1"></i> Dinheiro</button>
                            <button class="erp-small-button !py-3" type="button" data-add-payment="credit"><i class="fas fa-credit-card text-violet-600 mr-1"></i> Crédito</button>
                            <button class="erp-small-button !py-3" type="button" data-add-payment="debit"><i class="fas fa-credit-card text-slate-500 mr-1"></i> Débito</button>
                        </div>
                        <div id="payment-list" class="space-y-2" aria-live="polite"><p class="text-sm text-slate-400 text-center py-4">Pagamento pode ser registrado depois.</p></div>
                        <div class="border-t border-slate-100 pt-3 space-y-2">
                            <div class="flex justify-between text-sm text-slate-500"><span>Recebido</span><span id="total-paid" class="font-bold text-slate-800 tabular-nums">R$ 0,00</span></div>
                            <div class="flex justify-between text-sm text-slate-500"><span>Saldo pendente</span><span id="remaining-amount" class="font-extrabold text-slate-900 tabular-nums">R$ 0,00</span></div>
                        </div>
                        <input type="hidden" name="payments" id="payments-input" value="{{ old('payments', '[]') }}">
                    </div>
                </section>
                <div class="erp-panel p-4 sm:p-5 space-y-3">
                    <p id="save-hint" class="text-xs text-slate-500"><i class="fas fa-shield-halved text-emerald-600 mr-1"></i> Valores conferidos novamente pelo servidor ao salvar.</p>
                    <button type="submit" class="erp-save w-full" id="save-order" {{ $washes->isEmpty() ? 'disabled' : '' }}><i class="fas fa-check"></i><span>Criar ordem</span><span class="opacity-80">·</span><span id="submit-total" class="tabular-nums">R$ 0,00</span></button>
                    <p class="text-[11px] text-center text-slate-400">Atalho: Ctrl + Enter para salvar</p>
                </div>
            </aside>
        </div>
        <div id="order-items-inputs"></div>
    </form>
</div>

<script>
(() => {
    'use strict';
    const root = document.getElementById('erp-order');
    if (!root) return;
    const form = document.getElementById('orderForm');
    const catalog = @json($catalog);
    const oldItems = @json($selectedItems);
    const oldPaymentsText = @json(old('payments', '[]'));
    const money = (cents) => (cents / 100).toLocaleString('pt-BR', { style:'currency', currency:'BRL' });
    const price = (id, type) => Number(catalog[id]?.[type] || 0);
    const quantities = {};
    const paymentLabels = { pix:'Pix',cash:'Dinheiro',credit:'Crédito',debit:'Débito' };
    let vehicle = document.getElementById('vehicle_type').value;
    let filter = 'all';
    let payments = [];
    let saving = false;
    let totalCents = 0;

    for (const [id, qty] of Object.entries(oldItems || {})) {
        if (catalog[id]) quantities[id] = Math.min(100, Math.max(0, Number.parseInt(qty, 10) || 0));
    }
    try {
        const parsed = JSON.parse(oldPaymentsText);
        if (Array.isArray(parsed)) payments = parsed.filter(p => paymentLabels[p.method] && Number.isFinite(Number(p.amount)) && Number(p.amount) > 0).map(p => ({ method:p.method, cents:Math.round(Number(p.amount)*100) }));
    } catch (_) { /* dados de formulário antigo inválidos não interrompem a tela */ }

    const escapeHtml = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const findWash = () => Object.keys(quantities).find(id => quantities[id] > 0 && catalog[id]?.type === 'wash');
    const selected = () => Object.keys(quantities).filter(id => quantities[id] > 0 && catalog[id]);
    const showError = message => { const n = document.getElementById('order-notice'); n.textContent = message; n.classList.remove('hidden'); n.scrollIntoView({behavior:'smooth',block:'nearest'}); };
    const hideError = () => document.getElementById('order-notice').classList.add('hidden');

    function setVehicle(type) {
        if (!['normal','suv','pickup'].includes(type)) return;
        vehicle = type;
        document.getElementById('vehicle_type').value = type;
        root.querySelectorAll('[data-vehicle]').forEach(b => { const a = b.dataset.vehicle === type; b.dataset.active = a; b.setAttribute('aria-pressed', String(a)); });
        root.querySelectorAll('[data-price-for]').forEach(el => el.textContent = money(price(el.dataset.priceFor, vehicle)));
        render();
    }
    function setCustomer(type) {
        document.getElementById('customer_type').value = type;
        root.querySelectorAll('[data-customer]').forEach(b => { const a = b.dataset.customer === type; b.dataset.active = a; b.setAttribute('aria-pressed', String(a)); });
        document.getElementById('pf-fields').hidden = type !== 'pf';
        document.getElementById('pj-fields').hidden = type !== 'pj';
    }
    function setWash(id) {
        for (const key of Object.keys(quantities)) if (catalog[key]?.type === 'wash') delete quantities[key];
        if (id && catalog[id]?.type === 'wash') quantities[id] = 1;
        hideError();
        render();
    }
    function changeQty(id, change) {
        if (!catalog[id] || catalog[id].type === 'wash') return;
        const next = Math.max(0, Math.min(100, (quantities[id] || 0) + change));
        if (next === 0) delete quantities[id]; else quantities[id] = next;
        hideError();
        render();
    }
    function render() {
        const ids = selected();
        const inputs = document.getElementById('order-items-inputs');
        inputs.replaceChildren();
        for (const id of ids) {
            const input = document.createElement('input');
            input.type = 'hidden'; input.name = `items[${id}]`; input.value = quantities[id]; inputs.appendChild(input);
        }
        const wash = findWash();
        root.querySelectorAll('[data-wash]').forEach(b => b.setAttribute('aria-pressed', String(b.dataset.wash === wash)));
        root.querySelectorAll('[data-product]').forEach(el => {
            const id = el.dataset.product, count = quantities[id] || 0;
            el.dataset.selected = String(count > 0);
            document.getElementById(`qty-${id}`).textContent = count;
            el.querySelector('[data-dec]').disabled = count === 0;
            el.querySelector('[data-inc]').disabled = count >= 100;
        });
        const summary = document.getElementById('summary-items');
        summary.replaceChildren();
        totalCents = 0;
        let units = 0;
        if (!ids.length) summary.innerHTML = '<p class="text-sm text-slate-400 py-6 text-center">Escolha uma lavagem para começar.</p>';
        for (const id of ids) {
            const count = quantities[id], amount = price(id,vehicle)*count;
            units += count; totalCents += amount;
            const el = document.createElement('div'); el.className = 'erp-summary-line';
            el.innerHTML = `<span class="text-slate-600 min-w-0 break-words">${count}× ${escapeHtml(catalog[id].name)}</span><strong class="text-slate-900 whitespace-nowrap tabular-nums">${money(amount)}</strong>`;
            summary.append(el);
        }
        document.getElementById('totalAmount').textContent = money(totalCents);
        document.getElementById('summary-count').textContent = `${units} ${units === 1 ? 'unidade' : 'unidades'}`;
        document.getElementById('submit-total').textContent = money(totalCents);
        renderPayments();
    }
    function paidCents() { return payments.reduce((sum, p) => sum+p.cents, 0); }
    function renderPayments() {
        const holder = document.getElementById('payment-list');
        holder.replaceChildren();
        if (!payments.length) holder.innerHTML = '<p class="text-sm text-slate-400 text-center py-4">Pagamento pode ser registrado depois.</p>';
        payments.forEach((p,i) => {
            const row = document.createElement('div'); row.className = 'erp-payment-row';
            row.innerHTML = `<span class="text-sm font-bold text-slate-700">${paymentLabels[p.method]}</span><label class="sr-only" for="payment-${i}">Valor ${paymentLabels[p.method]}</label><input class="erp-input !min-h-[38px] !py-2 text-right tabular-nums" id="payment-${i}" data-payment-index="${i}" inputmode="decimal" value="${(p.cents/100).toFixed(2).replace('.',',')}" autocomplete="off"><button type="button" data-remove-payment="${i}" aria-label="Remover ${paymentLabels[p.method]}" class="text-red-500 hover:bg-red-50 rounded-lg h-9"><i class="fas fa-xmark"></i></button>`;
            holder.appendChild(row);
        });
        document.getElementById('total-paid').textContent = money(paidCents());
        const remaining = totalCents-paidCents();
        document.getElementById('remaining-amount').textContent = remaining >= 0 ? money(remaining) : `Excedente ${money(-remaining)}`;
        document.getElementById('remaining-amount').className = 'font-extrabold tabular-nums ' + (remaining < 0 ? 'text-red-600' : remaining === 0 && totalCents > 0 ? 'text-emerald-600' : 'text-slate-900');
        document.getElementById('payments-input').value = JSON.stringify(payments.map(p=>({method:p.method,amount:Number((p.cents/100).toFixed(2))})));
    }
    function parseMoney(value) {
        const text = String(value).trim().replace(/\s/g,'').replace(/^R\$/i,'');
        // pt-BR: 1.234,50 e 12,50; também aceita 12.50.
        const normalized = text.includes(',') ? text.replace(/\./g,'').replace(',','.') : text;
        if (!/^\d+(?:\.\d{1,2})?$/.test(normalized)) return null;
        const cents = Math.round(Number(normalized)*100);
        return Number.isSafeInteger(cents) && cents > 0 ? cents : null;
    }
    function filterProducts() {
        const term = document.getElementById('product-search').value.trim().toLocaleLowerCase('pt-BR');
        let count = 0;
        root.querySelectorAll('[data-product]').forEach(el => {
            const visible = (filter === 'all' || el.dataset.category === filter) && (!term || el.dataset.name.includes(term));
            el.hidden = !visible;
            if (visible) count++;
        });
        document.getElementById('products-empty').classList.toggle('hidden', count !== 0);
    }
    root.addEventListener('click', e => {
        const b = e.target.closest('button');
        if (!b) { const card = e.target.closest('[data-product]'); if (card) changeQty(card.dataset.product, 1); return; }
        if (b.dataset.vehicle) setVehicle(b.dataset.vehicle);
        else if (b.dataset.customer) setCustomer(b.dataset.customer);
        else if (b.dataset.wash) setWash(b.dataset.wash);
        else if (b.dataset.inc) changeQty(b.dataset.inc,1);
        else if (b.dataset.dec) changeQty(b.dataset.dec,-1);
        else if (b.dataset.filter) {
            filter = b.dataset.filter;
            root.querySelectorAll('[data-filter]').forEach(btn => btn.setAttribute('aria-pressed',String(btn.dataset.filter === filter)));
            filterProducts();
        }
        else if (b.dataset.addPayment) {
            const remaining = totalCents-paidCents();
            if (remaining <= 0) { showError('Selecione uma lavagem ou ajuste os pagamentos para adicionar outra forma de recebimento.'); return; }
            payments.push({method:b.dataset.addPayment,cents:remaining});
            hideError(); renderPayments();
            const input = document.getElementById(`payment-${payments.length-1}`);
            input?.focus(); input?.select();
        }
        else if (b.dataset.removePayment !== undefined) { payments.splice(Number(b.dataset.removePayment),1); renderPayments(); }
    });
    root.addEventListener('change', e => {
        if (!e.target.matches('[data-payment-index]')) return;
        const i = Number(e.target.dataset.paymentIndex);
        const cents = parseMoney(e.target.value);
        if (cents === null) { e.target.classList.add('erp-invalid'); showError('Informe um pagamento válido, maior que zero, usando até duas casas decimais.'); return; }
        payments[i].cents = cents;
        hideError(); renderPayments();
    });
    document.getElementById('product-search').addEventListener('input', filterProducts);
    document.getElementById('license_plate').addEventListener('input', e => { e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9-]/g,''); });
    form.addEventListener('submit', e => {
        if (saving) { e.preventDefault(); return; }
        const invalidPayment = [...root.querySelectorAll('[data-payment-index]')].find(input => parseMoney(input.value) === null);
        if (invalidPayment) { e.preventDefault(); invalidPayment.classList.add('erp-invalid'); showError('Corrija o valor do pagamento destacado antes de continuar.'); invalidPayment.focus(); return; }
        if (!document.getElementById('license_plate').value.trim()) { e.preventDefault(); showError('Informe a placa do veículo.'); document.getElementById('license_plate').focus(); return; }
        if (!findWash()) { e.preventDefault(); showError('Escolha uma lavagem para continuar.'); return; }
        root.querySelectorAll('[data-payment-index]').forEach(input => { payments[Number(input.dataset.paymentIndex)].cents = parseMoney(input.value); });
        renderPayments();
        if (paidCents() > totalCents) { e.preventDefault(); showError('O valor recebido excede o total. Ajuste os pagamentos antes de salvar.'); return; }
        if (totalCents <= 0) { e.preventDefault(); showError('Não é possível registrar uma ordem com total zero. Confira os preços dos produtos.'); return; }
        saving = true;
        const save = document.getElementById('save-order'); save.disabled = true;
        save.querySelector('span').textContent = 'Registrando...';
    });
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); form.requestSubmit(); }
        if (e.key === '/' && !e.ctrlKey && !e.metaKey && !['INPUT','TEXTAREA'].includes(document.activeElement?.tagName)) { e.preventDefault(); document.getElementById('product-search').focus(); }
    });
    window.addEventListener('pageshow', () => { if (saving) { saving=false; document.getElementById('save-order').disabled=false; document.getElementById('save-order').querySelector('span').textContent='Criar ordem'; } });
    setCustomer(document.getElementById('customer_type').value);
    setVehicle(vehicle);
    filterProducts();
})();
</script>
@endsection
