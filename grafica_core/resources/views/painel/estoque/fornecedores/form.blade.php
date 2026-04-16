{{--
Autoria: Abimael Borges
Site: https://abimaelborges.adv.br
Data: 2026-04-15 19:30
--}}
@php $isEdit = $fornecedor->exists; @endphp
<x-layouts.app>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-black text-brand-secondary">
                {{ $isEdit ? 'Editar' : 'Novo' }} <span class="text-brand-primary">Fornecedor</span>
            </h1>
            <p class="text-slate-500 font-medium">Cadastre contatos e dados fiscais de seus fornecedores.</p>
        </div>
        <a href="{{ route('admin.inventory.fornecedores.index') }}" class="text-sm font-bold text-slate-400 hover:text-brand-primary transition">← Voltar para listagem</a>
    </div>

    <div class="max-w-4xl rounded-3xl bg-white p-8 shadow-2xl border border-slate-100">
        <form action="{{ $isEdit ? route('admin.inventory.fornecedores.update', $fornecedor) : route('admin.inventory.fornecedores.store') }}" method="POST" class="space-y-8">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <!-- Dados Básicos -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Nome Comercial / Nome Fantasia <span class="text-red-500">*</span></label>
                    <input type="text" name="nome" value="{{ old('nome', $fornecedor->nome) }}" required class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Razão Social</label>
                    <input type="text" name="razao_social" value="{{ old('razao_social', $fornecedor->razao_social) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">CPF / CNPJ</label>
                    <input type="text" name="cnpj_cpf" value="{{ old('cnpj_cpf', $fornecedor->cnpj_cpf) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                </div>

                @if($isEdit)
                <div>
                    <label class="block text-xs font-black text-slate-400 uppercase mb-2">Status</label>
                    <select name="ativo" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                        <option value="1" {{ $fornecedor->ativo ? 'selected' : '' }}>Ativo</option>
                        <option value="0" {{ !$fornecedor->ativo ? 'selected' : '' }}>Inativo</option>
                    </select>
                </div>
                @endif
            </div>

            <!-- Contato -->
            <div class="border-t border-slate-100 pt-8">
                <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                    <span class="bg-emerald-100 p-2 rounded-lg text-emerald-600">📞</span> Contato
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-2">E-mail</label>
                        <input type="email" name="email" value="{{ old('email', $fornecedor->email) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-2">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $fornecedor->whatsapp) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    </div>
                </div>
            </div>

            <!-- Localização -->
            <div class="border-t border-slate-100 pt-8">
                <h3 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-2">
                    <span class="bg-slate-100 p-2 rounded-lg text-slate-600">📍</span> Localização
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-black text-slate-400 uppercase mb-2">Cidade</label>
                        <input type="text" name="cidade" value="{{ old('cidade', $fornecedor->cidade) }}" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary">
                    </div>
                    <div>
                        <label class="block text-xs font-black text-slate-400 uppercase mb-2">UF</label>
                        <input type="text" name="uf" value="{{ old('uf', $fornecedor->uf) }}" maxlength="2" class="w-full rounded-xl border-slate-200 bg-slate-50 focus:ring-brand-primary uppercase">
                    </div>
                </div>
            </div>

            <div class="pt-8 flex justify-end">
                <button type="submit" class="rounded-2xl bg-brand-primary px-12 py-5 text-center font-black text-white shadow-xl hover:-translate-y-1 transition uppercase tracking-widest text-sm">
                    {{ $isEdit ? 'Salvar Alterações' : 'Cadastrar Fornecedor' }}
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
