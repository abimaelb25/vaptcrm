# PLANO DE MIGRAÇÃO DE DADOS - REFACTOR INSUMOS

## 🎯 Estratégia de Migração

**Abordagem**: INCREMENTAL E SEGURA (sem quebra)
**Compatibilidade**: Mantida com dados legados
**Rollback**: Reversível via migrations

---

## 📋 CHECKLIST PRÉ-MIGRAÇÃO

- [ ] Backup completo do banco de dados
- [ ] Testar em staging com dados reais
- [ ] Comunicar time de uso do módulo
- [ ] Preparar plano de rollback

---

## 🔄 PASSO 1: EXECUTAR MIGRATION

```bash
# Terminal em /app (dentro do container Laravel)
php artisan migrate --step
# Ou para listar apenas:
php artisan migrate:status | grep "2026_04_29"
```

**O que acontece**:
```sql
-- Em estoque_movimentacoes (compatível com dados existentes):
ALTER TABLE estoque_movimentacoes ADD COLUMN quantidade_base DECIMAL(15,4) NULL;
ALTER TABLE estoque_movimentacoes ADD COLUMN saldo_anterior DECIMAL(15,4) NULL;
ALTER TABLE estoque_movimentacoes ADD COLUMN saldo_posterior DECIMAL(15,4) NULL;
ALTER TABLE estoque_movimentacoes ADD COLUMN origem_tela VARCHAR(50) NULL;
ALTER TABLE estoque_movimentacoes ADD COLUMN motivo VARCHAR(100) NULL;

-- Em insumos:
ALTER TABLE insumos ADD COLUMN pode_ser_excluido BOOLEAN DEFAULT TRUE;
ALTER TABLE insumos ADD COLUMN inativado_em TIMESTAMP NULL;
ALTER TABLE insumos ADD COLUMN inativado_por_usuario_id BIGINT UNSIGNED NULL;
ALTER TABLE insumos ADD COLUMN motivo_inativacao TEXT NULL;
```

**Resultado**: Nenhuma dado é deletado, todos os campos novos iniciam `NULL` ou com default.

---

## 🔄 PASSO 2: EXECUTAR SANEAMENTO (Opcional, mas Recomendado)

Preencher campos `pode_ser_excluido` com valor inteligente baseado em movimentações:

```php
// File: database/seeders/SaneamentoPosmigracao.php
php artisan tinker

// Dentro do tinker:
$insumos = \App\Models\Insumo::all();
foreach ($insumos as $insumo) {
  $temMovimentacoes = $insumo->movimentacoes()->count() > 0;
  $insumo->pode_ser_excluido = !$temMovimentacoes; // Não excluível se houver movimentações
  $insumo->save();
}
```

**Ou via comando Artisan** (ainda não criado, mas recomendado):
```bash
php artisan insumo:saneamento-pos-migracao
```

---

## 🔄 PASSO 3: VALIDAR INTEGRIDADE

```php
php artisan tinker

// Verificar campos novos
$insumo = \App\Models\Insumo::first();
echo "pode_ser_excluido: " . $insumo->pode_ser_excluido . "\n";
echo "inativado_em: " . $insumo->inativado_em . "\n";

// Verificar movimentações antigas NÃO foram alteradas
$mov = \App\Models\EstoqueMovimentacao::first();
echo "quantidade_base: " . ($mov->quantidade_base ?? 'NULL') . "\n";
echo "saldo_anterior: " . ($mov->saldo_anterior ?? 'NULL') . "\n";
```

---

## 🔄 PASSO 4: TESTAR COM DADOS REAIS

### Teste 1: Validação de Conversão

```php
php artisan tinker

use App\Services\Domain\InsumoConversaoService;

$service = app(InsumoConversaoService::class);
$tinta = \App\Models\Insumo::where('nome', 'like', '%Tinta%')->first();

// Validar estrutura
$summary = $service->getConversionSummary($tinta);
echo "Conversão válida: " . json_encode($summary) . "\n";
```

### Teste 2: Permissões

```php
use App\Services\Domain\InsumoPermissaoService;
use App\Models\Insumo;

$permissao = app(InsumoPermissaoService::class);
$admin = \App\Models\Usuario::where('perfil', 'administrador')->first();
$insumo = Insumo::first();

// Testar delete
try {
  $canDelete = $permissao->canDelete($admin, $insumo);
  echo "Pode deletar: " . ($canDelete ? "SIM" : "NÃO") . "\n";
} catch (\RuntimeException $e) {
  echo "Erro: " . $e->getMessage() . "\n";
}
```

