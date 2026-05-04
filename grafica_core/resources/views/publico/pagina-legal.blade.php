{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 23:55
--}}
<x-layouts.publico titulo="{{ $pagina->titulo }} - {{ $configSite['empresa_nome'] ?? 'Gráfica' }}">
    <x-public.breadcrumb :items="[
        ['label' => 'Início', 'url' => \App\Support\PublicUrlHelper::inicio()],
        ['label' => $pagina->titulo],
    ]" />

    <main class="public-card my-4 min-h-[60vh] max-w-4xl rounded-2xl border border-slate-100 px-5 py-7 shadow-xl sm:my-8 sm:px-8 sm:py-10 md:my-10 md:rounded-[3rem] md:px-12 md:py-12">
        
        <header class="mb-8 border-b border-slate-100 pb-7 text-center sm:mb-12 sm:pb-10">
            <span class="inline-block bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-lg mb-4">Institucional</span>
            <h1 class="text-3xl font-black tracking-tight text-brand-secondary sm:text-4xl md:text-5xl">{{ $pagina->titulo }}</h1>
            @if($pagina->resumo)
                <p class="mx-auto mt-3 max-w-2xl text-base font-medium text-slate-500 sm:mt-4 sm:text-lg">{{ $pagina->resumo }}</p>
            @endif
        </header>

        <article class="prose prose-slate prose-lg md:prose-xl max-w-none prose-headings:font-black prose-headings:text-brand-secondary prose-li:marker:text-brand-primary prose-a:text-brand-primary hover:prose-a:text-brand-secondary transition-all">
            {!! $pagina->conteudo !!}
        </article>

        <footer class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-slate-100 pt-6 text-center sm:mt-16 sm:flex-row sm:gap-4 sm:pt-8 sm:text-left">
            <div class="text-xs text-slate-400 font-bold uppercase tracking-widest">
                {{ $configSite['empresa_nome'] ?? 'Gráfica' }} • CNPJ: {{ $configSite['empresa_cnpj'] ?? 'Não informado' }}
            </div>
            <a href="{{ route('site.inicio') }}" class="inline-flex items-center gap-2 text-sm font-bold text-brand-primary hover:text-brand-secondary transition-colors">
                <x-icon name="arrow-left" class="w-4 h-4" /> Voltar à Loja
            </a>
        </footer>
    </main>
</x-layouts.publico>
