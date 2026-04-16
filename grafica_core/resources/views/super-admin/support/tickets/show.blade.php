{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.super-admin>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Visualização de Ticket Global</h1>
            <p class="text-gray-500 text-sm">Analisando e respondendo ticket da loja: <strong>{{ $ticket->loja->nome_fantasia ?? 'Desconhecida' }}</strong></p>
        </div>
        <a href="{{ route('superadmin.support.tickets.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
            <i class="fas fa-arrow-left mr-1"></i> Voltar à Lista
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 flex flex-col h-[calc(100vh-14rem)] bg-white rounded-lg shadow overflow-hidden border">
            {{-- Header Ticket --}}
            <div class="p-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <div>
                    <span class="text-xs font-bold bg-gray-200 text-gray-600 px-2 py-1 rounded">{{ $ticket->numero_ticket }}</span>
                    <h2 class="text-xl font-bold text-gray-800 mt-2">{{ $ticket->assunto }}</h2>
                </div>
                <div class="text-right text-xs text-gray-500">
                    Aberto em: {{ $ticket->created_at->format('d/m/Y H:i') }}
                </div>
            </div>

            {{-- Chat History --}}
            <div class="p-4 flex-1 overflow-y-auto space-y-4 bg-slate-50">
                @foreach($ticket->mensagens as $msg)
                    <div class="flex {{ $msg->autor_tipo === 'cliente' ? 'justify-start' : 'justify-end' }}">
                        <div class="max-w-[75%] rounded-lg p-4 shadow-sm {{ $msg->interno ? 'bg-amber-50 border border-amber-200' : ($msg->autor_tipo === 'cliente' ? 'bg-white border' : 'bg-indigo-50 border border-indigo-100') }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-bold {{ $msg->autor_tipo === 'cliente' ? 'text-gray-700' : 'text-indigo-700' }}">
                                    @if($msg->interno) <i class="fas fa-lock text-amber-500"></i> NOTA INTERNA - @endif
                                    {{ $msg->nome_autor }}
                                </span>
                                <span class="text-[10px] text-gray-400 ml-4">{{ $msg->created_at->format('d/m H:i') }}</span>
                            </div>
                            <div class="text-sm text-gray-800 whitespace-pre-wrap">{{ $msg->mensagem }}</div>
                            @if($msg->anexo_path)
                                <div class="mt-3">
                                    <a href="{{ asset('storage/' . $msg->anexo_path) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $msg->anexo_path) }}" class="rounded shadow-sm max-w-full h-auto max-h-48 cursor-zoom-in hover:opacity-90">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Resposta Form --}}
            <div class="p-4 bg-white border-t">
                <form action="{{ route('superadmin.support.tickets.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex gap-4">
                        <div class="flex-1">
                            <textarea name="mensagem" required rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" placeholder="Digite sua resposta..."></textarea>
                            
                            <div class="mt-2 pl-1">
                                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-widest cursor-pointer hover:text-indigo-600 inline-flex items-center gap-1 group">
                                    <i class="fas fa-paperclip text-slate-400 group-hover:text-indigo-500"></i> Anexar Captura de Tela / Arquivo
                                    <input type="file" name="anexo" accept="image/png, image/jpeg, image/gif, image/webp" class="hidden" onchange="document.getElementById('anexo-preview-master').textContent = this.files.length > 0 ? this.files[0].name : '';">
                                </label>
                                <span id="anexo-preview-master" class="ml-2 text-xs text-green-600 font-medium"></span>
                            </div>

                            <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-50">
                                <label class="flex items-center text-sm text-amber-700 font-semibold cursor-pointer">
                                    <input type="checkbox" name="interno" value="1" class="rounded text-amber-600 focus:ring-amber-500 mr-2 shadow-sm">
                                    Apenas Nota Interna (Cliente não vê)
                                </label>
                                <div class="flex items-center gap-3">
                                    <select name="status" class="border-gray-300 rounded text-sm text-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="aguardando_cliente" {{ $ticket->status == 'aguardando_cliente' ? 'selected' : '' }}>Aguardando Cliente</option>
                                        <option value="resolvido">Marcar como Resolvido</option>
                                        <option value="fechado">Fechar Definitivamente</option>
                                        <option value="aberto" {{ $ticket->status == 'aberto' ? 'selected' : '' }}>Deixar Aberto</option>
                                    </select>
                                    <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded shadow font-semibold hover:bg-indigo-700">Enviar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Lateral Panel Info --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-5 border border-gray-100">
                <h3 class="font-bold text-gray-800 border-b pb-2 mb-3">Informações do Chamado</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="block text-gray-500 text-xs font-semibold">Loja Alvo</span>
                        <div class="font-medium text-gray-900">{{ $ticket->loja->nome_fantasia ?? 'N/D' }}</div>
                    </div>
                    <div>
                        <span class="block text-gray-500 text-xs font-semibold">Cliente Solicitante</span>
                        <div class="font-medium text-gray-900">{{ $ticket->user->nome ?? 'N/D' }}</div>
                        <div class="text-xs text-gray-400">{{ $ticket->user->email ?? '' }}</div>
                    </div>
                    <div>
                        <span class="block text-gray-500 text-xs font-semibold">Categoria</span>
                        <div>{{ $ticket->categoria->nome ?? 'Sem categoria' }}</div>
                    </div>
                    <div>
                        <span class="block text-gray-500 text-xs font-semibold">Status Atual</span>
                        <span class="uppercase tracking-wider text-[10px] font-bold px-2 py-1 rounded bg-slate-100">{{ str_replace('_', ' ', $ticket->status) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.super-admin>
