{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 17/04/2026
Descrição: Componente do sino de notificações para o painel admin.
           Usa Alpine.js para dropdown e polling a cada 30s.
           Inclua este parcial no header/topbar do layout admin.

Uso: @include('painel.partials.notification-bell')
--}}
<div x-data="notificationBell()" x-init="init()" class="relative">
    {{-- Botão do Sino --}}
    <button @click="toggle()" class="relative flex items-center justify-center rounded-xl p-2 text-slate-400 hover:bg-slate-100 hover:text-brand-primary transition-all">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        {{-- Badge de contagem --}}
        <span x-show="count > 0"
              x-text="count > 9 ? '9+' : count"
              x-transition
              class="absolute -top-1 -right-1 flex h-5 min-w-[20px] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-black text-white">
        </span>
    </button>

    {{-- Dropdown de Notificações --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="open = false"
         class="absolute right-0 top-12 z-50 w-96 rounded-2xl border border-slate-100 bg-white shadow-2xl overflow-hidden">

        {{-- Header --}}
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-3">
            <h3 class="text-sm font-black text-slate-800 tracking-tight">Notificações</h3>
            <button x-show="count > 0" @click="markAllAsRead()" class="text-[10px] font-bold uppercase tracking-wider text-brand-primary hover:underline">
                Marcar tudo como lido
            </button>
        </div>

        {{-- Lista --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-slate-50">
            <template x-for="notif in notifications" :key="notif.id">
                <div class="flex items-start gap-3 px-5 py-3 hover:bg-slate-50 transition-colors cursor-pointer"
                     :class="{ 'bg-brand-primary/5 border-l-2 border-l-brand-primary': !notif.lida }"
                     @click="markAsRead(notif)">
                    <div class="flex-shrink-0 mt-0.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl"
                             :class="{
                                'bg-blue-100 text-blue-600': notif.tipo === 'novo_pedido',
                                'bg-green-100 text-green-600': notif.tipo === 'pagamento',
                                'bg-amber-100 text-amber-600': notif.tipo === 'atraso',
                                'bg-purple-100 text-purple-600': notif.tipo === 'ticket_suporte',
                                'bg-slate-100 text-slate-400': !['novo_pedido','pagamento','atraso','ticket_suporte'].includes(notif.tipo)
                             }">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <template x-if="notif.tipo === 'novo_pedido'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                                </template>
                                <template x-if="notif.tipo === 'pagamento'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </template>
                                <template x-if="notif.tipo === 'atraso'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </template>
                                <template x-if="notif.tipo === 'ticket_suporte'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                </template>
                                <template x-if="!['novo_pedido','pagamento','atraso','ticket_suporte'].includes(notif.tipo)">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </template>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-grow min-w-0">
                        <p class="text-sm font-semibold text-slate-700 truncate" x-text="notif.mensagem"></p>
                        <p class="text-[11px] text-slate-400" x-text="notif.tempo"></p>
                    </div>
                </div>
            </template>

            {{-- Empty state --}}
            <div x-show="notifications.length === 0" class="px-5 py-10 text-center">
                <svg class="mx-auto w-10 h-10 text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <p class="text-sm font-bold text-slate-400">Nenhuma notificação</p>
            </div>
        </div>

        {{-- Footer --}}
        <a href="{{ route('admin.notifications.index') }}" class="block border-t border-slate-100 px-5 py-3 text-center text-xs font-bold uppercase tracking-wider text-brand-primary hover:bg-slate-50 transition-colors">
            Ver todas as notificações
        </a>
    </div>
</div>

<script>
function notificationBell() {
    return {
        open: false,
        count: 0,
        notifications: [],
        pollingInterval: null,

        init() {
            this.fetchRecent();
            // Polling a cada 30 segundos
            this.pollingInterval = setInterval(() => this.fetchRecent(), 30000);
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchRecent();
            }
        },

        async fetchRecent() {
            try {
                const res = await fetch('{{ route("admin.notifications.recent") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    }
                });
                const data = await res.json();
                this.notifications = data.notificacoes || [];
                this.count = data.total_nao_lidas || 0;
            } catch (e) {
                console.error('Erro ao buscar notificações:', e);
            }
        },

        async markAsRead(notif) {
            if (notif.lida) {
                if (notif.pedido_id) {
                    window.location.href = `/painel/vendas/pedidos/${notif.pedido_id}`;
                }
                return;
            }

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                await fetch(`/painel/notificacoes/lida/${notif.id}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    }
                });
                notif.lida = true;
                this.count = Math.max(0, this.count - 1);

                if (notif.pedido_id) {
                    window.location.href = `/painel/vendas/pedidos/${notif.pedido_id}`;
                }
            } catch (e) {
                console.error('Erro ao marcar como lida:', e);
            }
        },

        async markAllAsRead() {
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content;
                await fetch('{{ route("admin.notifications.read-all") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token,
                    }
                });
                this.count = 0;
                this.notifications.forEach(n => n.lida = true);
            } catch (e) {
                console.error('Erro ao marcar todas como lidas:', e);
            }
        },

        destroy() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
            }
        }
    }
}
</script>
