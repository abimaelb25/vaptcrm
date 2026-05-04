{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-17
Descrição: Gestão de Contas Bancárias (CRUD).
--}}
<x-layouts.app>
    <div class="mb-8 flex flex-col sm:flex-row items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">Contas Bancárias</h1>
            <p class="text-slate-500 font-medium">Gerencie caixa, contas bancárias e carteiras digitais.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openModal('modal-nova-conta')" class="rounded-xl bg-gradient-to-r from-brand-primary to-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:scale-105 flex items-center gap-2">
                <span>➕</span> Nova Conta
            </button>
        </div>
    </div>

    <!-- Alertas -->
    @if(session('sucesso'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 flex items-center gap-3">
            <span class="text-emerald-600 text-xl">✅</span>
            <p class="text-sm font-bold text-emerald-700">{{ session('sucesso') }}</p>
        </div>
    @endif

    @if(session('erro'))
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 p-4 flex items-center gap-3">
            <span class="text-red-600 text-xl">⚠️</span>
            <p class="text-sm font-bold text-red-700">{{ session('erro') }}</p>
        </div>
    @endif

    <!-- Cards de Contas -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($contas as $conta)
            <div class="rounded-2xl bg-white border {{ $conta->ativo ? 'border-slate-200' : 'border-red-200 bg-red-50/30' }} shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-xl flex items-center justify-center text-2xl
                                @if($conta->tipo === 'caixa') bg-emerald-100
                                @elseif($conta->tipo === 'banco') bg-blue-100
                                @elseif($conta->tipo === 'digital') bg-purple-100
                                @else bg-slate-100 @endif">
                                @if($conta->tipo === 'caixa') 💵
                                @elseif($conta->tipo === 'banco') 🏦
                                @elseif($conta->tipo === 'digital') 📱
                                @else 💳 @endif
                            </div>
                            <div>
                                <h3 class="font-black text-slate-800 {{ !$conta->ativo ? 'line-through opacity-50' : '' }}">{{ $conta->nome }}</h3>
                                <span class="text-[10px] font-bold uppercase tracking-widest {{ $conta->ativo ? 'text-slate-400' : 'text-red-500' }}">
                                    {{ $conta->tipo }} {{ !$conta->ativo ? '• INATIVA' : '' }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-1">
                            <button onclick="editarConta({{ $conta->id }}, '{{ $conta->nome }}', '{{ $conta->tipo }}', {{ $conta->saldo_inicial }}, {{ $conta->ativo ? 'true' : 'false' }})" 
                                    class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition" title="Editar">
                                ✏️
                            </button>
                            @if($conta->pagamentos_count === 0)
                                <form action="{{ route('admin.finance.accounts.destroy', $conta) }}" method="POST" class="inline" onsubmit="return confirm('Excluir esta conta?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition" title="Excluir">
                                        🗑️
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl">
                            <span class="text-xs font-bold text-slate-400 uppercase">Saldo Inicial</span>
                            <span class="text-sm font-black text-slate-600">R$ {{ number_format($conta->saldo_inicial, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-xl {{ $conta->saldo_calculado >= 0 ? 'bg-emerald-50' : 'bg-red-50' }}">
                            <span class="text-xs font-bold {{ $conta->saldo_calculado >= 0 ? 'text-emerald-600' : 'text-red-600' }} uppercase">Saldo Atual</span>
                            <span class="text-lg font-black {{ $conta->saldo_calculado >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                                R$ {{ number_format($conta->saldo_calculado, 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span>{{ $conta->pagamentos_count }} pagamentos vinculados</span>
                            <form action="{{ route('admin.finance.accounts.toggle', $conta) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-bold {{ $conta->ativo ? 'text-orange-500 hover:text-orange-600' : 'text-emerald-500 hover:text-emerald-600' }} transition">
                                    {{ $conta->ativo ? 'Desativar' : 'Reativar' }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full flex flex-col items-center justify-center py-16 bg-white rounded-2xl border border-slate-100">
                <span class="text-6xl mb-4 opacity-30">🏦</span>
                <p class="text-lg font-black text-slate-600">Nenhuma conta cadastrada</p>
                <p class="text-sm text-slate-400 mt-1">Clique em "Nova Conta" para começar.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Nova Conta -->
    <div id="modal-nova-conta" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="bg-slate-800 px-6 py-4 flex items-center justify-between">
                <h3 class="font-black text-white uppercase tracking-widest text-sm">Nova Conta Bancária</h3>
                <button onclick="closeModal('modal-nova-conta')" class="text-white opacity-50 hover:opacity-100 transition">✕</button>
            </div>
            <form action="{{ route('admin.finance.accounts.store') }}" method="POST" class="p-6 space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Nome da Conta</label>
                    <input type="text" name="nome" required placeholder="Ex: Caixa Principal, Banco do Brasil, Nubank..." class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Tipo</label>
                    <select name="tipo" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        <option value="caixa">💵 Caixa Físico</option>
                        <option value="banco">🏦 Conta Bancária</option>
                        <option value="digital">📱 Carteira Digital</option>
                        <option value="carteira">💳 Outros</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Saldo Inicial (R$)</label>
                    <input type="number" step="0.01" name="saldo_inicial" value="0" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    <p class="text-[10px] text-slate-400 mt-1">Informe o saldo existente nesta conta no momento do cadastro.</p>
                </div>

                <button type="submit" class="w-full rounded-xl bg-brand-primary py-3 font-black uppercase tracking-widest text-white shadow-lg transition hover:bg-orange-600">
                    Cadastrar Conta
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Editar Conta -->
    <div id="modal-editar-conta" class="fixed inset-0 z-[60] hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
            <div class="bg-slate-800 px-6 py-4 flex items-center justify-between">
                <h3 class="font-black text-white uppercase tracking-widest text-sm">Editar Conta</h3>
                <button onclick="closeModal('modal-editar-conta')" class="text-white opacity-50 hover:opacity-100 transition">✕</button>
            </div>
            <form id="form-editar-conta" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Nome da Conta</label>
                    <input type="text" name="nome" id="edit-nome" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Tipo</label>
                    <select name="tipo" id="edit-tipo" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        <option value="caixa">💵 Caixa Físico</option>
                        <option value="banco">🏦 Conta Bancária</option>
                        <option value="digital">📱 Carteira Digital</option>
                        <option value="carteira">💳 Outros</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-1">Saldo Inicial (R$)</label>
                    <input type="number" step="0.01" name="saldo_inicial" id="edit-saldo" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="ativo" id="edit-ativo" value="1" class="rounded text-brand-primary focus:ring-brand-primary">
                    <label for="edit-ativo" class="text-sm font-bold text-slate-600">Conta Ativa</label>
                </div>

                <button type="submit" class="w-full rounded-xl bg-brand-secondary py-3 font-black uppercase tracking-widest text-white shadow-lg transition hover:bg-slate-700">
                    Salvar Alterações
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }

        function editarConta(id, nome, tipo, saldo, ativo) {
            document.getElementById('form-editar-conta').action = `/admin/gestao-financeira/contas/${id}`;
            document.getElementById('edit-nome').value = nome;
            document.getElementById('edit-tipo').value = tipo;
            document.getElementById('edit-saldo').value = saldo;
            document.getElementById('edit-ativo').checked = ativo;
            openModal('modal-editar-conta');
        }

        // Fechar modal ao clicar fora
        document.querySelectorAll('.fixed').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) closeModal(this.id);
            });
        });
    </script>
    @endpush
</x-layouts.app>
