{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.app>
    <div class="mb-8 text-center py-6 bg-gradient-to-r from-blue-700 via-blue-800 to-indigo-900 rounded-2xl shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>
        <div class="relative z-10 px-6">
            <h1 class="text-3xl md:text-4xl font-black text-white mb-3">Como podemos te ajudar hoje?</h1>
            <p class="text-blue-200 text-lg max-w-2xl mx-auto">Explore nossa base de conhecimento, assista tutoriais e aprenda a extrair o máximo do VaptCRM.</p>
            
            <div class="mt-8 max-w-xl mx-auto flex">
                <input type="text" placeholder="Buscar por assunto, ex: 'Configurar PDV'" class="w-full px-5 py-4 rounded-l-xl border-0 focus:ring-4 focus:ring-blue-400/50 text-gray-800 font-medium">
                <button class="bg-amber-500 hover:bg-amber-600 px-6 py-4 rounded-r-xl text-white font-bold transition-colors">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    @if($destaques->count() > 0)
    <div class="mb-12">
        <div class="flex items-center gap-2 mb-6 border-b pb-2">
            <i class="fas fa-fire text-amber-500 text-xl"></i>
            <h2 class="text-2xl font-bold text-slate-800">Tutoriais em Destaque</h2>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($destaques as $video)
                <a href="{{ route('admin.support.help.show', $video) }}" class="group block bg-white rounded-xl shadow-sm border hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="relative aspect-video bg-slate-900 overflow-hidden">
                        @if($video->thumbnail)
                            <img src="{{ $video->thumbnail }}" alt="{{ $video->titulo }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 opacity-80 group-hover:opacity-100">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-slate-700">
                                <i class="fab fa-youtube text-4xl"></i>
                            </div>
                        @endif
                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-black/30 backdrop-blur-sm">
                            <div class="w-16 h-16 rounded-full bg-brand-primary/90 flex items-center justify-center text-white scale-75 group-hover:scale-100 transition-transform duration-300">
                                <i class="fas fa-play text-xl ml-1"></i>
                            </div>
                        </div>
                    </div>
                    <div class="p-5 relative">
                        <div class="absolute -top-3 right-4 bg-amber-500 text-white text-[10px] font-black uppercase tracking-wider px-2 py-1 rounded shadow">Premium</div>
                        <h3 class="font-bold text-lg text-slate-800 group-hover:text-brand-primary transition-colors line-clamp-2">{{ $video->titulo }}</h3>
                        <p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $video->descricao }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif

    <div>
        <div class="flex items-center gap-2 mb-6 border-b pb-2 mt-10">
            <i class="fas fa-play-circle text-blue-500 text-xl"></i>
            <h2 class="text-2xl font-bold text-slate-800">Biblioteca de Treinamentos</h2>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($videos as $video)
                <a href="{{ route('admin.support.help.show', $video) }}" class="group block bg-white rounded-xl shadow-sm border hover:shadow-lg transition-all duration-200">
                    <div class="relative aspect-video bg-slate-100 overflow-hidden rounded-t-xl">
                        @if($video->thumbnail)
                            <img src="{{ $video->thumbnail }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @endif
                        <div class="absolute bottom-2 right-2 bg-black/70 text-white text-[10px] font-bold px-1.5 py-0.5 rounded backdrop-blur">
                            VÍDEO
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-bold text-sm text-slate-800 group-hover:text-brand-primary line-clamp-2 leading-tight">{{ $video->titulo }}</h3>
                    </div>
                </a>
            @endforeach
        </div>
        
        @if($videos->count() === 0 && $destaques->count() === 0)
            <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-dashed border-slate-300">
                <i class="fas fa-video-slash text-5xl text-slate-300 mb-4"></i>
                <h3 class="text-lg font-bold text-slate-600">Nenhum treinamento disponível</h3>
                <p class="text-slate-400">Em breve nossa equipe adicionará novos vídeos tutoriais.</p>
            </div>
        @endif
    </div>

    <div class="mt-16 bg-blue-50 rounded-2xl p-8 border border-blue-100 flex flex-col md:flex-row items-center justify-between gap-6 shadow-inner">
        <div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Ainda precisa de ajuda especializada?</h3>
            <p class="text-slate-600">Nossa equipe de especialistas está pronta para resolver o seu problema técnico.</p>
        </div>
        <a href="{{ route('admin.support.meus-tickets.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-md transition-transform hover:scale-105 shrink-0">
            <i class="fas fa-life-ring mr-2"></i> Abrir Ticket de Suporte
        </a>
    </div>
</x-layouts.app>
