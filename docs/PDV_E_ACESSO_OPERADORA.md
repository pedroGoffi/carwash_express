# CarWash Express — PDV balcão e acesso da operadora

## O que foi implementado

1. **Nova ordem / Venda balcão (PDV)**: na tela `Nova ordem`, escolha o modo **Venda balcão · PDV**, ou use a entrada direta do menu. O PDV aceita somente bebidas, lanches e outros produtos, sem lavagem, veículo ou placa. O cliente é opcional.
2. **Carrinho, preços e pagamentos**: informe quantidades e receba por Pix, dinheiro, cartão de crédito, débito ou pagamentos divididos; também é possível registrar pagamento posteriormente. Os preços e a titularidade dos produtos são novamente validados no servidor. Produtos com preço zerado não são exibidos como vendáveis no PDV.
3. **Histórico/recibo**: cada venda possui número de OS, itens, pagamentos e recibo; aparece identificada como PDV. O status da venda balcão fica `delivered` imediatamente, sem ocupar a fila de chaves/entregas. Vendas podem ser filtradas separadamente na listagem de ordens.
4. **Resumo diário D-1 e dashboard**: contagem de lavagens e tipos de veículo exclui vendas balcão; contagem e valor do PDV aparecem separadamente. Bebidas vendidas e recebimentos incluem os lançamentos correspondentes ao PDV. O painel de resumo detalhado é ocultado ao entrar no modo PDV para abrir o caixa diretamente.
5. **Acesso da operadora**: produtos (cadastro/edição), equipe (cadastro/edição), folha e custos, vales, consumo de produtos, despesas de funcionários e custos operacionais estão acessíveis também para `operator`. Dados permanecem limitados à empresa do usuário.
6. **Ações de gestão reservadas**: fechamento da competência, dashboard gerencial e relatórios gerenciais continuam com `admin/manager`. Permissões são checadas no servidor, não apenas na navegação.
7. **Histórico preservado**: remover um produto já usado em ordens ou consumos o desativa em vez de destruir suas referências; a categoria de um produto já vendido não pode ser alterada para evitar reclassificação de vendas anteriores.

## Banco de dados

A migração `2026_10_16_020000_add_order_type_to_service_orders.php` adiciona `service_orders.order_type`, com valor padrão `service` para ordens antigas e `sale` para vendas do PDV. É necessário executar a migração após atualizar o código. Não é necessário apagar nem recriar o banco.

O campo técnico `vehicle_type` das vendas balcão armazena o valor-base `normal` por compatibilidade com o esquema existente, mas **não indica um veículo atendido**: para indicadores e relatórios de lavagens, utilize `order_type = 'service'`.

## Instalação (Docker Compose atual)

Preserve o `.env` existente da instalação, o banco de dados e os volumes. Este ZIP não inclui `.env` nem `.git` por segurança.

```bash
# No diretório do projeto, com o .env original preservado:
docker compose up -d --build
docker compose exec web php artisan migrate --force
docker compose exec web php artisan optimize:clear
```

Em instalação local:

```bash
composer install
php artisan migrate --force
npm ci
npm run build
```

## Validação funcional recomendada

- Cadastre bebida com preço positivo em Produtos.
- Em **PDV · Venda balcão**, venda 2 ou mais bebidas sem informar placa, veículo ou lavagem.
- Confira a lista de ordens, o recibo, o recebimento e o resumo diário.
- Confirme que a venda não aparece em **Entregas** nem na contagem de veículos/lavagens.
- Teste como operadora os cadastros da equipe e produtos, lançamento de vale, consumo de produto, despesa de funcionário e custo operacional.
- Confirme que operadora não consegue fechar competência nem acessar Dashboard/Relatórios gerenciais.
- Execute os testes automatizados após instalar as dependências:

```bash
php artisan test --filter=PointOfSaleAndOperatorAccessTest
```

**Verificação feita na entrega:** sintaxe PHP e JavaScript. A suíte PHPUnit não foi executada neste ambiente, porque a pasta `vendor` não está incluída no arquivo de origem e não havia `phpunit`/Composer disponíveis.
