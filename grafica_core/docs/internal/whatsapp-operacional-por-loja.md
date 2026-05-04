# WhatsApp Operacional por Loja

## Objetivo

Evoluir o módulo oficial de WhatsApp Business Platform para uso operacional diário por loja no VaptCRM, mantendo isolamento multi-tenant, sem expor tokens e sem quebrar a camada base já implementada.

## Escopo entregue

- Tela HTML de configuração por loja no painel.
- Persistência das preferências operacionais em tabela própria.
- Mapeamento de template por evento com placeholders editáveis.
- Ativação e desativação individual de automações.
- Ação de envio de mensagem teste com validação operacional.
- Inbox operacional com filtros.
- Tela de logs com mensagens e webhooks.
- Integração da automação com os eventos atuais de pedido.

## Tabela nova

### `whatsapp_store_settings`

Persistência por loja para a camada operacional.

Campos principais:

- `loja_id`: vínculo único com a loja.
- `default_account_id`: conta padrão da loja.
- `catalog_link`: link usado em placeholders.
- `send_mode`: `manual_link` (link wa.me) ou `official_api` (API oficial).
- `automations`: JSON com flags booleanas por evento.
- `event_mappings`: JSON com template e variáveis por evento.
- `last_test_phone`: último número usado no teste operacional.

Regra híbrida aplicada:

- Sem API ativa, o modo efetivo é manual por link.
- Com API ativa e modo `official_api`, o envio automático por evento é permitido.
- Com modo `manual_link`, nenhum envio automatizado é disparado.

## Eventos suportados

Constantes em `App\Models\WhatsApp\WhatsAppTemplate`:

- `order_created`
- `order_quote_sent`
- `payment_confirmed`
- `order_in_production`
- `order_ready`
- `order_delivered`

## Variáveis suportadas

Configuradas em `App\Services\WhatsApp\WhatsAppSettingsService::variableOptions()`:

- `cliente_nome`
- `pedido_numero`
- `orcamento_valor`
- `catalogo_link`
- `status_pedido`

Os placeholders são enviados como parâmetros posicionais do body do template. A operação deve preencher apenas a quantidade real de placeholders existentes no template aprovado na Meta.

## Serviços novos

### `App\Services\WhatsApp\WhatsAppSettingsService`

Responsabilidades:

- Criar ou recuperar a configuração operacional da loja.
- Persistir automações e mapeamentos.
- Resolver o template configurado para cada evento.
- Montar os parâmetros do template a partir do contexto do pedido.
- Enviar mensagem teste reutilizando a fila existente.

### Ajustes em serviços existentes

`WhatsAppConversationService`

- Suporta filtros operacionais: não lidas, pedido vinculado e responsável.

`WhatsAppOrderAutomationService`

- Passa a consultar `whatsapp_store_settings` quando a loja já possui configuração operacional.
- Se não houver configuração salva, mantém fallback para o comportamento anterior baseado em `system_key` dos templates.

`OrderService`

- Dispara automação WhatsApp após criação e transição de status do pedido.
- Dispara evento adicional de `payment_confirmed` no registro de pagamento.

## Controllers novos

### `App\Http\Controllers\Admin\WhatsApp\WhatsAppOperationsController`

Métodos:

- `index()`: tela principal de configuração por loja.
- `connect()`: registro ou atualização operacional da conta.
- `syncTemplates()`: sincronização HTML com redirect e flash message.
- `updateSettings()`: persistência das preferências por loja.
- `sendTest()`: envio operacional de template teste.
- `inbox()`: inbox HTML com filtros.
- `conversation()`: histórico da conversa com distinção humano vs automático.
- `sendConversationMessage()`: envio manual pela página da conversa.
- `logs()`: auditoria simples de mensagens e webhooks.

## Rotas HTML novas

Prefixo: `painel/whatsapp`

- `GET /painel/whatsapp` → `admin.whatsapp.index`
- `POST /painel/whatsapp/connect` → `admin.whatsapp.connect`
- `POST /painel/whatsapp/settings` → `admin.whatsapp.settings.update`
- `POST /painel/whatsapp/test-send` → `admin.whatsapp.settings.test-send`
- `GET /painel/whatsapp/manual-link/{pedido}?event_key=...` → `admin.whatsapp.manual-link`
- `POST /painel/whatsapp/accounts/{account}/sync-templates-ui` → `admin.whatsapp.accounts.sync-ui`
- `GET /painel/whatsapp/caixa-de-entrada` → `admin.whatsapp.page.inbox`
- `GET /painel/whatsapp/caixa-de-entrada/{conversation}` → `admin.whatsapp.page.conversation`
- `POST /painel/whatsapp/caixa-de-entrada/{conversation}/send` → `admin.whatsapp.page.conversation.send`
- `GET /painel/whatsapp/logs` → `admin.whatsapp.logs`

As rotas JSON já existentes foram preservadas.

## Views novas

- `resources/views/painel/whatsapp/index.blade.php`
- `resources/views/painel/whatsapp/inbox.blade.php`
- `resources/views/painel/whatsapp/conversation.blade.php`
- `resources/views/painel/whatsapp/logs.blade.php`

## Segurança

- O token continua criptografado no modelo `WhatsAppAccount`.
- A UI não lê nem exibe o token armazenado.
- Toda autorização operacional valida `loja_id` da conta ou conversa.
- O envio de teste exige confirmação explícita de opt-in na interface.
- O uso de fila e limites por plano permanece no serviço central de mensagens.

## Plano e limites

Limites já suportados e reaproveitados:

- `whatsapp_accounts`
- `whatsapp_messages_month`

Feature flag exigida nas rotas HTML e JSON:

- `modulo_whatsapp`

## Testes

Arquivo novo:

- `tests/Feature/WhatsApp/WhatsAppOperationsTest.php`

Cenários cobertos:

- carregamento e persistência da configuração operacional por loja;
- envio de mensagem teste com criação de opt-in e enfileiramento do job.

Os testes de base do módulo continuam em:

- `tests/Feature/WhatsApp/WhatsAppModuleTest.php`

## Limites conhecidos

- O fluxo visual completo de Embedded Signup da Meta ainda não foi implementado no frontend; a tela operacional atual registra a conta via formulário administrativo seguro.
- O envio manual de template pela página da conversa não preenche placeholders dinamicamente; para mapeamentos dinâmicos, usar o fluxo de automações por evento ou o teste operacional.