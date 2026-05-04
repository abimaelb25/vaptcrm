<x-layouts.app titulo="Kanban de Produção">
    @php
        $phaseTheme = static function (string $name): array {
            $normalized = mb_strtolower(trim($name));

            return match (true) {
                str_contains($normalized, 'pre') => [
                    'rail' => 'border-l-[#3b82f6]',
                ],
                str_contains($normalized, 'pos') => [
                    'rail' => 'border-l-[#eab308]',
                ],
                str_contains($normalized, 'final') => [
                    'rail' => 'border-l-[#f97316]',
                ],
                str_contains($normalized, 'produc') => [
                    'rail' => 'border-l-[#22c55e]',
                ],
                default => [
                    'rail' => 'border-l-slate-400',
                ],
            };
        };
    @endphp

    <div class="space-y-8">
        <div id="kanban-loader" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/40 backdrop-blur-sm">
            <div class="rounded-xl bg-white px-5 py-4 shadow-xl">
                <p class="text-sm font-bold text-slate-700">Movendo OP, aguarde...</p>
            </div>
        </div>

        <div class="flex flex-col gap-2">
            <h1 class="text-3xl font-black tracking-tight text-slate-900">Chão de Fábrica</h1>
            <p class="text-sm text-slate-500">Kanban operacional por fases e etapas com rastreabilidade de movimentações.</p>
            <p class="text-xs font-semibold text-slate-500">Atualização automática a cada 15s • próxima em <span id="polling-countdown">15</span>s</p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-500">Em Produção</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $metrics['total_em_producao'] }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-500">Concluídas Hoje</p>
                <p class="mt-2 text-3xl font-black text-emerald-600">{{ $metrics['total_concluidas_hoje'] }}</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-500">Tempo Médio</p>
                <p class="mt-2 text-3xl font-black text-sky-700">{{ $metrics['tempo_medio_producao'] }} min</p>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase text-slate-500">Gargalo Atual</p>
                <p class="mt-2 text-base font-black text-amber-700">{{ $metrics['gargalo_atual']['etapa_nome'] ?? 'Sem gargalo' }}</p>
                <p class="text-xs font-semibold text-slate-500">{{ $metrics['gargalo_atual']['total_ops'] ?? 0 }} OP(s)</p>
                <p class="mt-1 text-xs font-semibold text-rose-600">Taxa de atraso: {{ $metrics['taxa_atraso'] ?? 0 }}%</p>
            </div>
        </div>

        <div class="space-y-8">
            @foreach($kanban as $phase)
                @php($theme = $phaseTheme($phase['fase']))
                <section class="rounded-3xl border border-slate-200 border-l-4 {{ $theme['rail'] }} bg-slate-50 shadow-sm overflow-hidden">
                    <header class="bg-[#0f172a] px-6 py-4 text-white flex items-center justify-between">
                        <h2 class="text-lg font-black tracking-tight text-white">{{ $phase['fase'] }}</h2>
                        <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-bold text-white">
                            {{ count($phase['etapas']) }} etapa(s)
                        </span>
                    </header>

                    <div class="grid gap-4 p-4 md:grid-cols-2 xl:grid-cols-4">
                        @foreach($phase['etapas'] as $step)
                            <article class="card-etapa flex min-h-[260px] flex-col rounded-2xl border border-[#e5e7eb] bg-white shadow-sm transition">
                                <header class="border-b border-[#e5e7eb] px-4 py-3">
                                    <h3 class="text-sm font-black !text-[#111827]">{{ $step['etapa_nome'] }}</h3>
                                    <p class="text-[10px] font-bold uppercase tracking-widest !text-[#6b7280]"><span data-step-count>{{ count($step['ordens']) }}</span> OP(s)</p>
                                </header>

                                <ul class="kanban-dropzone flex-1 space-y-2 overflow-y-auto p-4"
                                    data-step-id="{{ $step['etapa_id'] }}">
                                    @foreach($step['ordens'] as $order)
                                        @php
                                            $priorityClass = match($order['prioridade']) {
                                                'urgente' => 'bg-rose-600 text-white',
                                                'alta' => 'bg-amber-500 text-white',
                                                'normal' => 'bg-sky-600 text-white',
                                                default => 'bg-slate-500 text-white',
                                            };
                                        @endphp
                                        <li class="op-card cursor-grab rounded-lg border border-slate-200 bg-white p-3 shadow-sm"
                                            data-order-id="{{ $order['id'] }}">
                                            <div class="mb-2 flex items-start justify-between gap-2">
                                                <p class="text-xs font-black !text-[#111827]">OP #{{ $order['id'] }}</p>
                                                <span class="badge-prioridade rounded-full px-2 py-0.5 text-[10px] font-black uppercase {{ $priorityClass }}">
                                                    {{ $order['prioridade'] }}
                                                </span>
                                            </div>

                                            <p class="text-sm font-bold !text-[#111827]">{{ $order['cliente_nome'] }}</p>
                                            <p class="text-xs font-semibold !text-[#6b7280]">{{ $order['produto_nome'] }}</p>

                                            @if(($order['atrasada'] ?? false) === true)
                                                <p class="mt-2 rounded-md bg-rose-50 px-2 py-1 text-[11px] font-bold text-rose-700">
                                                    Atrasada há {{ $order['minutos_atraso'] ?? 0 }} min
                                                </p>
                                            @endif

                                            <div class="mt-3 flex items-center justify-between text-xs">
                                                <span class="font-semibold !text-[#111827]">Qtd: {{ $order['quantidade'] }}</span>
                                                <span class="font-semibold !text-[#6b7280]">
                                                    {{ $order['tempo_em_producao_humano'] ?? ($order['tempo_em_producao_minutos'] . ' min em produção') }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>

    @push('styles')
        <style>
            .card-etapa {
                color: #111827;
            }

            .card-etapa * {
                color: inherit;
            }

            .card-etapa:hover {
                background: #f8fafc;
            }

            .card-etapa.active {
                background: #fff7ed;
                border-color: #f97316;
            }

            /* Preserve badge contrast on priority */
            .card-etapa .badge-prioridade {
                color: #fff !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
        <script>
            (() => {
                const csrf = '{{ csrf_token() }}';
                const lists = document.querySelectorAll('.kanban-dropzone');
                const loader = document.getElementById('kanban-loader');
                const countdown = document.getElementById('polling-countdown');
                let isDragging = false;
                let isMoving = false;
                let secondsToRefresh = 15;

                const showLoader = () => {
                    if (!loader) {
                        return;
                    }

                    loader.classList.remove('hidden');
                    loader.classList.add('flex');
                };

                const hideLoader = () => {
                    if (!loader) {
                        return;
                    }

                    loader.classList.remove('flex');
                    loader.classList.add('hidden');
                };

                const rollbackMove = (evt) => {
                    const { from, item, oldIndex } = evt;

                    if (!from || oldIndex === undefined) {
                        return;
                    }

                    const target = from.children[oldIndex] ?? null;
                    from.insertBefore(item, target);
                };

                lists.forEach((list) => {
                    new Sortable(list, {
                        group: 'kanban-producao',
                        animation: 150,
                        ghostClass: 'opacity-60',
                        dragClass: 'shadow-lg',
                        onStart: () => {
                            isDragging = true;
                        },
                        onEnd: async (evt) => {
                            isDragging = false;

                            if (evt.from === evt.to) {
                                return;
                            }

                            const orderId = evt.item.dataset.orderId;
                            const nextStepId = evt.to.dataset.stepId;

                            if (!orderId || !nextStepId) {
                                rollbackMove(evt);
                                return;
                            }

                            try {
                                isMoving = true;
                                showLoader();

                                const response = await fetch(`/api/production/orders/${orderId}/move`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': csrf,
                                    },
                                    body: JSON.stringify({
                                        next_step_id: Number(nextStepId),
                                    }),
                                });

                                const payload = await response.json();

                                if (!response.ok || payload.success === false) {
                                    rollbackMove(evt);
                                    window.alert(payload.message ?? 'Movimentação inválida para esta OP.');
                                    return;
                                }

                                window.location.reload();
                            } catch (error) {
                                rollbackMove(evt);
                                window.alert('Falha ao mover OP. Tente novamente.');
                            } finally {
                                isMoving = false;
                                hideLoader();
                            }
                        },
                    });
                });

                setInterval(() => {
                    secondsToRefresh -= 1;
                    if (countdown) {
                        countdown.textContent = String(Math.max(secondsToRefresh, 0));
                    }

                    if (secondsToRefresh > 0) {
                        return;
                    }

                    secondsToRefresh = 15;

                    if (isDragging || isMoving || document.hidden) {
                        return;
                    }

                    fetch('/api/production/kanban', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                        },
                    })
                        .then((response) => response.json().then((payload) => ({ response, payload })))
                        .then(({ response, payload }) => {
                            if (!response.ok || payload.success === false) {
                                return;
                            }

                            window.location.reload();
                        })
                        .catch(() => {
                            // Falhas pontuais de polling não devem interromper a operação do kanban.
                        });
                }, 1000);
            })();
        </script>
    @endpush
</x-layouts.app>
