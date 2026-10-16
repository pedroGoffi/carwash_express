# CarWash Express — Lavagens adicionais e painel diário

## Alterações

1. **Uma lavagem principal + lavagens adicionais:** o operador escolhe uma lavagem principal e pode incluir outros produtos já cadastrados como `wash` na seção **Serviços adicionais de lavagem**. Mantêm-se bebidas, lanches e outros itens na mesma OS. Uma mesma lavagem não pode ser simultaneamente principal e adicional. Lavagens extras têm quantidade 1 cada. Não é criado novo tipo de produto, nem novos produtos fictícios.
2. **Persistência:** migration `2026_10_16_000001_mark_primary_wash_on_order_items.php` acrescenta `service_order_items.is_primary_wash` (boolean), com `false` por padrão, e marca as lavagens das ordens legadas como principais, pois a versão anterior só permitia uma lavagem. Quantidades e preços históricos são preservados. O campo `primary_wash_id` é obrigatório na criação de novas OSs; o servidor recalcula preços, valida tenant, produto ativo, tipo e quantidade.
3. **Consulta de OS e recibo:** a listagem de serviços deixa de cortar a apresentação após 3 itens; lavagens adicionais ganham indicação **+ Lav.**. O recibo utiliza `is_primary_wash`, não a posição em que o banco retorna os produtos.
4. **Mini dashboard no topo de Nova ordem:** ordens (e veículos por categoria) criadas hoje; pagamentos **efetivamente lançados hoje** por Pix, dinheiro, crédito e débito, mesmo se a OS for de outro dia; bebidas e lavagens adicionais **lançadas em ordens criadas hoje**, com quantidade, valor e produtos mais vendidos. Ordens canceladas são excluídas. Todas as consultas exigem `company_id` do usuário autenticado. Atualiza a cada 60 s ou quando a aba retorna ao primeiro plano, sem recarregar o formulário. Se a conexão cair, mantém o último resumo e indica indisponibilidade.

## Contrato HTTP

- `GET /operator/create-order`: carrega formulário e snapshot do dia; exige login e `admin`, `manager` ou `operator`.
- `GET /operator/orders/today-summary`: JSON autenticado, exclusivo da empresa da sessão; recebe **nenhum identificador de empresa**. Resposta:

```json
{
  "date": "15/10/2026",
  "orders": 0,
  "vehicles": {"normal": 0, "suv": 0, "pickup": 0},
  "received": 0,
  "payments": {
    "pix": {"label": "Pix", "quantity": 0, "amount": 0},
    "cash": {"label": "Dinheiro", "quantity": 0, "amount": 0},
    "credit": {"label": "Crédito", "quantity": 0, "amount": 0},
    "debit": {"label": "Débito", "quantity": 0, "amount": 0}
  },
  "drinks": {"quantity": 0, "amount": 0, "items": []},
  "wash_addons": {"quantity": 0, "amount": 0, "items": []}
}
```

- `POST /operator/orders`: os campos anteriores continuam válidos, mas agora exige `primary_wash_id` como ID do produto de lavagem principal **presente** em `items`, com quantidade 1. Adicionais de lavagem são outros IDs de produtos `wash` presentes em `items`, também com quantidade 1. Os pagamentos podem ser registrados ou divididos como antes. Retorna redirecionamento após salvar ou erros de validação no campo `items`, `payments` ou `primary_wash_id`.

## Implantação na VPS

**Antes:** faça backup do banco PostgreSQL e use a imagem PHP 8.4 do Dockerfile atual. O ZIP da entrega não contém o `.env` nem a pasta `.git`, e não deve sobrescrever o `.env` da VPS.

```bash
cd /var/www/sistema
# Descompactar código atualizado sobre o projeto preservando .env.
# Inspecionar a alteração de schema antes de migrar:
php artisan migrate:status
# Se a aplicação roda somente no Docker, pode executar no serviço de imagem:
docker compose build web
# Migrações exigem banco disponível e variáveis .env corretas:
docker compose run --rm --no-deps --entrypoint php web artisan migrate --force
# Subir com a imagem reconstruída:
docker compose up -d --force-recreate
# Conferir:
docker compose ps
docker compose logs --tail=100 web
curl -I http://127.0.0.1:8080
```

Se o build do Docker estiver com falha, **não aplique a migration até resolver a imagem e verificar o backup**. No ambiente com dependências de desenvolvimento (Composer PHP 8.4), execute:

```bash
composer install
php artisan migrate --env=testing
php artisan test --filter=OrderWashExtrasAndTodaySummaryTest
php artisan view:cache
npm ci && npm run build
```

**Rollback:** antes de desfazer schema, publique o código da versão anterior e suspenda novas OS com mais de uma lavagem. A versão anterior rejeita novas ordens com várias lavagens e não identifica adicionais. Para reverter apenas a migration específica, em ambiente controlado: `php artisan migrate:rollback --step=1` *somente quando ela for a última migration aplicada*, após verificar `migrate:status` e assegurar que não haverá perda da distinção entre lavagens nas ordens novas. A coluna de marcação será perdida no rollback, mas os itens/preços não serão removidos.

## Cobertura de testes e limites da entrega

Arquivo de regressão `tests/Feature/OrderWashExtrasAndTodaySummaryTest.php` cobre: lavagem principal + adicional + bebida; preços; dados por veículo; pagamento Pix; recibo; ausência de lavagem principal; quantidade inválida; adicional de empresa diferente; pagamentos atuais de OS antigas; isolamento de outra empresa; negação a visitantes. Testes dependem de `vendor/`, PHP 8.4 e banco SQLite para testes ou PostgreSQL.

Validação local neste ambiente: sintaxe dos arquivos PHP com `php -l` e sintaxe JS embutida com `node --check`. **Não foi executada a suíte PHPUnit/Laravel nem o build de assets**, pois o ZIP de entrada não inclui `vendor/` e o ambiente não tem Composer instalado nem conexão externa para instalar dependências. Não afirmar homologação da VPS sem testes reais de dados e painel.
