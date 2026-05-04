{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Listagem centralizada de notificações do sistema.
--}}
<x-layouts.app titulo="Notificações Internas">
    <div class="mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">Centro de Alertas</h1>
            <p class="text-sm font-medium text-slate-500">Acompanhe as atividades cruciais da sua gráfica em tempo real.</p>
        </div>
        
        <div class="flex gap-3">
            @if(Auth::user()->unreadNotifications->count() > 0)
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-black text-slate-600 hover:bg-slate-200 transition-all uppercase tracking-widest">
                        <x-icon name="check-double" class="w-4 h-4" />
                        Marcar tudo como lido
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="space-y-3">
        @forelse($notificacoes as $notificacao)
            <div class="group relative flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition-all hover:border-brand-primary/20 hover:shadow-md {{ $notificacao->read_at ? 'opacity-60' : 'border-l-4 border-l-brand-primary' }}">
                <div class="flex-shrink-0">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl {{ $notificacao->read_at ? 'bg-slate-100 text-slate-400' : 'bg-brand-primary/10 text-brand-primary' }}">
                        @php
                            $tipo = $notificacao->data['tipo'] ?? 'default';
                            $icon = match($tipo) {
                                'novo_pedido' => 'shopping-cart',
                                'pagamento' => 'cash',
                                'atraso' => 'clock',
                                default => 'bell'
                            };
                        @endphp
                        <x-icon name="{{ $icon }}" class="w-6 h-6" />
                    </div>
                </div>

                <div class="flex-grow">
                    <div class="flex items-center justify-between gap-2">
                        <h4 class="font-black text-slate-800 tracking-tight">{{ $notificacao->data['mensagem'] ?? 'Notificação de sistema' }}</h4>
                        <span class="text-[10px] font-black uppercase text-slate-400 opacity-60">{{ $notificacao->created_at->diffForHumans() }}</span>
                    </div>
                    
                    <div class="mt-1 flex items-center gap-4">
                         @if(isset($notificacao->data['pedido_id']))
                            <a href="{{ route('admin.sales.pedidos.show', $notificacao->data['pedido_id']) }}" class="text-[11px] font-bold text-brand-secondary hover:underline">
                                Ver Detalhes do Pedido
                            </a>
                         @endif
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    @if(!$notificacao->read_at)
                        <form method="POST" action="{{ route('admin.notifications.read', $notificacao->id) }}">
                            @csrf
                            <button title="Marcar como lida" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-primary transition-all">
                                <x-icon name="check" class="w-5 h-5" />
                            </button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('admin.notifications.destroy', $notificacao->id) }}">
                        @csrf
                        @method('DELETE')
                        <button title="Excluir" class="rounded-lg p-2 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-all">
                            <x-icon name="trash" class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="flex flex-col items-center justify-center rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50 py-20 text-center">
                <div class="mb-4 rounded-full bg-slate-200 p-4 text-slate-400">
                    <x-icon name="bell" class="w-10 h-10" />
                </div>
                <h3 class="font-black text-slate-800">Tudo em ordem por aqui!</h3>
                <p class="text-sm text-slate-500">Não há novas notificações para você no momento.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $notificacoes->links() }}
    </div>
</x-layouts.app>