### Teste 3: Caso Crítico - Tinta 4L

```php
php artisan test tests/Feature/Inventory/InsumoConversionRegressionTest.php::test_regression_tinta_ajuste_fisico_700ml
```

---

## ⚠️ POSSÍVEIS PROBLEMAS E SOLUÇÕES

### Problema 1: "SQLSTATE[42S21]: Column already exists"

**Causa**: Migration já foi executada (schema já tem colunas)

**Solução**:
```bash
# Verificar:
php artisan migrate:status

# Se mostrar "Run": a migration não foi executada
# Se a coluna existe no BD mas não aparece na migration, revisar colunas do schema
php artisan tinker
\DB::getSchemaBuilder()->getColumnListing('insumos')
```

### Problema 2: Dados legados com `quantidade_consumo_per_subunidade` vs `quantidade_consumo_por_subunidade`

**Causa**: Typo no nome da coluna em código antigo

**Solução**: Usar alias no Model ou corrigir typo em migração futura

---

## 🔄 PASSO 5: ATIVAR NOVOS SERVICES (Integração)

Após validar testes, integrar Controllers:

```php
// app/Http/Controllers/Admin/Inventory/InsumoController.php

use App\Services\Domain\InsumoConversaoService;
use App\Services\Domain\InsumoPermissaoService;

public function __construct(
    protected InsumoConversaoService $conversionService,
    protected InsumoPermissaoService $permissaoService,
) {}

public function store(Request $request): RedirectResponse {
  // ... validação ...
  
  // NOVO: Validação robusta
  $data = $this->conversionService->validateAndNormalizeConversion($data);
  
  // ... criar insumo ...
}

public function destroy(Insumo $insumo): RedirectResponse {
  // NOVO: Usar permissões
  try {
    $this->permissaoService->canDelete(auth()->user(), $insumo);
    $insumo->forceDelete();
  } catch (\RuntimeException $e) {
    // Tem movimentações, inativar em vez disso
    $insumo->inativar("Deletado por: " . auth()->user()->nome);
  }
  return redirect()->route('admin.inventory.insumos.index');
}
```

---

## 📊 DADOS DE COMPATIBILIDADE

### Dados Legados vs Novos

| Campo | Antes | Depois | Impacto |
|-------|-------|--------|---------|
| `estoque_atual` | Persiste como está | Sem mudança | ✅ SAFE |
| `custo_medio` | Persiste como está | Sem mudança | ✅ SAFE |
| `quantidade_base` em movimentações | Não existia ou NULL | Preenchido em novas | ✅ SAFE |
| `pode_ser_excluido` | Não existia | Default=TRUE | ✅ SAFE |
| `inativado_em` | Não existia | NULL até inativar | ✅ SAFE |

---

## 🔄 PASSO 6: ROLLBACK (Se Necessário)

```bash
# Reverter APENAS a última migration:
php artisan migrate:rollback --step=1

# Ou voltar N steps:
php artisan migrate:rollback --steps=5

# Verificar:
php artisan migrate:status
```

**Resultado**: Todas as colunas adicionadas são removidas, dados existentes preservados.

---

## 📋 VALIDAÇÃO FINAL (Checklist)

- [ ] Migrations executadas sem erros
- [ ] Testes de regressão passando
- [ ] Dados legados preservados
- [ ] Novos Services funcionando
- [ ] Permissões testadas (multi-tenant, exclusão, inativação)
- [ ] Controllers integrados (se aplícavel)
- [ ] Views atualizadas (se aplícavel)
- [ ] Documentação de usuário preparada

---

## 📚 REFERÊNCIA RÁPIDA

**Executar migration**:
```bash
cd app
php artisan migrate
```

**Testar regressão**:
```bash
php artisan test tests/Feature/Inventory/InsumoConversionRegressionTest
php artisan test tests/Feature/Inventory/InsumoPermissaoTest
```

**Reverter se quebrar**:
```bash
php artisan migrate:rollback --step=1
```

**Inspecionar dados**:
```bash
php artisan tinker
>>> Insumo::count()
>>> EstoqueMovimentacao::count()
>>> Insumo::first()->pode_ser_excluido
```

---

**Preparado em**: 2026-04-29 | **Versão**: 1.0 | **Status**: Pronto para Staging
