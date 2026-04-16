{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:45
--}}
@php $isEdit = $asset->exists; @endphp
<x-layouts.app>
    <div class="mb-12 max-w-4xl mx-auto flex items-center justify-between">
        <div>
            <h1 class="text-4xl font-black text-brand-secondary tracking-tighter">
                {{ $isEdit ? 'Editar' : 'Cadastrar' }} <span class="text-brand-primary">Ativo</span>
            </h1>
            <p class="text-slate-500 font-medium">Controle patrimonial e depreciativo de maquinário.</p>
        </div>
        <a href="{{ route('admin.inventory.assets.index') }}" class="text-sm font-black text-slate-400 hover:text-brand-primary transition uppercase tracking-widest border border-slate-200 px-5 py-2.5 rounded-2xl bg-white shadow-sm">← Gestão</a>
    </div>

    <div class="max-w-4xl mx-auto rounded-[3rem] bg-white p-12 shadow-2xl border-4 border-slate-50 relative overflow-hidden">
        <!-- Detalhe decorativo premium -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-brand-primary/5 rounded-full blur-3xl animate-pulse"></div>

        <form action="{{ $isEdit ? route('admin.inventory.assets.update', $asset) : route('admin.inventory.assets.store') }}" method="POST" class="space-y-12">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <!-- 1. IDENTIFICAÇÃO TÉCNICA -->
            <div class="space-y-8">
                <h3 class="text-xl font-black text-slate-800 flex items-center gap-3 border-b border-slate-100 pb-4">
                    <span class="p-3 bg-brand-primary/10 rounded-2xl text-brand-primary text-2xl">📋</span> Identificação Técnica
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Nome do Equipamento <span class="text-red-500">*</span></label>
                        <input type="text" name="nome" value="{{ old('nome', $asset->nome) }}" required placeholder="Ex: Impressora Digital Xerox Versant 180" class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:ring-brand-primary text-lg font-black text-slate-800 px-6 py-4">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Tipo <span class="text-red-500">*</span></label>
                        <select name="tipo" required class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:ring-brand-primary font-bold text-slate-700 px-6 py-4">
                            <option value="">Selecione o tipo...</option>
                            @foreach(['impressora', 'plotter', 'corte', 'acabamento', 'veiculo', 'informatica', 'outro'] as $tipo)
                                <option value="{{ $tipo }}" {{ old('tipo', $asset->tipo) == $tipo ? 'selected' : '' }}>{{ ucfirst($tipo) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Setor Operacional</label>
                        <input type="text" name="setor" value="{{ old('setor', $asset->setor) }}" placeholder="Ex: Produção Interna, Logística" class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:ring-brand-primary font-bold text-slate-700 px-6 py-4">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Marca</label>
                        <input type="text" name="marca" value="{{ old('marca', $asset->marca) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:ring-brand-primary font-bold text-slate-700 px-6 py-4">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Modelo</label>
                        <input type="text" name="modelo" value="{{ old('modelo', $asset->modelo) }}" class="w-full rounded-2xl border-slate-200 bg-slate-50 focus:ring-brand-primary font-bold text-slate-700 px-6 py-4">
                    </div>
                </div>
            </div>

            <!-- 2. FINANCEIRO E DEPRECIAÇÃO -->
            <div class="space-y-8 bg-slate-50 p-10 rounded-[2.5rem] border-2 border-dashed border-slate-200">
                <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                    <span class="p-3 bg-brand-secondary/10 rounded-2xl text-brand-secondary text-2xl">💰</span> Valores e Depreciação
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Data de Aquisição <span class="text-red-500">*</span></label>
                        <input type="date" name="data_aquisicao" value="{{ old('data_aquisicao', $asset->data_aquisicao ? $asset->data_aquisicao->format('Y-m-d') : '') }}" required class="w-full rounded-2xl border-slate-200 bg-white focus:ring-brand-primary font-bold text-slate-700 px-6 py-4">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Valor de Aquisição (R$) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="valor_aquisicao" value="{{ old('valor_aquisicao', $asset->valor_aquisicao) }}" required class="w-full rounded-2xl border-slate-200 bg-white focus:ring-brand-primary text-xl font-black text-slate-800 px-6 py-4">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Vida Útil (Meses) <span class="text-red-500">*</span></label>
                        <input type="number" name="vida_util_meses" value="{{ old('vida_util_meses', $asset->vida_util_meses) }}" required class="w-full rounded-2xl border-slate-200 bg-white focus:ring-brand-primary text-xl font-black text-slate-800 px-6 py-4">
                    </div>
                </div>
                <div class="p-6 bg-white rounded-2xl border border-slate-200">
                    <p class="text-[10px] font-black text-slate-300 uppercase underline decoration-brand-primary mb-2">Resumo de Depreciação Linear</p>
                    <p class="text-xs font-bold text-slate-500 leading-relaxed italic italic">O sistema calculará automaticamente a depreciação mensal baseada na vida útil, auxiliando na precificação de custos de máquina por hora/trabalho no futuro.</p>
                </div>
            </div>

            <div class="pt-12 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-8 gap-y-12">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Status Inicial</label>
                    <div class="flex flex-wrap gap-4">
                        @foreach(['ativo' => 'Ativo', 'manutencao' => 'Manutenção', 'inativo' => 'Inativo'] as $key => $lbl)
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <input type="radio" name="status" value="{{ $key }}" {{ old('status', $asset->status ?? 'ativo') == $key ? 'checked' : '' }} class="sr-only peer">
                            <div class="px-6 py-4 rounded-2xl border-2 border-slate-100 bg-slate-50 peer-checked:border-brand-primary peer-checked:bg-brand-primary/5 transition-all text-xs font-black text-slate-400 peer-checked:text-brand-primary uppercase">
                                {{ $lbl }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <button type="submit" class="w-full rounded-3xl bg-slate-900 py-6 text-center font-black text-white shadow-2xl hover:bg-brand-primary transition-all duration-500 uppercase tracking-[0.2em] text-sm">
                        {{ $isEdit ? 'Atualizar Equipamento' : 'Efetivar Cadastro Patrimonial' }}
                    </button>
                    <p class="mt-4 text-center text-[10px] font-black text-slate-300 uppercase tracking-widest">Responsabilidade e Auditoria: {{ auth()->user()->nome }}</p>
                </div>
            </div>
        </form>
    </div>
</x-layouts.app>
