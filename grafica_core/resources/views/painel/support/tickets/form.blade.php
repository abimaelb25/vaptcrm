{{-- Autoria: Abimael Borges | https://abimaelborges.adv.br | Data: 2026-04-16 --}}
<x-layouts.app>
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Abertura de Ticket de Suporte</h1>
            <p class="text-slate-500 text-sm">Relate o seu problema ou dúvida de forma detalhada para agilizarmos a solução.</p>
        </div>
        <a href="{{ route('admin.support.meus-tickets.index') }}" class="text-slate-500 hover:text-slate-700 font-medium text-sm flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Voltar à Lista
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden max-w-3xl">
        <form action="{{ route('admin.support.meus-tickets.store') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-6">
            @csrf
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Qual é o assunto principal?</label>
                    <input type="text" name="assunto" required value="{{ old('assunto') }}" placeholder="Ex: Erro ao tentar fechar fluxo de caixa" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-brand-primary focus:border-brand-primary">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Categoria</label>
                        <select name="categoria_id" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-brand-primary focus:border-brand-primary">
                            <option value="">Selecione uma categoria...</option>
                            @foreach($categorias as $cat)
                                <option value="{{ $cat->id }}" {{ old('categoria_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Prioridade</label>
                        <select name="prioridade" required class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-brand-primary focus:border-brand-primary">
                            <option value="baixa" {{ old('prioridade') == 'baixa' ? 'selected' : '' }}>Baixa - Dúvida geral</option>
                            <option value="media" {{ old('prioridade', 'media') == 'media' ? 'selected' : '' }}>Média - Sistema funcionando, mas com ressalvas</option>
                            <option value="alta" {{ old('prioridade') == 'alta' ? 'selected' : '' }}>Alta - Funcionalidade importante paralisada</option>
                            <option value="urgente" {{ old('prioridade') == 'urgente' ? 'selected' : '' }}>Urgente - Sistema totalmente inoperante</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Descreva os Detalhes</label>
                    <p class="text-xs text-slate-500 mb-2">Por favor, descreva o que ocorreu, que tela você estava e se exibiu alguma mensagem de erro.</p>
                    <textarea name="mensagem" required rows="6" class="w-full border-slate-300 rounded-lg shadow-sm focus:ring-brand-primary focus:border-brand-primary" placeholder="Olá equipe de suporte, eu estava tentando..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-1">Anexar Captura de Tela <span class="text-xs text-slate-400 font-normal">(Opcional)</span></label>
                    <input type="file" name="anexo" accept="image/png, image/jpeg, image/gif, image/webp" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    <p class="text-[10px] text-slate-400 mt-1">Imagens no formato PNG, JPG ou GIF até 5MB.</p>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="bg-brand-primary hover:bg-orange-600 text-white font-bold py-3 px-8 rounded-xl shadow transition-colors w-full md:w-auto">
                    Enviar Ticket
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
