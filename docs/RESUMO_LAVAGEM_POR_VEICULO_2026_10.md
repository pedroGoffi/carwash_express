# CarWash — Mini dashboard e Resumo diário por tipo de lavagem e veículo

## Alterações

- **Nova Ordem:** adicionada tabela compacta `Lavagens por tipo e veículo`, discriminando a lavagem principal por produto e carro/SUV/picape, com quantidade e faturamento de cada lavagem. Ela permanece sincronizada pela API do resumo a cada atualização, sem recarregar o formulário.
- **Resumo diário:** adicionadas duas tabelas, `Lavagens principais por tipo e veículo` e `Lavagens adicionais por tipo e veículo`, ambas respeitando a data selecionada (incluindo D-1). As células também apresentam os valores vendidos por tipo de veículo.
- **Backend:** o `TodayOrderSummaryService` passou a retornar `wash_types` e `wash_addon_types`, com `quantity`, `amount` e `items[]` contendo `id`, `name`, `quantity`, `amount`, `vehicles` e `vehicle_amounts` por `normal`, `suv`, `pickup`.

## Regras

1. **Lavagem principal** entra somente no total de `wash_types`; **adicionais** entram apenas em `wash_addon_types`, evitando duplicar ordens.
2. O cálculo usa `service_order_items.total_price` gravado na ordem, não o preço atual do catálogo.
3. Exclui ordens canceladas, restringe por `company_id` e data de criação da OS.
4. Valores de venda das lavagens não são a mesma métrica de recebimentos (que seguem `payments.created_at`).
5. Não requer migration nova.

## Aplicar no localhost

Extraia o ZIP de arquivos alterados e, dentro da pasta do projeto, copie a pasta extraída sobre a atual:

```bash
cp -a CARWASH_LAVAGENS_POR_TIPO_E_VEICULO_ARQUIVOS_ALTERADOS/. ./
docker compose build web
docker compose up -d --force-recreate web
docker compose exec web php artisan optimize:clear
```

## Testes

```bash
docker compose exec web php artisan test --filter=OrderWashExtrasAndTodaySummaryTest
```
