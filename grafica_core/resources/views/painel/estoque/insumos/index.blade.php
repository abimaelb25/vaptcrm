{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 18:50
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Estoque de <span class="text-brand-primary">Insumos</span></h1>
            <p class="text-slate-500 font-medium">Controle de matéria-prima e suprimentos para produção.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            <a href="{{ route('admin.inventory.movimentacoes.entrada') }}" class="rounded-xl bg-emerald-500 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-emerald-600 flex items-center gap-2">
                <span>📩</span> Registrar Entrada
            </a>
            <a href="{{ route('admin.inventory.insumos.create') }}" class="rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-bold text-white shadow-md transition hover:bg-slate-700 flex items-center gap-2">
                <span>➕</span> Novo Insumo
            </a>
        </div>
    </div>

    <!-- Filtros e Status -->
    <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="md:col-span-3 rounded-2xl bg-white p-4 shadow-sm border border-slate-100">
            <form class="flex flex-wrap items-center gap-4">
                <input type="text" name="busca" value="{{ request('busca') }}" placeholder="Buscar insumo..." class="rounded-lg border-slate-200 text-sm focus:ring-brand-primary min-w-[200px]">
                
                <select name="categoria" class="rounded-lg border-slate-200 text-sm focus:ring-brand-primary">
                    <option value="">Todas as Categorias</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat }}" {{ request('categoria') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="estoque_baixo" value="1" {{ request('estoque_baixo') ? 'checked' : '' }} class="rounded text-brand-primary focus:ring-brand-primary">
                    <span class="text-sm font-bold text-slate-600">Apenas Estoque Baixo</span>
                </label>

                <button type="submit" class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-bold text-slate-600 hover:bg-slate-200 transition">Filtrar</button>
            </form>
        </div>

        <a href="{{ route('admin.inventory.insumos.alertas') }}" class="rounded-2xl p-4 shadow-sm border {{ $alertasCount > 0 ? 'bg-orange-50 border-orange-200' : 'bg-white border-slate-100' }} flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black uppercase text-slate-400">Alertas</p>
                <p class="text-2xl font-black {{ $alertasCount > 0 ? 'text-orange-600' : 'text-slate-800' }}">{{ $alertasCount }}</p>
            </div>
            <span class="text-2xl">{{ $alertasCount > 0 ? '⚠️' : '✅' }}</span>
        </a>
    </div>

    <!-- Tabela de Insumos -->
    <div class="rounded-2xl bg-white border border-slate-100 shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Insumo</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase">Categoria</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-center">Und.</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-right">Mínimo</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-right">Atual</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-right">Custo Médio</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-center">Status</th>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($insumos as $insumo)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <p class="text-sm font-black text-slate-800">{{ $insumo->nome }}</p>
                                <p class="text-[10px] text-slate-400 font-bold">COD: {{ $insumo->codigo_interno ?? '—' }}</p>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-slate-500">{{ $insumo->categoria ?? 'S/ Categoria' }}</td>
                            <td class="px-6 py-4 text-xs font-black text-slate-600 text-center">{{ $insumo->unidade_medida }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-400 text-right">{{ number_format($insumo->estoque_minimo, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-lg font-black {{ $insumo->estaComEstoqueBaixo() ? 'text-red-600' : 'text-slate-800' }}">
                                    {{ number_format($insumo->estoque_atual, 2, ',', '.') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-black text-slate-700 text-right">R$ {{ number_format($insumo->custo_medio, 2, ',', '.') }}</td>
                            <td class="px-6 py-4 text-center">
                                @php $status = $insumo->status_estoque; @endphp
                                <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase 
                                    {{ $status === 'ok' ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-200' : 
                                       ($status === 'baixo' ? 'bg-orange-50 text-orange-600 ring-1 ring-orange-200' : 'bg-red-50 text-red-600 ring-1 ring-red-200') }}">
                                    {{ $status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.inventory.insumos.edit', $insumo) }}" class="p-2 text-slate-400 hover:text-brand-primary transition" title="Editar">✏️</a>
                                    <a href="{{ route('admin.inventory.insumos.ajuste', $insumo) }}" class="p-2 text-slate-400 hover:text-brand-secondary transition" title="Ajuste de Saldo">⚖️</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-20 text-center">
                                <span class="text-6xl block mb-4 opacity-20">📦</span>
                                <p class="text-lg font-black text-slate-600">Nenhum insumo encontrado.</p>
                                <p class="text-sm text-slate-400">Cadastre suas lâminas, lonas, tintas e outros itens consumíveis.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($insumos->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                {{ $insumos->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
