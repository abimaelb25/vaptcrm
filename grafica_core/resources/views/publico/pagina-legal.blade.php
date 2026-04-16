{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 23:55
--}}
<x-layouts.publico titulo="{{ $pagina->titulo }} - {{ $configSite['empresa_nome'] ?? 'Gráfica' }}">
    <main class="max-w-4xl mx-auto py-12 px-6 sm:px-12 bg-white min-h-[60vh] rounded-[3rem] shadow-xl border border-slate-100 my-10">
        
        <header class="mb-12 border-b border-slate-100 pb-10 text-center">
            <span class="inline-block bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-lg mb-4">Institucional</span>
            <h1 class="text-4xl md:text-5xl font-black text-brand-secondary tracking-tight">{{ $pagina->titulo }}</h1>
            @if($pagina->resumo)
                <p class="mt-4 text-slate-500 font-medium text-lg max-w-2xl mx-auto">{{ $pagina->resumo }}</p>
            @endif
        </header>

        <article class="prose prose-slate prose-lg md:prose-xl max-w-none prose-headings:font-black prose-headings:text-brand-secondary prose-li:marker:text-brand-primary prose-a:text-brand-primary hover:prose-a:text-brand-secondary transition-all">
            {!! $pagina->conteudo !!}
        </article>

        <footer class="mt-16 border-t border-slate-100 pt-8 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-widest">
                {{ $configSite['empresa_nome'] ?? 'Gráfica' }} • CNPJ: {{ $configSite['empresa_cnpj'] ?? 'Não informado' }}
            </div>
            <a href="{{ route('site.inicio') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-primary hover:text-brand-secondary transition-colors">
                <x-icon name="arrow-left" class="w-4 h-4" /> Voltar à Loja
            </a>
        </footer>
    </main>
</x-layouts.publico>
