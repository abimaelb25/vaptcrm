<x-layouts.app titulo="Páginas Legais - CMS">
    <div class="mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">Páginas Institucionais</h1>
            <p class="text-slate-500 mt-1 font-medium">Crie políticas, termos e páginas ricas para sua loja.</p>
        </div>
        
        <div class="flex items-center gap-3">
            <span class="text-xs font-bold px-3 py-1 rounded-full {{ $limiteAtingido ? 'bg-red-100 text-red-600' : 'bg-brand-primary/10 text-brand-primary' }}">
                Páginas Autorais: {{ $totalPersonalizadas }} / 5
            </span>
            
            <x-dropdown>
                <x-slot name="trigger">
                    <button class="btn bg-brand-primary text-white font-bold py-2.5 px-5 rounded-xl shadow flex items-center gap-2">
                        + Nova Página
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </button>
                </x-slot>
                <div class="w-64">
                    <div class="p-2 text-[10px] font-black uppercase text-slate-400 tracking-widest">A partir de Modelos</div>
                    <a href="{{ route('admin.system.paginas-legais.create', ['template' => 'politica_privacidade']) }}" class="block px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">🔒 Política de Privacidade</a>
                    <a href="{{ route('admin.system.paginas-legais.create', ['template' => 'termos_condicoes']) }}" class="block px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">📜 Termos de Uso</a>
                    <a href="{{ route('admin.system.paginas-legais.create', ['template' => 'reembolso_devolucao']) }}" class="block px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">🔄 Trocas e Devoluções</a>
                    <a href="{{ route('admin.system.paginas-legais.create', ['template' => 'entregas_finalizacoes']) }}" class="block px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">🚚 Prazos e Entregas</a>
                    
                    <div class="border-t border-slate-100 my-1"></div>
                    
                    @if(!$limiteAtingido)
                        <a href="{{ route('admin.system.paginas-legais.create') }}" class="block px-4 py-2 text-sm font-bold text-brand-primary hover:bg-orange-50">Página Única (Em Branco)</a>
                    @else
                        <span class="block px-4 py-2 text-sm font-bold text-slate-400 cursor-not-allowed">Limite Atingido</span>
                    @endif
                </div>
            </x-dropdown>
        </div>
    </div>

    @if(session('erro'))
        <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-600 font-bold text-sm border border-red-200">
            {{ session('erro') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200 uppercase text-[10px] tracking-wider">
                <tr>
                    <th class="px-6 py-4">Página / Link</th>
                    <th class="px-6 py-4">Exibição</th>
                    <th class="px-6 py-4">Status no Rodapé</th>
                    <th class="px-6 py-4 text-right">Ação</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($paginas as $p)
                <tr class="hover:bg-slate-50">
                    <td class="px-6 py-4">
                        <div class="font-black text-slate-800 text-base flex items-center gap-2">
                            {{ $p->titulo }}
                            @if($p->pagina_sistema)
                                <span class="bg-blue-100 text-blue-700 text-[9px] uppercase px-2 py-0.5 rounded-full">Essencial</span>
                            @endif
                        </div>
                        <a href="{{ url('p/' . $p->slug) }}" target="_blank" class="text-brand-primary text-xs font-semibold hover:underline flex items-center gap-1 mt-1">
                            <x-icon name="link" class="w-3 h-3" /> /p/{{ $p->slug }}
                        </a>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-slate-500 font-semibold text-xs border border-slate-200 px-2 py-1 rounded-lg bg-white shadow-sm">
                            Ordem: {{ $p->ordem_exibicao }}
                        </span>
                    </td>
                    <td class="px-6 py-4 gap-2 flex flex-col items-start mt-2">
                        @if($p->ativa)
                            <span class="bg-emerald-100 text-emerald-700 font-bold text-[10px] uppercase px-2 py-1 rounded-lg">Página Ativa</span>
                        @else
                            <span class="bg-slate-100 text-slate-500 font-bold text-[10px] uppercase px-2 py-1 rounded-lg">Desativada</span>
                        @endif

                        @if($p->exibir_no_rodape)
                            <span class="bg-blue-100 text-blue-700 font-bold text-[10px] uppercase px-2 py-1 rounded-lg">Fixada no Rodapé</span>
                        @else
                            <span class="bg-slate-100 text-slate-500 font-bold text-[10px] uppercase px-2 py-1 rounded-lg">Página Oculta</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.system.paginas-legais.edit', $p->id) }}" class="text-slate-500 hover:text-brand-primary font-bold px-3 py-2 bg-slate-50 rounded-lg hover:bg-orange-50 transition-colors inline-block text-xs">Editar</a>
                        
                        @if(!$p->pagina_sistema)
                            <form action="{{ route('admin.system.paginas-legais.destroy', $p->id) }}" method="POST" class="inline ml-1" onsubmit="return confirm('Deseja realmente apagar esta página? A exclusão é irreversível.');">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-3 py-2 rounded-lg font-bold text-xs transition-colors">Excluir</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-12 text-center text-slate-500">
                    <div class="mb-4 text-5xl">📄</div>
                    <div class="font-black text-slate-700">Nenhuma página criada</div>
                    <p class="text-sm">Comece criando sua Política de Privacidade.</p>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="mt-4">
        {{ $paginas->links() }}
    </div>
</x-layouts.app>

