# GUIA DE INTEGRAÇÃO - CONTROLLERS E VIEWS

## 🎯 Objetivo

Este guia mostra EXATAMENTE COMO integrar os novos Services no código existente.

---

## 📋 INTEGRAÇÃO DE CONTROLLERS

### 1. InsumoController - Integração de Permissões

**Arquivo**: `app/Http/Controllers/Admin/Inventory/InsumoController.php`

#### Adicionar ao Constructor:

```php
use App\Services\Domain\InsumoPermissaoService;
use App\Services\Domain\InsumoConversaoService;

public function __construct(
    protected InventoryService $inventoryService,
    protected InventoryConversionService $conversionService,
    protected InsumoOperationalClassifierService $insumoClassifierService,
    protected FinancePlanService $planService,
    protected TenantContext $tenantContext,
    
    // NOVO:
    protected InsumoPermissaoService $permissaoService,
    protected InsumoConversaoService $insumoConversao, // Novo service principal
) {}
```

#### Método `store()` - Usar Validação Robusta:

```php
public function store(Request $request): RedirectResponse
{
    $this->authorize('create', Insumo::class);
    
    $data = $request->validate([
        // ... validações existentes ...
    ]);

    $data['controlar_estoque'] = $request->boolean('controlar_estoque');
    $data['usar_na_precificacao'] = $request->boolean('usar_na_precificacao');

    $data = $this->insumoClassifierService->normalizarCamposInsumo($data);

    // NOVO: Usar novo service para validação robusta
    try {
        $data = $this->insumoConversao->validateAndNormalizeConversion($data);
    } catch (\InvalidArgumentException $e) {
        return back()->with('erro', $e->getMessage())->withInput();
    }

    $insumo = Insumo::create(array_merge($data, [
        'loja_id' => $this->tenantContext->getLojaId() ?? auth()->user()->loja_id,
        'pode_ser_excluido' => true, // Novo insumo é excluível
    ]));

    if (($data['submit_action'] ?? 'save') === 'save_and_entry') {
        return redirect()
            ->route('admin.inventory.movimentacoes.entrada', ['insumo_id' => $insumo->id])
            ->with('sucesso', 'Item cadastrado. Agora registre a entrada inicial para definir saldo e custo.');
    }

    return redirect()->route('admin.inventory.insumos.index')->with('sucesso', 'Insumo cadastrado com sucesso.');
}
```

#### Método `update()` - Usar Validação Robusta:

```php
public function update(Request $request, Insumo $insumo): RedirectResponse
{
    $this->authorize('update', $insumo);
    
    $data = $request->validate([
        // ... validações existentes ...
    ]);

    $data['controlar_estoque'] = $request->boolean('controlar_estoque');
    $data['usar_na_precificacao'] = $request->boolean('usar_na_precificacao');

    $data = $this->insumoClassifierService->normalizarCamposInsumo($data);

    // NOVO: Usar novo service para validação robusta
    try {
        $data = $this->insumoConversao->validateAndNormalizeConversion($data);
    } catch (\InvalidArgumentException $e) {
        return back()->with('erro', $e->getMessage())->withInput();
    }

    // ... resto do logic legado de re-normalização de custo ...
    
    $insumo->update($data);

    return redirect()->route('admin.inventory.insumos.index')->with('sucesso', 'Insumo atualizado.');
}
```

#### Método `destroy()` - NOVO: Governança de Exclusão:

```php
// IMPORTANTE: Este método não existia antes ou estava vazio
// Adicionar novo método ou sobrescrever se existir

public function destroy(Insumo $insumo): RedirectResponse
{
    $this->authorize('delete', $insumo);
    
    try {
        // Verificar se pode excluir fisicamente
        $this->permissaoService->canDelete(auth()->user(), $insumo);
        
        // Se passou na validação, excluir
        $insumo->forceDelete();
        
        return redirect()
            ->route('admin.inventory.insumos.index')
            ->with('sucesso', 'Insumo excluído permanentemente.');
            
    } catch (\RuntimeException $e) {
        // Tem movimentações, inativar em vez disso
        $insumo->inativar("Deletado por usuário (com movimentações): " . auth()->user()->nome);
        
        return redirect()
            ->route('admin.inventory.insumos.index')
            ->with('sucesso', 'Insumo inativado (não pode ser deletado com movimentações, mas foi desativado).');
    }
}

/**
 * NOVO: Ação de inativação explícita
 */
public function deactivate(Request $request, Insumo $insumo): RedirectResponse
{
    $this->authorize('deactivate', $insumo);
    
    $motivo = $request->input('motivo', 'Inativado pelo usuário');
    
    $insumo->inativar($motivo);
    
    return redirect()
        ->route('admin.inventory.insumos.index')
        ->with('sucesso', "Insumo '{$insumo->nome}' inativado.");
}

/**
 * NOVO: Ação de reativação (apenas admin)
 */
public function reactivate(Insumo $insumo): RedirectResponse
{
    $this->authorize('restore', $insumo);
    
    $insumo->reativar();
    
    return redirect()
        ->route('admin.inventory.insumos.index')
        ->with('sucesso', "Insumo '{$insumo->nome}' reativado.");
}
```

