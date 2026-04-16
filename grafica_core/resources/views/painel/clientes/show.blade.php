{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-06 00:00 -03:00
--}}
<x-layouts.app>
    <!-- Header Navegação -->
    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.sales.clientes.index') }}" class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm border border-slate-200 text-slate-500 hover:text-brand-primary hover:bg-brand-primary/10 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </a>
            <h1 class="text-3xl font-black text-brand-secondary">Perfil do Cliente</h1>
        </div>
        <div class="mt-4 sm:mt-0">
            <span class="inline-flex rounded-full bg-status-success/10 px-3 py-1 font-bold text-status-success border border-status-success/20">
                Cliente desde {{ $cliente->created_at->format('M/Y') }}
            </span>
        </div>
    </div>

    <!-- Container Header Perfil -->
    <div class="mb-8 rounded-3xl bg-white shadow-lg border border-slate-100 overflow-hidden relative">
        <div class="h-32 w-full bg-gradient-to-r from-brand-secondary to-blue-900 border-b-4 border-brand-primary"></div>
        <div class="px-6 sm:px-10 pb-8 relative">
            <div class="-mt-16 flex flex-col sm:flex-row gap-6 relative">
                <!-- Avatar Gigante -->
                <div class="h-32 w-32 shrink-0 rounded-full bg-slate-50 border-4 border-white shadow-xl flex items-center justify-center text-brand-primary font-black text-5xl overflow-hidden bg-white relative z-10">
                    @if($cliente->avatar)
                        <img src="{{ asset('storage/' . $cliente->avatar) }}" alt="{{ $cliente->nome }}" class="h-full w-full object-cover">
                    @else
                        {{ substr(mb_strtoupper($cliente->nome, 'UTF-8'), 0, 1) }}
                    @endif
                </div>

                <!-- Info Direita -->
                <div class="flex-1 mt-4 sm:mt-16 sm:pl-2">
                    <h2 class="text-3xl font-black text-slate-800 tracking-tight">{{ $cliente->nome }}</h2>
                    @if($cliente->empresa)
                        <p class="text-lg font-bold text-slate-500 flex items-center gap-2">
                            <span>🏢</span> {{ $cliente->empresa }}
                        </p>
                    @endif
                    <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold text-slate-600">
                        @if($cliente->telefone)
                            <div class="flex items-center gap-2">
                                <span>📱</span> {{ $cliente->telefone }}
                            </div>
                        @endif
                        @if($cliente->email)
                            <div class="flex items-center gap-2">
                                <span>📧</span> {{ $cliente->email }}
                            </div>
                        @endif
                        @if($cliente->cpf_cnpj)
                            <div class="flex items-center gap-2 bg-slate-100 px-3 py-1 rounded border border-slate-200">
                                <span>🪪</span> <span class="font-bold text-slate-400">{{ $cliente->tipo_pessoa == 'J' ? 'CNPJ:' : 'CPF:' }}</span> {{ $cliente->cpf_cnpj }}
                            </div>
                        @endif
                        @if($cliente->data_nascimento)
                            <div class="flex items-center gap-2 bg-brand-accent/10 text-brand-accent px-3 py-1 rounded border border-brand-accent/20">
                                <span>🎂</span> Aniv: {{ \Carbon\Carbon::parse($cliente->data_nascimento)->format('d/m/Y') }}
                            </div>
                        @endif
                    </div>
                </div>

                <!-- WhatsApp CTA Action -->
                @if($cliente->telefone)
                @php $whatsClean = preg_replace('/[^0-9]/', '', $cliente->telefone); @endphp
                <div class="absolute right-0 top-16 hidden sm:block">
                    <a href="https://wa.me/55{{ $whatsClean }}" target="_blank" class="flex items-center gap-3 bg-gradient-to-r from-emerald-500 to-green-600 text-white px-6 py-4 rounded-2xl shadow-lg hover:shadow-xl hover:scale-105 transition-all w-64 group relative overflow-hidden">
                        <div class="absolute inset-0 bg-white/20 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>    
                        <svg class="h-8 w-8 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.372-12 12 0 2.19.593 4.24 1.621 6L0 24l6.196-1.583c1.716.945 3.69 1.488 5.804 1.488 6.627 0 12-5.373 12-12 0-6.628-5.373-12-12-12z"/></svg>
                        <div class="flex flex-col">
                            <span class="text-xs font-bold uppercase tracking-widest text-green-100">Contatar Cliente</span>
                            <span class="text-lg font-black leading-tight">Enviar Mensagem</span>
                        </div>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Histórico de Pedidos -->
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-2xl font-bold flex items-center gap-3">
                <span class="bg-brand-primary h-8 w-1.5 rounded-full block"></span> Histórico de Pedidos Internos
            </h3>
            <span class="text-sm font-semibold text-slate-500">{{ $cliente->pedidos->count() }} pedido(s)</span>
        </div>
        
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-lg overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-200 text-left">
                    <tr>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs text-slate-600">Protocolo</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs text-slate-600">Data</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs text-slate-600">Status</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs text-slate-600">Resumo de Itens</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs text-slate-600 text-right">Valor Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($cliente->pedidos as $pedido)
                        <tr class="transition-colors hover:bg-brand-primary/5">
                            <td class="px-5 py-4 font-bold text-slate-700">#{{ $pedido->numero_pedido ?? $pedido->id }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded bg-slate-100 px-2.5 py-1 text-xs font-bold font-mono border text-slate-600 uppercase">{{ $pedido->status }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-500">
                                {{ $pedido->itensPedido->count() }} Item(s)
                                <div class="text-xs text-slate-400 mt-1 line-clamp-1">
                                    @foreach($pedido->itensPedido as $item)
                                        {{ $item->produto->nome ?? 'Produto Customizado' }}@if(!$loop->last), @endif
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-5 py-4 font-black text-brand-secondary text-right text-lg">R$ {{ number_format((float) $pedido->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                                <span class="text-4xl block mb-2">🛒</span>
                                <span class="font-bold text-lg">Sem histórico</span>
                                <p class="text-sm">O cliente ainda não possui nenhum pedido faturado.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
