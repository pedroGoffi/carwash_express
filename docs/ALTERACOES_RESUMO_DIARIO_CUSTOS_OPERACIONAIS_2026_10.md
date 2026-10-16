# CarWash Express — Resumo por data, preços zero e custos operacionais

## Entregue

1. **Resumo diário independente**: menu **Operação > Resumo diário**. Em `/operator/daily-summary`, há calendário e atalhos **Hoje** e **D-1 (ontem)**. Mantido o mini-resumo na tela Nova Ordem, agora com link para o histórico. Relatórios carregam dados da empresa autenticada.
2. **Critérios do resumo**: ordens, veículos, bebidas e lavagens adicionais usam data de criação da ordem; recebimentos usam a data de efetivação do pagamento (`payments.created_at`). Ordens canceladas são ignoradas. Se um recebimento de ordem antiga acontece hoje, conta no caixa de hoje, e não no da data de criação da OS.
3. **Preço zero**: serviços de lavagem sem preço positivo para a categoria do veículo selecionada não aparecem nos blocos de lavagem principal/adicional da Nova Ordem; o cliente não pode escolhê-los. Na troca da categoria do veículo, opções indisponíveis são removidas da seleção. Lavagens sem preço positivo em nenhuma categoria já são removidas no controller. O backend segue validando o preço maior que zero antes de criar a ordem.
4. **Custos operacionais**: nova área **Gestão > Custos operacionais** (`/manager/operating-expenses`) para água, luz, aluguel, manutenção, materiais, internet, impostos e outros; formulário, resumo por mês/categoria, histórico paginado e exclusão. Dados isolados por empresa. Apenas admin/gerente têm acesso. Valores mantidos separadamente do modelo preexistente `employee_expenses`, dedicado aos custos de colaboradores.
5. **Dashboard gerencial**: despesas operacionais passam a integrar custos e resultado mensal e a série dos últimos seis meses. **Adiantamentos** não são somados de novo como custos, pois podem representar antecipação salarial.

## Atualizar localhost existente

Na pasta que contém `docker-compose.yml`, salve um backup das alterações locais e o `.env`. Se estiver usando o ZIP **somente alterados**, extraia e mescle os diretórios:

```bash
cp -a CARWASH_RESUMO_CUSTOS_ARQUIVOS_ALTERADOS/. ./

docker compose build --no-cache web
docker compose up -d --force-recreate
docker compose exec web php artisan migrate --force
docker compose exec web php artisan optimize:clear
```

Se estiver usando o ZIP **projeto completo**, extraia a pasta `carwash_express/` como base do projeto mantendo seu `.env`. Rode os comandos Docker dentro dela.

**Não execute `docker compose down -v`** em ambientes com dados reais.

## Rotas adicionadas

- `GET /operator/daily-summary?date=AAAA-MM-DD` — consulta qualquer dia, perfil operacional.
- `GET /operator/orders/today-summary?date=AAAA-MM-DD` — resumo em JSON; sem parâmetro, retorna hoje (compatível com atualização automática existente).
- `GET /manager/operating-expenses?month=AAAA-MM` — gestão de despesas operacionais.
- `POST /manager/operating-expenses` — registro de custo.
- `DELETE /manager/operating-expenses/{expense}` — remoção, com verificação de empresa.

## Banco de dados

Migration aditiva `2026_10_16_010000_create_operating_expenses_table.php`, cria tabela `operating_expenses` com `company_id`, `category`, `date`, `amount`, `description`, `created_by`, timestamps e índice `(company_id,date)`.

**Não há alteração destrutiva** nas ordens, pagamentos, produtos ou despesas de funcionários.

## Verificação e testes

- Sintaxe de arquivos PHP validada com `php -l`.
- JavaScript da Nova Ordem validado com `node --check` após expandir placeholders Blade.
- Testes Feature incluídos: `tests/Feature/DailySummaryAndOperatingExpensesTest.php` (data D-1, tenant, preço zero, custos e permissões).
- O ZIP não inclui `vendor/`, e **PHPUnit não foi executado aqui**. Para executar no ambiente local com dev-dependencies: `docker compose exec web php artisan test --filter=DailySummaryAndOperatingExpensesTest` (a imagem de produção pode ter instalado Composer com `--no-dev`, neste caso rode em ambiente de testes com dependências de desenvolvimento).
