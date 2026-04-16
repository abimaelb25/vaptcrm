{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.app>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Acompanhamento de Ticket</h1>
            <p class="text-slate-500 text-sm">Ticket <strong>{{ $ticket->numero_ticket }}</strong></p>
        </div>
        <a href="{{ route('admin.support.meus-tickets.index') }}" class="text-slate-500 hover:text-brand-primary text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Meus Tickets
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 flex flex-col min-h-[60vh] max-h-[calc(100vh-14rem)] bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            
            {{-- Header Chat --}}
            <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center shrink-0">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">{{ $ticket->assunto }}</h2>
                    <span class="text-xs font-semibold text-slate-500 mt-1 block">Aberto em {{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>

            {{-- Histórico de Chat --}}
            <div class="p-5 flex-1 overflow-y-auto space-y-5 bg-slate-50">
                @foreach($ticket->mensagens as $msg)
                    @continue($msg->interno) {{-- Omissão SEGURANÇA: Cliente não pode NUNCA ver msgs internas --}}
                    
                    @if($msg->autor_tipo === 'cliente')
                        <div class="flex justify-end">
                            <div class="max-w-[85%] bg-brand-primary/10 border border-brand-primary/20 rounded-2xl rounded-tr-sm p-4 shadow-sm">
                                <div class="flex items-center justify-end mb-2 gap-3">
                                    <span class="text-[10px] text-slate-400">{{ $msg->created_at->format('d/m H:i') }}</span>
                                    <span class="text-xs font-bold text-brand-primary">Você ({{ $msg->autorUser->nome ?? 'Usuário' }})</span>
                                </div>
                                <div class="text-sm text-slate-800 whitespace-pre-wrap">{{ $msg->mensagem }}</div>
                                @if($msg->anexo_path)
                                    <div class="mt-3">
                                        <a href="{{ asset('storage/' . $msg->anexo_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $msg->anexo_path) }}" class="rounded shadow-sm max-w-full h-auto max-h-48 cursor-pointer hover:opacity-90">
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="flex justify-start">
                            <div class="max-w-[85%] bg-white border border-slate-200 rounded-2xl rounded-tl-sm p-4 shadow-sm">
                                <div class="flex items-center justify-start mb-2 gap-3">
                                    <div class="w-6 h-6 rounded-full bg-slate-800 flex items-center justify-center text-white shrink-0">
                                        <i class="fas fa-headset text-[10px]"></i>
                                    </div>
                                    <span class="text-xs font-bold text-slate-800">Suporte VaptCRM</span>
                                    <span class="text-[10px] text-slate-400">{{ $msg->created_at->format('d/m H:i') }}</span>
                                </div>
                                <div class="text-sm text-slate-700 whitespace-pre-wrap">{{ $msg->mensagem }}</div>
                                @if($msg->anexo_path)
                                    <div class="mt-3 relative inline-block">
                                        <a href="{{ asset('storage/' . $msg->anexo_path) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $msg->anexo_path) }}" class="rounded shadow border border-slate-200 max-w-full h-auto max-h-48 cursor-zoom-in hover:opacity-95">
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            {{-- Resposta Form --}}
            @if(in_array($ticket->status, ['resolvido', 'fechado']))
                <div class="p-6 bg-slate-100 border-t border-slate-200 text-center shrink-0">
                    <p class="text-slate-600 font-medium"><i class="fas fa-lock mr-2 text-slate-400"></i> Este ticket encontra-se bloqueado para novas respostas, pois foi classificado como <strong>{{ strtoupper($ticket->status) }}</strong>.</p>
                </div>
            @else
                <div class="p-5 bg-white border-t border-slate-100 shrink-0">
                    <form action="{{ route('admin.support.meus-tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="flex flex-col gap-3">
                            <div class="flex gap-4">
                                <div class="w-10 h-10 rounded-full bg-brand-primary flex items-center justify-center font-bold text-white shrink-0">
                                    {{ substr(auth()->user()->nome, 0, 1) }}
                                </div>
                                <div class="flex-1 relative">
                                    <textarea name="mensagem" required rows="3" class="w-full pl-4 pr-16 py-3 border-slate-300 bg-slate-50/50 rounded-xl shadow-inner focus:border-brand-primary focus:ring-brand-primary focus:bg-white text-sm" placeholder="Escreva sua mensagem detalhada aqui..."></textarea>
                                    <button type="submit" class="absolute bottom-3 right-3 w-10 h-10 rounded-lg bg-slate-800 hover:bg-slate-900 text-white flex items-center justify-center transition-colors shadow">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="pl-14">
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest cursor-pointer hover:text-brand-primary inline-flex items-center gap-1 group">
                                    <i class="fas fa-paperclip text-slate-400 group-hover:text-brand-primary"></i> Anexar Captura de Tela (Opcional)
                                    <input type="file" name="anexo" accept="image/png, image/jpeg, image/gif, image/webp" class="hidden" onchange="document.getElementById('anexo-preview').textContent = this.files.length > 0 ? this.files[0].name : '';">
                                </label>
                                <span id="anexo-preview" class="ml-2 text-xs text-green-600 font-medium"></span>
                            </div>
                        </div>
                    </form>
                </div>
            @endif
        </div>

        {{-- Lateral Panel Info --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-slate-200">
                <h3 class="font-bold text-slate-800 pb-3 border-b flex items-center gap-2">
                    <i class="fas fa-info-circle text-brand-primary"></i> Detalhes
                </h3>
                <div class="space-y-4 mt-4">
                    <div>
                        <span class="block text-slate-400 text-xs font-bold uppercase tracking-wider mb-1">Status</span>
                        @php
                            $statusColors = [
                                'aberto' => 'bg-indigo-100 text-indigo-800',
                                'aguardando_suporte' => 'bg-blue-100 text-blue-800',
                                'aguardando_cliente' => 'bg-amber-100 text-amber-800 border border-amber-300',
                                'resolvido' => 'bg-emerald-100 text-emerald-800',
                                'fechado' => 'bg-slate-100 text-slate-600',
                            ];
                        @endphp
                        <span class="inline-block px-3 py-1 text-xs font-black uppercase rounded bg-slate-100 {{ $statusColors[$ticket->status] ?? '' }}">
                            {{ str_replace('_', ' ', $ticket->status) }}
                        </span>
                        
                        @if($ticket->status === 'aguardando_cliente')
                            <p class="text-xs text-amber-600 font-medium mt-2">Aguardamos o seu retorno para prosseguirmos com o atendimento.</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
                <h4 class="font-bold text-blue-900 text-sm mb-2"><i class="fas fa-video-camera mr-1 text-blue-500"></i> Consulte a VaptAcademy</h4>
                <p class="text-xs text-blue-800 mb-3 leading-relaxed">Muitas dúvidas do dia a dia já estão resolvidas em formato de vídeo rápido. Antes de mais nada, explore nosso acervo.</p>
                <a href="{{ route('admin.support.help.index') }}" class="block text-center text-xs font-bold uppercase transition bg-white text-blue-700 py-2 rounded-lg border border-blue-200 shadow-sm hover:bg-blue-600 hover:text-white">
                    Ir para VaptAcademy
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