---

### 2. EstoqueMovimentacaoController - Já Está Correto

Não precisa de mudanças pois:
- ✅ `registerAdjustment()` não recalcula custo (correto)
- ✅ Movimentações já registram saldo anterior/posterior
- ✅ Já usa `origem_tela`

Apenas verificar que método `processarAjuste()` passa `origem_tela`:

```php
// ✅ Já existe, conforme visto no código anterior
public function processarAjuste(Request $request, Insumo $insumo): RedirectResponse
{
    // ...
    $data['origem_tela'] = 'ajuste'; // ✅ JÁ ESTÁ
    $this->inventoryService->registrarAjuste($insumo, $data);
    // ...
}
```

---

## 📋 INTEGRAÇÃO DE VIEWS

### 1. Listagem de Insumos (`insumos/index.blade.php`)

**Mudanças recomendadas**:

```blade
{{-- ANTES: Ação fixa de exclusão --}}
<a href="{{ route('admin.inventory.insumos.edit', $insumo) }}">✏️</a>

{{-- DEPOIS: Ação dinâmica baseada em permissão --}}
<div class="flex gap-1">
    <a href="{{ route('admin.inventory.insumos.edit', $insumo) }}" class="p-2" title="Editar">✏️</a>
    <a href="{{ route('admin.inventory.insumos.ajuste', $insumo) }}" class="p-2" title="Ajuste de Saldo">⚖️</a>
    
    {{-- NOVO: Ação de remoção dinâmica --}}
    @php
        $removalAction = $permissao->getRemovalAction(auth()->user(), $insumo);
    @endphp
    
    @if($removalAction === 'delete')
        <form method="POST" action="{{ route('admin.inventory.insumos.destroy', $insumo) }}" style="display:inline;">
            @csrf @method('DELETE')
            <button type="submit" class="p-2 text-red-500" 
                    onclick="return confirm('Confirmar exclusão?')"
                    title="Deletar insumo">🗑️</button>
        </form>
    @elseif($removalAction === 'deactivate')
        <form method="POST" action="{{ route('admin.inventory.insumos.deactivate', $insumo) }}" style="display:inline;">
            @csrf
            <button type="submit" class="p-2 text-orange-500"
                    title="Inativar insumo (tem movimentações)">🚫</button>
        </form>
    @endif
</div>
```

**No topo, adicionar**:

```blade
@php
    use App\Services\Domain\InsumoPermissaoService;
    $permissao = app(InsumoPermissaoService::class);
@endphp
```

---

### 2. Edição de Insumo (`insumos/form.blade.php`)

**Melhorias de Microcopy** (substitua os labels confusos):

```blade
{{-- ANTES --}}
<label>Quantas unidades internas por compra?</label>
<label>Nome da unidade interna</label>
<label>Quanto há por unidade interna?</label>

{{-- DEPOIS --}}
<label>Quantas <em>unidades intermediárias</em> há por compra? <span class="text-red-500">*</span></label>
<p class="text-xs text-slate-400 mt-1">Ex: 6 frascos por caixa</p>

<label>Nome da <em>unidade intermediária</em> <span class="text-red-500">*</span></label>
<p class="text-xs text-slate-400 mt-1">Ex: frasco, ampola, sachê (deve ser diferente de "{{ $insumo->unidade_compra }}")</p>

<label>Quanto há em cada <em>unidade intermediária</em>? <span class="text-red-500">*</span></label>
<p class="text-xs text-slate-400 mt-1">Ex: 1000 (cada {{ $insumo->unidade_subunidade }} tem 1000 {{ $insumo->unidade_medida }})</p>
```

**Adicionar validação lado-cliente**:

```blade
<script>
document.getElementById('unidade_compra').addEventListener('change', function() {
    const unidadeCompra = this.value.toLowerCase();
    const unidadeSub = document.getElementById('unidade_sub').value.toLowerCase();
    
    if (unidadeCompra && unidadeSub && unidadeCompra === unidadeSub) {
        alert('⚠️ A unidade de compra e a unidade intermediária não podem ter o mesmo nome!\nExemplo válido: caixa → frasco');
        document.getElementById('unidade_sub').value = '';
    }
});
</script>
```

---

### 3. Ajuste Físico (`movimentacoes/ajuste.blade.php`)

**Adicionar aviso claro**:

```blade
<div class="mb-6 p-4 bg-red-50 rounded-xl border border-red-200 flex items-center gap-4">
    <span class="text-2xl">⚠️</span>
    <div>
        <p class="text-sm font-black text-red-700 uppercase">Importante</p>
        <p class="text-sm font-semibold text-red-800">
            Este ajuste <strong>não representa uma compra</strong>. É uma correção de inventário.
            O custo médio <strong>não será alterado</strong>.
        </p>
    </div>
</div>
```

**Modo guiado recomendado** (futuro, mas esboço):

