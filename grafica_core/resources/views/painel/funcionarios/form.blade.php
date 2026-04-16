@php
/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-15 19:45
| Descrição: Formulário profissional de Colaborador (Evolução RH Completa).
*/
@endphp
<x-layouts.app titulo="{{ $funcionario->exists ? 'Editar Colaborador' : 'Novo Colaborador' }} - Vapt RH">
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.system.equipe.index') }}" class="text-xs font-black text-slate-400 hover:text-brand-primary mb-2 inline-flex items-center gap-2 uppercase tracking-tighter transition-colors">
                <i class="fas fa-arrow-left"></i> Voltar para Time
            </a>
            <h1 class="text-3xl font-black text-brand-secondary tracking-tight">{{ $funcionario->exists ? 'Ficha do Colaborador' : 'Novo Colaborador' }}</h1>
            <p class="text-slate-500 font-medium">Cadastre ou edite as informações completas do funcionário.</p>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl animate-in shake duration-500">
            <ul class="list-disc list-inside text-sm text-red-600 font-bold">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-8" x-data="{ tab: 'pessoais', criarAcesso: {{ old('criar_acesso', 0) }} }">
        
        <!-- Sidebar de Navegação Superior -->
        <aside class="w-full lg:w-72 shrink-0">
            <nav class="sticky top-6 space-y-2">
                <button @click="tab = 'pessoais'" :class="tab === 'pessoais' ? 'bg-brand-primary text-white shadow-xl shadow-brand-primary/20 translate-x-1' : 'bg-white text-slate-600 hover:bg-slate-50'" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl font-black text-sm transition-all border border-slate-200/50 text-left">
                    <i class="fas fa-user-circle w-5 text-lg"></i> <span>1. Dados Pessoais</span>
                </button>
                <button @click="tab = 'trabalhistas'" :class="tab === 'trabalhistas' ? 'bg-brand-primary text-white shadow-xl shadow-brand-primary/20 translate-x-1' : 'bg-white text-slate-600 hover:bg-slate-50'" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl font-black text-sm transition-all border border-slate-200/50 text-left">
                    <i class="fas fa-briefcase w-5 text-lg"></i> <span>2. Trabalhistas</span>
                </button>
                <button @click="tab = 'acesso'" :class="tab === 'acesso' ? 'bg-brand-primary text-white shadow-xl shadow-brand-primary/20 translate-x-1' : 'bg-white text-slate-600 hover:bg-slate-50'" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl font-black text-sm transition-all border border-slate-200/50 text-left">
                    <i class="fas fa-key w-5 text-lg"></i> <span>3. Acesso Sistema</span>
                </button>
                @if($funcionario->exists)
                <div class="pt-4 mt-4 border-t border-slate-200">
                    <p class="px-6 text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Módulos Pro</p>
                    <a href="{{ route('admin.system.equipe.show', $funcionario->id) }}#documentos" class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl bg-white text-slate-400 hover:text-brand-primary transition-all border border-slate-200/50 text-left opacity-60">
                        <i class="fas fa-folder-open w-5"></i> <span class="text-sm font-bold">Documentos</span>
                    </a>
                </div>
                @endif
            </nav>
        </aside>

        <!-- Formulário Principal -->
        <div class="flex-1">
            <form action="{{ $funcionario->exists ? route('admin.system.equipe.update', $funcionario->id) : route('admin.system.equipe.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if($funcionario->exists) @method('PUT') @endif

                <!-- SECTION 1: PESSOAL -->
                <section x-show="tab === 'pessoais'" class="space-y-6 animate-in slide-in-from-bottom-2 duration-300">
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden p-8">
                        <h2 class="text-xl font-black text-slate-800 mb-8 border-b border-slate-100 pb-4 flex items-center gap-3">
                           <i class="fas fa-user-tag text-brand-primary text-sm"></i> Cadastro de Pessoa Física
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Nome Completo (Civil) *</label>
                                <input type="text" name="nome_completo" value="{{ old('nome_completo', $funcionario->nome_completo) }}" required placeholder="Ex: João da Silva Santos" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm bg-slate-50/30 focus:bg-white transition-all">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">CPF</label>
                                <input type="text" name="cpf" value="{{ old('cpf', $funcionario->cpf) }}" placeholder="000.000.000-00" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">RG / Org. Emissor</label>
                                <div class="flex gap-2">
                                    <input type="text" name="rg" value="{{ old('rg', $funcionario->rg) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                                    <input type="text" name="orgao_emissor" value="{{ old('orgao_emissor', $funcionario->orgao_emissor) }}" placeholder="SSP/SP" class="w-32 rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                                </div>
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Data de Nascimento</label>
                                <input type="date" name="data_nascimento" value="{{ old('data_nascimento', $funcionario->data_nascimento?->format('Y-m-d')) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Sexo</label>
                                <select name="sexo" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm appearance-none cursor-pointer bg-slate-50/50">
                                    <option value="">Selecione...</option>
                                    <option value="masculino" {{ old('sexo', $funcionario->sexo) === 'masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="feminino" {{ old('sexo', $funcionario->sexo) === 'feminino' ? 'selected' : '' }}>Feminino</option>
                                    <option value="outro" {{ old('sexo', $funcionario->sexo) === 'outro' ? 'selected' : '' }}>Outro</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-12 pt-10 border-t border-slate-100 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">WhatsApp / Telefone Principal</label>
                                <input type="text" name="whatsapp" value="{{ old('whatsapp', $funcionario->whatsapp) }}" placeholder="(00) 00000-0000" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm bg-emerald-50/20">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">E-mail de Contato (Pessoal)</label>
                                <input type="email" name="email_pessoal" value="{{ old('email_pessoal', $funcionario->email_pessoal) }}" placeholder="exemplo@gmail.com" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                         <h2 class="text-xl font-black text-slate-800 mb-8 border-b border-slate-100 pb-4">Endereço & Localização</h2>
                         <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-1">
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">CEP</label>
                                <input type="text" name="cep" value="{{ old('cep', $funcionario->cep) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Logradouro / Rua</label>
                                <input type="text" name="endereco" value="{{ old('endereco', $funcionario->endereco) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Número</label>
                                <input type="text" name="numero" value="{{ old('numero', $funcionario->numero) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Bairro</label>
                                <input type="text" name="bairro" value="{{ old('bairro', $funcionario->bairro) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Cidade</label>
                                <input type="text" name="cidade" value="{{ old('cidade', $funcionario->cidade) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div class="md:col-span-1">
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">UF</label>
                                <input type="text" name="uf" value="{{ old('uf', $funcionario->uf) }}" maxlength="2" placeholder="SP" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm uppercase">
                            </div>
                         </div>
                    </div>
                </section>

                <!-- SECTION 2: TRABALHISTAS -->
                <section x-show="tab === 'trabalhistas'" class="space-y-6 animate-in slide-in-from-bottom-2 duration-300">
                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                        <h2 class="text-xl font-black text-slate-800 mb-8 border-b border-slate-100 pb-4">Vinculação Funcional</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Matrícula Interna</label>
                                <input type="text" name="matricula" value="{{ old('matricula', $funcionario->matricula) }}" placeholder="Ex: VAPT-001" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Tipo de Vínculo Contratual</label>
                                <select name="tipo_vinculo" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-black text-slate-800 shadow-sm bg-slate-50/50 appearance-none cursor-pointer">
                                    <option value="clt" {{ old('tipo_vinculo', $funcionario->tipo_vinculo) === 'clt' ? 'selected' : '' }}>CLT (Consolidado)</option>
                                    <option value="pj" {{ old('tipo_vinculo', $funcionario->tipo_vinculo) === 'pj' ? 'selected' : '' }}>PJ (Pessoa Jurídica)</option>
                                    <option value="estagio" {{ old('tipo_vinculo', $funcionario->tipo_vinculo) === 'estagio' ? 'selected' : '' }}>Estágio</option>
                                    <option value="autonomo" {{ old('tipo_vinculo', $funcionario->tipo_vinculo) === 'autonomo' ? 'selected' : '' }}>Autônomo / Freelancer</option>
                                    <option value="temporario" {{ old('tipo_vinculo', $funcionario->tipo_vinculo) === 'temporario' ? 'selected' : '' }}>Temporário / Diarista</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Cargo Formal (Carteira)</label>
                                <input type="text" name="cargo_formal" value="{{ old('cargo_formal', $funcionario->cargo_formal) }}" placeholder="Ex: Auxiliar de Produção I" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Setor / Departamento</label>
                                <input type="text" name="setor" value="{{ old('setor', $funcionario->setor) }}" placeholder="Ex: Acabamento" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-8">
                        <h2 class="text-xl font-black text-slate-800 mb-8 border-b border-slate-100 pb-4">Remuneração & Jornada</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Salário Base (Bruto)</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-black text-slate-300">R$</span>
                                    <input type="number" step="0.01" name="salario_base" value="{{ old('salario_base', $funcionario->salario_base) }}" class="w-full rounded-2xl border-slate-200 pl-12 pr-5 py-4 font-black text-brand-secondary text-xl shadow-inner">
                                </div>
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Comissão (%)</label>
                                <div class="relative">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 font-black text-slate-300">%</span>
                                    <input type="number" step="0.1" name="comissao_percentual" value="{{ old('comissao_percentual', $funcionario->comissao_percentual) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-black text-slate-700 text-xl shadow-inner text-right">
                                </div>
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Data de Admissão</label>
                                <input type="date" name="data_admissao" value="{{ old('data_admissao', $funcionario->data_admissao?->format('Y-m-d')) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                            <div>
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Status Funcional</label>
                                <select name="status_funcional" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-black text-slate-800 shadow-sm bg-slate-100">
                                    <option value="ativo" {{ old('status_funcional', $funcionario->status_funcional) === 'ativo' ? 'selected' : '' }}>Ativo / Disponível</option>
                                    <option value="ferias" {{ old('status_funcional', $funcionario->status_funcional) === 'ferias' ? 'selected' : '' }}>Em Férias</option>
                                    <option value="afastado" {{ old('status_funcional', $funcionario->status_funcional) === 'afastado' ? 'selected' : '' }}>Afastado (Médico/Licença)</option>
                                    <option value="desligado" {{ old('status_funcional', $funcionario->status_funcional) === 'desligado' ? 'selected' : '' }}>Desligado (Ex-Func.)</option>
                                </select>
                            </div>
                            <div class="lg:col-span-2">
                                <label class="text-[11px] uppercase font-black text-slate-400 mb-1 block">Jornada de Trabalho</label>
                                <input type="text" name="jornada_tipo" placeholder="Ex: 44h semanais (Seg a Sex das 08h às 18h)" value="{{ old('jornada_tipo', $funcionario->jornada_tipo) }}" class="w-full rounded-2xl border-slate-200 px-5 py-4 font-bold text-slate-700 shadow-sm">
                            </div>
                        </div>
                    </div>
                </section>

                <!-- SECTION 3: ACESSO SISTEMA -->
                <section x-show="tab === 'acesso'" class="space-y-6 animate-in slide-in-from-bottom-2 duration-300">
                    <div class="bg-slate-900 rounded-3xl p-8 text-white shadow-2xl relative overflow-hidden">
                        <div class="relative z-10">
                            <h2 class="text-2xl font-black mb-1">Acesso ao Software</h2>
                            <p class="text-white/50 text-sm mb-8 flex items-center gap-2">
                                <i class="fas fa-lock text-brand-primary"></i> Vincular um login do sistema a este currículo.
                            </p>

                            @if(!$funcionario->user_id)
                            <div class="mb-8 p-6 bg-white/5 rounded-2xl border border-white/10 flex items-center gap-4">
                                <input type="checkbox" name="criar_acesso" value="1" x-model="criarAcesso" class="h-6 w-6 rounded border-white/20 bg-slate-800 text-brand-primary focus:ring-brand-primary">
                                <div>
                                    <label class="font-black text-white block">Liberar Acesso do Colaborador</label>
                                    <span class="text-[10px] text-white/40 uppercase font-bold tracking-widest">Consumirá 1 licença do plano</span>
                                </div>
                            </div>
                            @endif

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-show="criarAcesso || {{ $funcionario->user_id ? 'true' : 'false' }}">
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-[10px] uppercase font-black text-white/40 mb-1 block">Nome de Acesso</label>
                                        <input type="text" name="nome_acesso" value="{{ old('nome_acesso', $usuario->nome ?? '') }}" class="w-full rounded-2xl border-white/10 bg-white/5 px-5 py-3 font-bold text-white focus:bg-white/10 focus:border-brand-primary transition-all">
                                    </div>
                                    <div>
                                        <label class="text-[10px] uppercase font-black text-white/40 mb-1 block">E-mail de Login</label>
                                        <input type="email" name="email_acesso" value="{{ old('email_acesso', $usuario->email ?? '') }}" class="w-full rounded-2xl border-white/10 bg-white/5 px-5 py-3 font-bold text-white focus:bg-white/10 focus:border-brand-primary transition-all">
                                    </div>
                                    <div>
                                        <label class="text-[10px] uppercase font-black text-white/40 mb-1 block">Senha do Sistema</label>
                                        <input type="password" name="senha_acesso" placeholder="{{ $funcionario->user_id ? 'Deixe em branco para manter' : '6+ caracteres' }}" class="w-full rounded-2xl border-white/10 bg-white/5 px-5 py-3 font-bold text-white focus:bg-white/10 focus:border-brand-primary transition-all">
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <label class="text-[10px] uppercase font-black text-white/40 mb-1 block">Nível de Permissão (Perfil)</label>
                                        @php
                                            $currentPerfil = strtolower($usuario->perfil ?? '');
                                        @endphp
                                        <select name="perfil" class="w-full rounded-2xl border-white/10 bg-white/5 px-5 py-3 font-black text-white focus:bg-white/10 focus:border-brand-primary transition-all appearance-none cursor-pointer">
                                            <option value="atendente" {{ $currentPerfil === 'atendente' ? 'selected' : '' }} class="bg-slate-900">Atendente / Comercial</option>
                                            <option value="producao" {{ in_array($currentPerfil, ['producao', 'produção']) ? 'selected' : '' }} class="bg-slate-900">Produção / Operativo</option>
                                            <option value="gerente" {{ $currentPerfil === 'gerente' ? 'selected' : '' }} class="bg-slate-900">Gerente Local</option>
                                            <option value="financeiro" {{ $currentPerfil === 'financeiro' ? 'selected' : '' }} class="bg-slate-900">Adm. Financeiro</option>
                                            <option value="administrador" {{ $currentPerfil === 'administrador' ? 'selected' : '' }} class="bg-slate-900 text-brand-primary font-black">PRO: Administrador</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="text-[10px] uppercase font-black text-white/40 mb-2 block">Avatar de Identificação</label>
                                        <div class="flex items-center gap-4 bg-white/5 border border-white/10 p-3 rounded-2xl">
                                            <div class="h-12 w-12 rounded-full border border-white/20 overflow-hidden bg-white/10 shrink-0">
                                                @if($usuario->avatar)
                                                    <img src="{{ asset('storage/' . $usuario->avatar) }}" class="h-full w-full object-cover">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center text-white/20"><i class="fas fa-user"></i></div>
                                                @endif
                                            </div>
                                            <input type="file" name="avatar" class="text-[10px] text-white/40 file:mr-2 file:rounded-lg file:border-0 file:bg-white/10 file:px-2 file:py-1 file:text-white file:text-[10px] file:font-black">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Decor -->
                        <div class="absolute -bottom-10 -right-10 h-64 w-64 bg-brand-primary/10 rounded-full blur-3xl"></div>
                    </div>
                </section>

                <div class="mt-12 flex items-center justify-between gap-6">
                    <div class="hidden md:block">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Protocolo de Segurança</p>
                        <p class="text-[11px] text-slate-500 font-medium">Todas as alterações de cargo e salário geram histórico automático (Dossiê RH).</p>
                    </div>
                    <button type="submit" class="w-full md:w-auto px-12 py-5 font-black text-white bg-brand-primary hover:bg-orange-500 hover:scale-105 shadow-2xl shadow-brand-primary/30 rounded-3xl transition-all flex items-center justify-center gap-3 active:scale-95">
                        <i class="fas fa-save text-lg"></i> SALVAR ALTERAÇÕES
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
