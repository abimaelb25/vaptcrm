@php
/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Modificado em: 14/04/2026 (Kanban Ultrarrealista - Versão Final)
*/
@endphp
<x-layouts.app titulo="{{ $titulo }}">
    @push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Fundo geral e fontes */
        body { background-color: #f3f4f6 !important; }
        
        /* Dashboard Stats boxes */
        .stat-card {
            background: white !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 12px !important;
            padding: 20px !important;
            display: flex !important;
            align-items: center !important;
            gap: 15px !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
        }
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }

        /* Container do Kanban */
        .kanban-container {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 20px !important;
            padding: 10px 0 !important;
        }
        
        @media (max-width: 1280px) {
            .kanban-container { grid-template-columns: repeat(2, 1fr) !important; }
        }
        @media (max-width: 768px) {
            .kanban-container { grid-template-columns: 1fr !important; }
        }

        /* Coluna do Kanban */
        .kanban-column {
            background: #e5e7eb !important; /* Cinza claro do print */
            border-radius: 14px !important;
            padding: 12px !important;
            display: flex !important;
            flex-direction: column !important;
            min-height: 250px !important;
            border: 1px solid #d1d5db !important;
            position: relative;
        }

        .kanban-column-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            margin-bottom: 12px !important;
            padding: 0 4px !important;
        }
        
        .kanban-column-title {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            font-size: 11px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            color: #374151 !important;
        }
        
        .kanban-count {
            background: #9ca3af !important;
            color: white !important;
            font-size: 10px !important;
            font-weight: 900 !important;
            padding: 2px 8px !important;
            border-radius: 99px !important;
        }

        /* Lista de cards */
        .kanban-list {
            display: flex !important;
            flex-direction: column !important;
            gap: 10px !important;
            flex-grow: 1 !important;
            min-height: 100px !important;
        }

        /* Placeholder Vazio */
        .kanban-empty-placeholder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -40%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0.2;
            pointer-events: none;
            width: 100%;
            text-align: center;
        }
        .kanban-empty-placeholder i {
            font-size: 48px;
            margin-bottom: 10px;
            color: #4b5563;
        }
        .kanban-empty-placeholder span {
            font-size: 14px;
            font-weight: 700;
            color: #1f2937;
        }

        /* Esconder placeholder se houver cards usando JS para maior precisão */
        .kanban-empty-placeholder.hidden {
            display: none !important;
        }
        
        /* Drag handle style */
        .drag-handle {
            cursor: grab;
            color: #9ca3af;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding-right: 8px;
        }
        .drag-handle:active { cursor: grabbing; }

        /* Sortable styles */
        .sortable-ghost {
            opacity: 0.3 !important;
            background: #cbd5e1 !important;
            border: 2px dashed #94a3b8 !important;
        }
    </style>
    @endpush

    {{-- Top Heading --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Gestão</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-0.5">Visão geral do seu negócio</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="bg-emerald-50 text-emerald-600 px-4 py-2 rounded-xl text-sm font-bold border border-emerald-100 flex items-center gap-2 shadow-sm">
                <i class="fas fa-dollar-sign"></i> Venda Rápida
            </button>
            <a href="{{ route('admin.sales.pedidos.create') }}" class="bg-brand-primary text-white px-5 py-2 rounded-xl text-sm font-black shadow-lg hover:bg-orange-600 transition-all flex items-center gap-2">
                <i class="fas fa-plus"></i> Novo pedido
            </a>
        </div>
    </div>

    {{-- Dashboard Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="stat-card">
            <div class="stat-icon bg-amber-50 text-amber-500"><i class="fas fa-users"></i></div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $estatisticas['clientes'] }}</p>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Clientes</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-blue-50 text-blue-500"><i class="fas fa-file-invoice"></i></div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $estatisticas['orcamentos'] }}</p>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Orçamentos</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-emerald-50 text-emerald-500"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $estatisticas['pedidos'] }}</p>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Pedidos</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-orange-50 text-orange-500"><i class="fas fa-check-circle"></i></div>
            <div>
                <p class="text-2xl font-black text-slate-800">{{ $estatisticas['entregues'] }}</p>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Entregues</p>
            </div>
        </div>
    </div>

    {{-- Kanban Banner Heading --}}
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-black text-slate-700 tracking-tight">Painel de Pedidos</h2>
        <div class="flex items-center gap-4">
            <div class="relative">
                <input type="text" name="cliente" value="{{ request('cliente') }}" placeholder="Buscar por nome, e-mail ou OP" class="bg-white border-slate-200 rounded-xl px-4 py-2 text-sm pl-10 min-w-[320px] shadow-sm">
                <i class="fas fa-search absolute left-3 top-3 text-slate-300"></i>
            </div>
            <a href="#" class="text-xs font-black text-slate-500 uppercase tracking-widest hover:text-slate-800">Ver todos <i class="fas fa-arrow-right ml-1"></i></a>
        </div>
    </div>

    {{-- KANBAN CONTAINER --}}
    <div class="kanban-container">
        @foreach($columns as $column)
            <div class="kanban-column border-t-4 {{ $column['color'] }}" data-status="{{ $column['slug'] }}">
                <div class="kanban-column-header">
                    <span class="kanban-column-title">
                        <i class="fas {{ $column['icon'] ?? 'fa-circle' }} opacity-50"></i>
                        {{ $column['label'] }}
                    </span>
                    <span class="kanban-count">{{ count($column['orders'] ?? []) }}</span>
                </div>

                <div class="kanban-list" id="list-{{ $column['slug'] }}">
                    @foreach($column['orders'] as $order)
                        @include('admin.sales.pedidos.partials.kanban-card', ['order' => $order])
                    @endforeach
                </div>

                {{-- Placeholder centralizado para coluna vazia --}}
                <div class="kanban-empty-placeholder {{ count($column['orders'] ?? []) > 0 ? 'hidden' : '' }}">
                    <i class="fas fa-tint"></i>
                    <span>Arraste e Solte Aqui</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Status Banner Final --}}
    <div class="mt-6 flex items-center gap-2">
        <div class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></div>
        <span class="text-xs font-bold text-slate-600 uppercase tracking-tighter">Integração <strong class="text-slate-800">Kanban Ativa</strong></span>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const _csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const lists = document.querySelectorAll('.kanban-list');
            
            lists.forEach(el => {
                new Sortable(el, {
                    group: 'kanban',
                    animation: 250,
                    ghostClass: 'sortable-ghost',
                    handle: '.drag-handle',
                    onEnd: function (evt) {
                        let orderId = evt.item.dataset.id;
                        let newStatus = evt.to.closest('.kanban-column').dataset.status;
                        let oldStatus = evt.from.closest('.kanban-column').dataset.status;

                        if (newStatus !== oldStatus) {
                            updatePedidoStatus(orderId, newStatus, evt.from, evt.item);
                        }
                    }
                });
            });

            async function updatePedidoStatus(id, status, fromEl, cardEl) {
                // Alternar placeholders
                togglePlaceholders();

                try {
                    const response = await fetch('{{ route("admin.sales.pedidos.kanban-status") }}', {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': _csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            order_id: id,
                            status: status 
                        })
                    });

                    let data;
                    try {
                        data = await response.json();
                    } catch (parseErr) {
                        throw new Error(`Servidor retornou erro inesperado (HTTP ${response.status})`);
                    }

                    if (data.sucesso === false) {
                        throw new Error(data.mensagem || 'Falha na atualização.');
                    } else if (!response.ok) {
                        // Captura erros padrão do Laravel (401, 403, 419, 422)
                        let msgErro = data.message || data.error || 'Erro desconhecido na requisição.';
                        
                        // Formata erro de validação do Laravel, se houver
                        if (data.errors) {
                            msgErro += "\n" + Object.values(data.errors).map(e => e.join(', ')).join("\n");
                        }
                        
                        throw new Error(msgErro);
                    }
                    
                    // Sucesso: Toque sonoro ou toast discreto (opcional)
                } catch (err) {
                    alert('Erro: ' + (err.message || 'Desconhecido'));
                    fromEl.appendChild(cardEl);
                    togglePlaceholders();
                }
            }

            function togglePlaceholders() {
                document.querySelectorAll('.kanban-column').forEach(col => {
                    const list = col.querySelector('.kanban-list');
                    const placeholder = col.querySelector('.kanban-empty-placeholder');
                    const countEl = col.querySelector('.kanban-count');
                    const cardCount = list.children.length;
                    
                    if (cardCount > 0) {
                        placeholder.classList.add('hidden');
                    } else {
                        placeholder.classList.remove('hidden');
                    }
                    
                    if (countEl) {
                        countEl.innerText = cardCount;
                    }
                });
            }
            
            // Inicializar placeholders
            togglePlaceholders();
        });
    </script>
    @endpush
</x-layouts.app>
