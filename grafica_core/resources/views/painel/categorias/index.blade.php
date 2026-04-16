{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Modificado em: 2026-04-10
--}}
<x-layouts.app>
    <div class="mb-6 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Categorias do Catálogo</h1>
            <p class="text-slate-500 font-medium">Organize, ordene e destaque as famílias de produtos da loja.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.catalog.categorias.create') }}" class="rounded-xl bg-gradient-to-r from-brand-primary to-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition-transform duration-300 hover:scale-105 hover:shadow-lg flex items-center gap-2">
                <span>➕</span> Nova Categoria
            </a>
        </div>
    </div>

    @if(session('sucesso'))
        <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-emerald-700 font-bold shadow-sm flex items-center gap-3">
            <span>✅</span> {{ session('sucesso') }}
        </div>
    @endif
    @if(session('erro'))
        <div class="mb-5 rounded-xl bg-red-50 border border-red-200 p-4 text-red-600 font-bold shadow-sm flex items-center gap-3">
            <span>⚠️</span> {{ session('erro') }}
        </div>
    @endif

    <div class="rounded-2xl border border-slate-100 bg-white shadow-lg overflow-hidden">
        <div class="bg-gradient-to-r from-brand-secondary to-slate-700 px-6 py-3 flex items-center justify-between">
            <span class="text-white font-bold text-sm uppercase tracking-widest">Lista de Categorias · Arraste para Reordenar</span>
            <span class="text-slate-300 text-xs font-semibold">{{ $categorias->total() }} no sistema</span>
        </div>

        <ul id="lista-categorias" class="divide-y divide-slate-100">
            @forelse($categorias as $cat)
                <li data-id="{{ $cat->id }}" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-50/70 transition group cursor-grab active:cursor-grabbing">
                    <!-- Handle de drag -->
                    <span class="text-slate-300 group-hover:text-slate-500 transition text-lg select-none">⠿</span>

                    <!-- Banner chip -->
                    <div class="h-12 w-12 rounded-xl overflow-hidden border border-slate-200 bg-slate-100 shrink-0">
                        @if($cat->banner)
                            <img src="{{ asset('storage/' . $cat->banner) }}" class="h-full w-full object-cover" alt="{{ $cat->nome }}">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-xl text-slate-300">🗂️</div>
                        @endif
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="font-black text-slate-800 truncate">{{ $cat->nome }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ $cat->descricao ?: '—' }}</p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <span class="inline-flex items-center gap-1 bg-slate-100 rounded-lg px-3 py-1 text-xs font-bold text-slate-600">
                            📦 {{ $cat->produtos_count }} produto(s)
                        </span>
                        <span class="inline-flex items-center gap-1 {{ $cat->ativo ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} rounded-lg px-3 py-1 text-xs font-bold">
                            {{ $cat->ativo ? '✅ Ativa' : '🔒 Oculta' }}
                        </span>
                        <span class="bg-indigo-50 text-indigo-600 rounded-lg px-3 py-1 text-xs font-bold">#{{ $cat->ordem_exibicao }}</span>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('admin.catalog.categorias.edit', $cat->id) }}" class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-bold text-emerald-600 hover:bg-emerald-100 ring-1 ring-emerald-200 transition">Editar</a>
                        @if(auth()->user()->temPermissao('apagar_categoria'))
                        <form method="POST" action="{{ route('admin.catalog.categorias.destroy', $cat->id) }}" onsubmit="return confirm('Excluir categoria \'{{ $cat->nome }}\'? Só é possível se não houver produtos vinculados.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-lg bg-red-50 px-3 py-2 text-sm font-bold text-red-500 hover:bg-red-100 ring-1 ring-red-200 transition">Apagar</button>
                        </form>
                        @endif
                    </div>
                </li>
            @empty
                <li class="px-6 py-16 text-center">
                    <span class="text-5xl block mb-3 opacity-30">🗂️</span>
                    <span class="font-bold text-slate-600 text-lg block">Nenhuma categoria criada.</span>
                    <span class="text-sm text-slate-400 block">Crie a primeira categoria para organizar sua vitrine.</span>
                </li>
            @endforelse
        </ul>

        @if($categorias->hasPages())
            <div class="border-t border-slate-100 px-6 py-3">
                {{ $categorias->links() }}
            </div>
        @endif
    </div>

    <script>
        // Ordenação por drag-and-drop nativo (HTML5 Drag API)
        const lista = document.getElementById('lista-categorias');
        let dragging = null;

        lista.querySelectorAll('li').forEach(item => {
            item.draggable = true;

            item.addEventListener('dragstart', () => {
                dragging = item;
                setTimeout(() => item.classList.add('opacity-40'), 0);
            });

            item.addEventListener('dragend', () => {
                item.classList.remove('opacity-40');
                dragging = null;
                salvarOrdem();
            });

            item.addEventListener('dragover', e => {
                e.preventDefault();
                const bbox = item.getBoundingClientRect();
                const offset = e.clientY - bbox.top - bbox.height / 2;
                if (dragging && dragging !== item) {
                    lista.insertBefore(dragging, offset > 0 ? item.nextSibling : item);
                }
            });
        });

        function salvarOrdem() {
            const ids = [...lista.querySelectorAll('li[data-id]')].map(li => parseInt(li.dataset.id));
            fetch('{{ route('admin.catalog.categorias.ordenar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ ordem: ids })
            });
        }
    </script>
</x-layouts.app>