```blade
<div class="mb-6">
    <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" id="modo_guiado" name="modo_guiado" value="1" class="rounded">
        <span class="text-sm font-bold text-slate-700">Modo Guiado de Contagem</span>
        <span class="text-xs text-slate-400">(Contar embalagens e converter automaticamente)</span>
    </label>
</div>

<div id="bloco-modo-guiado" class="hidden">
    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 space-y-4">
        <p class="text-sm font-semibold text-indigo-800">
            Informe a contagem física conforme encontrado:
        </p>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="text-xs font-bold text-indigo-700">{{ $insumo->unidade_compra }}s completos</label>
                <input type="number" step="0.01" id="qtd_completos" class="w-full rounded">
            </div>
            <div>
                <label class="text-xs font-bold text-indigo-700">{{ $insumo->unidade_medida }} resíduos</label>
                <input type="number" step="0.01" id="qtd_residuos" class="w-full rounded">
            </div>
        </div>
        
        <div class="rounded-lg bg-white p-3 border border-indigo-200">
            <p class="text-xs font-bold text-slate-400">Total convertido:</p>
            <p id="total-convertido" class="text-lg font-black text-indigo-800">0 {{ $insumo->unidade_medida }}</p>
        </div>
    </div>
</div>

<script>
document.getElementById('modo_guiado').addEventListener('change', function() {
    document.getElementById('bloco-modo-guiado').classList.toggle('hidden', !this.checked);
});

// Converter automaticamente
document.getElementById('qtd_completos').addEventListener('input', calcularTotal);
document.getElementById('qtd_residuos').addEventListener('input', calcularTotal);

function calcularTotal() {
    const fator = {{ $insumo->quantidade_por_compra ?? 1 }};
    const completos = parseFloat(document.getElementById('qtd_completos').value) || 0;
    const residuos = parseFloat(document.getElementById('qtd_residuos').value) || 0;
    const total = (completos * fator) + residuos;
    
    document.getElementById('total-convertido').textContent = 
        total.toFixed(4).replace(/\.?0+$/, '') + ' {{ $insumo->unidade_medida }}';
    
    // Atualizar campo hidden do formulário
    document.getElementById('novo_saldo').value = total.toFixed(4);
}
</script>
```

---

### 4. Routes - Adicionar Novas Ações

**Arquivo**: `routes/web.php` ou `routes/web/inventory.php`

```php
// Adicionar nas rotas de inventory:
Route::prefix('inventory/insumos')->name('admin.inventory.insumos.')->group(function () {
    Route::get('/', [InsumoController::class, 'index'])->name('index');
    Route::get('/create', [InsumoController::class, 'create'])->name('create');
    Route::post('/', [InsumoController::class, 'store'])->name('store');
    Route::get('/{insumo}/edit', [InsumoController::class, 'edit'])->name('edit');
    Route::put('/{insumo}', [InsumoController::class, 'update'])->name('update');
    Route::delete('/{insumo}', [InsumoController::class, 'destroy'])->name('destroy');
    
    // NOVAS ROTAS:
    Route::post('/{insumo}/deactivate', [InsumoController::class, 'deactivate'])->name('deactivate');
    Route::post('/{insumo}/reactivate', [InsumoController::class, 'reactivate'])->name('reactivate');
    
    Route::get('/alertas', [InsumoController::class, 'alertas'])->name('alertas');
    Route::get('/{insumo}/ajuste', [EstoqueMovimentacaoController::class, 'ajuste'])->name('ajuste');
    Route::post('/{insumo}/processar-ajuste', [EstoqueMovimentacaoController::class, 'processarAjuste'])->name('processar-ajuste');
});
```

---

## 🧪 TESTES PÓS-INTEGRAÇÃO

```bash
# Rodar testes de permissão
php artisan test tests/Feature/Inventory/InsumoPermissaoTest

# Rodar testes de conversão
php artisan test tests/Feature/Inventory/InsumoConversionRegressionTest

# Verificar específico: Tinta 4L
php artisan test tests/Feature/Inventory/InsumoConversionRegressionTest::test_regression_tinta_ajuste_fisico_700ml
```

---

## 📋 CHECKLIST DE INTEGRAÇÃO

- [ ] Adicionar Services ao constructor do Controller
- [ ] Refatorar `store()` com novo `InsumoConversaoService`
- [ ] Refatorar `update()` com novo `InsumoConversaoService`
- [ ] Implementar novo `destroy()` com governança
- [ ] Implementar novo `deactivate()` com rastreabilidade
- [ ] Implementar novo `reactivate()` (admin only)
- [ ] Adicionar rotas para deactivate e reactivate
- [ ] Atualizar Blade de listagem com ação dinâmica
- [ ] Atualizar Blade de edição com microcopy
- [ ] Atualizar Blade de ajuste com aviso
- [ ] Adicionar validação lado-cliente (unidades iguais)
- [ ] Testar permissões em staging
- [ ] Testar caso crítico Tinta 4L

---

**Versão**: 1.0 | **Status**: Pronto para Implementação | **Data**: 2026-04-29
