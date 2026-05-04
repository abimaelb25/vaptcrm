# SaaS Plan Governance (Multi-tenant)

## Objetivo
Padronizar controle de features, limites, ciclo de assinatura e uso por loja para operacao SaaS real.

## Componentes Centrais
- PlanService: camada unica para validar feature, limite e assinatura.
- CheckPlanFeature middleware: bloqueia modulo nao contratado.
- CheckPlanLimit middleware: bloqueia criacao acima do limite.
- CheckStorageLimit middleware: bloqueia upload quando o limite de storage e atingido.

## Estrutura de Dados
- saas_planos: versionamento de plano (`version`, `legacy_slug`, `is_legacy`).
- saas_plano_features: mapa normalizado de flags por plano.
- saas_plano_limits: mapa normalizado de limites por plano.
- saas_assinaturas: snapshot de plano + campos de gateway/financeiro (`plan_snapshot`, `plan_version`, `gateway_*`, `financial_status`, `grace_ends_at`, `renews_at`, `canceled_at`).
- saas_usage_logs: trilha de uso/auditoria para consumo e rastreabilidade.

## Compatibilidade Legada
- Fallback em Plano::resolveLimit para `limite_produtos` e `limite_funcionarios`.
- Fallback em Plano::featureEnabled para `recursos_premium` quando feature normalizada nao existir.
- Seed continua mantendo colunas legadas e agora tambem popula features/limits canonicamente.

## Ciclo de Vida da Assinatura
- Estados tratados no model Assinatura: trial, active, past_due, suspended, canceled.
- Regras:
  - `ativa()` considera trial valido e grace period.
  - `expirada()` bloqueia quando nao ativa.

## Isolamento Multi-tenant
- Modelos SaaS sensiveis agora usam HasTenancy:
  - PagamentoSaaS
  - ConsumoMetrica
  - NotificacaoInadimplencia
  - EmailLog
- PlanService usa filtro explicito por loja em contagens criticas para cobertura em web/jobs/console.

## Integracao por Modulo (Routes)
- Catalogo: `check_plan_feature:modulo_produtos`
- Vendas/PDV: `check_plan_feature:modulo_pedidos`
- Financeiro: `check_plan_feature:modulo_financeiro`
- Estoque: `check_plan_feature:modulo_estoque`
- Producao: `check_plan_feature:modulo_producao`
- Kanban: `check_plan_feature:modulo_kanban`
- API de producao: `check_plan_feature:modulo_api` + `modulo_producao`
- Upload de docs de perfil: `check_storage_limit`

## Usage Tracking
- OrderService registra evento `pedido_criado` em `saas_usage_logs`.
- CheckPlanLimit registra evento `limit_check_passed` em sucesso.
- MediaService registra `storage_delta` e atualiza `lojas.storage_used_bytes` em upload/delete.

## Pagamentos e Gateways
- StripeService agora preenche campos gateway-agnosticos (`gateway_provider`, `gateway_subscription_id`, `gateway_status`, `financial_status`, `renews_at`, `canceled_at`).
- Estrutura pronta para expandir para Asaas/Mercado Pago usando os mesmos campos padrao.

## Testes
Arquivo: tests/Feature/SaaSPlanGovernanceTest.php
Cobertura:
- limite de recursos
- bloqueio de feature
- isolamento por loja
- mudanca de plano
- expiracao e grace period

Comando:
- php artisan test --filter=SaaSPlanGovernanceTest
