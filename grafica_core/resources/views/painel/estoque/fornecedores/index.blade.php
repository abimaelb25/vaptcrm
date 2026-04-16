{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:20
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Meus <span class="text-brand-primary">Fornecedores</span></h1>
            <p class="text-slate-500 font-medium">Gestão de parceiros e fornecedores de insumos.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.inventory.fornecedores.create') }}" class="rounded-xl bg-slate-800 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:scale-105 flex items-center gap-2">
                <span>➕</span> Novo Fornecedor
            </a>
        </div>
    </div>

    <!-- Lista de Fornecedores -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($fornecedores as $forn)
            <div class="rounded-2xl bg-white p-6 shadow-xl border border-slate-100 flex flex-col justify-between transition hover:shadow-2xl">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="bg-brand-primary/10 text-brand-primary p-3 rounded-xl text-2xl">🚚</span>
                        <span class="rounded-full px-3 py-1 text-[10px] font-black uppercase ring-1 {{ $forn->ativo ? 'bg-emerald-50 text-emerald-600 ring-emerald-200' : 'bg-slate-50 text-slate-600 ring-slate-200' }}">
                            {{ $forn->ativo ? 'Ativo' : 'Inativo' }}
                        </span>
                    </div>
                    
                    <h3 class="text-lg font-black text-slate-800 leading-tight mb-1">{{ $forn->nome }}</h3>
                    <p class="text-xs font-bold text-slate-400 mb-4">{{ $forn->razao_social ?? 'Pessoa Física / Outro' }}</p>
                    
                    <div class="space-y-2">
                        @if($forn->whatsapp)
                            <div class="flex items-center gap-2 text-sm text-slate-600 font-medium">
                                <span class="text-emerald-500">🟢</span> {{ $forn->whatsapp }}
                            </div>
                        @endif
                        @if($forn->email)
                            <div class="flex items-center gap-2 text-sm text-slate-600 font-medium">
                                <span class="text-brand-primary">📧</span> {{ $forn->email }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-6 pt-6 border-t border-slate-50 flex items-center justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase">{{ $forn->cidade }} {{ $forn->uf ? "/ $forn->uf" : '' }}</p>
                    <a href="{{ route('admin.inventory.fornecedores.edit', $forn) }}" class="text-brand-primary font-black text-sm hover:underline">Editar Detalhes</a>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white p-20 text-center border border-dashed border-slate-200">
                <span class="text-6xl block mb-4 opacity-20">🏢</span>
                <p class="text-lg font-black text-slate-600">Nenhum fornecedor cadastrado.</p>
            </div>
        @endforelse
    </div>

    @if($fornecedores->hasPages())
        <div class="mt-8">
            {{ $fornecedores->links() }}
        </div>
    @endif
</x-layouts.app>
