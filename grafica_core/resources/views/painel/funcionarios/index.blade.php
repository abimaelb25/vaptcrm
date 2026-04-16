@php
/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:20
| Descrição: Listagem profissional de colaboradores (Colaboradores vs Acessos).
*/
@endphp
<x-layouts.app titulo="Gestão de Colaboradores - Vapt RH">
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Time & RH</h1>
            <p class="text-slate-500 mt-1 font-medium">Gestão profissional do quadro de funcionários e acessos ao sistema</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.system.equipe.create') }}" class="btn bg-brand-primary text-white rounded-xl px-6 py-3 font-bold shadow-lg shadow-brand-primary/20 hover:-translate-y-1 transition-all flex items-center gap-2">
                <i class="fas fa-user-plus"></i> Novo Colaborador
            </a>
        </div>
    </div>

    <!-- Filtros Inteligentes -->
    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.system.equipe.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="text-[10px] uppercase font-black text-slate-400 mb-1 block">Pesquisar</label>
                <input type="text" name="busca" value="{{ $busca ?? '' }}" placeholder="Nome, email, CPF ou cargo..." class="w-full rounded-xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary">
            </div>
            <div>
                <label class="text-[10px] uppercase font-black text-slate-400 mb-1 block">Status Funcional</label>
                <select name="status" class="w-full rounded-xl border-slate-200 focus:border-brand-primary focus:ring-brand-primary">
                    <option value="">Todos</option>
                    <option value="ativo" {{ request('status') == 'ativo' ? 'selected' : '' }}>Ativos</option>
                    <option value="ferias" {{ request('status') == 'ferias' ? 'selected' : '' }}>Em Férias</option>
                    <option value="afastado" {{ request('status') == 'afastado' ? 'selected' : '' }}>Afastados</option>
                    <option value="desligado" {{ request('status') == 'desligado' ? 'selected' : '' }}>Desligados</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full h-[42px] rounded-xl bg-brand-secondary font-bold text-white hover:bg-slate-800 transition-colors">Filtrar Equipe</button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Colaborador</th>
                        <th class="px-6 py-4">Cargo / Setor</th>
                        <th class="px-6 py-4">Vínculo</th>
                        <th class="px-6 py-4 text-center">Status RH</th>
                        <th class="px-6 py-4 text-center">Acesso Sistema</th>
                        <th class="px-6 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($funcionarios as $f)
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 shrink-0 overflow-hidden rounded-full border border-slate-200 bg-slate-100">
                                        @if($f->usuario && $f->usuario->avatar)
                                            <img src="{{ asset('storage/' . $f->usuario->avatar) }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-slate-400 font-black text-xs">
                                                {{ substr($f->nome_completo, 0, 2) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-black text-slate-800">{{ $f->nome_completo }}</span>
                                        <span class="text-[11px] text-slate-400">{{ $f->email_pessoal ?? ($f->usuario->email ?? 'Sem e-mail') }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-700">{{ $f->cargo_interno ?: ($f->cargo_formal ?: 'Não definido') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $f->setor ?: 'Geral' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight">
                                    {{ $f->tipo_vinculo ?: 'N/A' }}
                                </span>
                                @if($f->data_admissao)
                                    <div class="text-[10px] text-slate-400 mt-1">Desde: {{ \Carbon\Carbon::parse($f->data_admissao)->format('d/m/Y') }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusColors = [
                                        'ativo' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        'ferias' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'afastado' => 'bg-blue-100 text-blue-700 border-blue-200',
                                        'desligado' => 'bg-rose-100 text-rose-700 border-rose-200',
                                        'inativo' => 'bg-slate-100 text-slate-700 border-slate-200',
                                    ];
                                    $color = $statusColors[$f->status_funcional] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                @endphp
                                <span class="{{ $color }} border px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">{{ $f->status_funcional }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($f->user_id)
                                    @if($f->usuario && $f->usuario->ativo)
                                        <div class="flex flex-col items-center">
                                            <span class="text-emerald-500 text-xs font-black flex items-center gap-1">
                                                <i class="fas fa-check-circle"></i> LIBERADO
                                            </span>
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ $f->usuario->perfil }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-300 text-xs font-black flex items-center justify-center gap-1 line-through">
                                            <i class="fas fa-times-circle"></i> BLOQUEADO
                                        </span>
                                    @endif
                                @else
                                    <span class="text-[10px] font-bold text-slate-300 italic">Sem Acesso</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-1">
                                    <a href="{{ route('admin.system.equipe.show', $f->id) }}" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-secondary hover:text-white transition-all shadow-sm" title="Ficha Completa & RH">
                                        <i class="fas fa-file-contract"></i>
                                    </a>
                                    <a href="{{ route('admin.system.equipe.edit', $f->id) }}" class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-brand-primary hover:text-white transition-all shadow-sm" title="Editar Cadastro">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($f->user_id !== auth()->id())
                                    <form action="{{ route('admin.system.equipe.destroy', $f->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Deseja realmente remover este colaborador do quadro ativo? O acesso ao sistema também será revogado.');">
                                        @csrf @method('DELETE')
                                        <button class="p-2 rounded-lg bg-slate-100 text-rose-400 hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <i class="fas fa-users-slash text-6xl mb-4"></i>
                                    <p class="font-black text-slate-500 uppercase tracking-widest">Nenhum colaborador encontrado</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($funcionarios->hasPages())
            <div class="border-t border-slate-200 p-4">
                {{ $funcionarios->links() }}
            </div>
        @endif
    </div>

    <!-- Widgets Informativos -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="rounded-3xl bg-white border border-slate-200 p-6 flex items-center gap-5 shadow-sm">
            <div class="h-14 w-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl">
                <i class="fas fa-user-check"></i>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase">Colaboradores Ativos</p>
                <p class="text-2xl font-black text-slate-800">{{ $funcionarios->total() }}</p>
            </div>
        </div>
        <div class="rounded-3xl bg-white border border-slate-200 p-6 flex items-center gap-5 shadow-sm">
            <div class="h-14 w-14 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl">
                <i class="fas fa-umbrella-beach"></i>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase">Em Férias / Programado</p>
                <p class="text-2xl font-black text-slate-800">0</p>
            </div>
        </div>
        <div class="rounded-3xl bg-white border border-slate-200 p-6 flex items-center gap-5 shadow-sm">
            <div class="h-14 w-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl">
                <i class="fas fa-key"></i>
            </div>
            <div>
                <p class="text-xs font-black text-slate-400 uppercase">Licenças de Acesso</p>
                <p class="text-2xl font-black text-slate-800">{{ $funcionarios->where('user_id', '!=', null)->count() }}</p>
            </div>
        </div>
    </div>
</x-layouts.app>
