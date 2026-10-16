# CarWash Express — caixa e lavagem em uma única tela

## Tela de lançamento

Acesse **Operação → Novo lançamento · PDV + Lavagem** (`/operator/create-order`).
Há **uma única tela** e **um único carrinho** para a operadora, sem alternância entre modos. Na tela:

1. **Bebidas e produtos:** busque ou use `+` e `−` para alterar a quantidade (até 100 por item).
2. **Lavagens:** selecione a categoria do veículo e, caso necessário, a lavagem principal. A placa, o status do veículo e os serviços adicionais aparecem automaticamente. Clicar novamente na principal retira a lavagem e seus adicionais; bebidas permanecem no pedido.
3. **Cliente:** identificação opcional. Placa obrigatória somente nas ordens que contêm lavagens.
4. **Carrinho e pagamento:** total calculado na mesma hora; Pix, dinheiro, crédito, débito e pagamentos parciais. Produtos e serviços podem ser pagos juntos.
5. **Salvar e novo:** grava a transação, exibe a confirmação/comprovante e reabre imediatamente o formulário limpo. **Salvar e consultar ordens:** grava e abre a listagem.

Atalhos: `/` foca a busca por produto (fora dos campos de digitação); `Ctrl + Enter` salva e abre o próximo lançamento. O resumo histórico permanece em **Resumo diário** (com seleção de data/D-1), fora do caminho de atendimento rápido.

## Regras de integridade (servidor)

O controller determina `order_type` a partir dos produtos *válidos* cadastrados na empresa e não confia no valor enviado pelo cliente:

- Sem itens `wash` → `sale`, sem placa, sem fila de entrega, status `delivered`.
- Com ao menos um `wash` → `service`, requer placa, tipo de veículo, situação e **uma lavagem principal**; adicionais aceitos uma vez por serviço.
- A mesma ordem pode conter uma lavagem, seus adicionais e qualquer quantidade permitida de bebidas/produtos.
- Os preços são recalculados no servidor, os itens devem pertencer à empresa autenticada e os pagamentos não podem exceder o total.
- As vendas balcão continuam separadas dos KPIs de lavagens e não aparecem na fila de chaves.
- `order_type` permanece no POST para compatibilidade com formulários antigos, mas não define o tipo da operação.

## Implantação

Atualize os arquivos da aplicação preservando `.env` e os dados, e execute:

```bash
docker compose up -d --build
docker compose exec web php artisan migrate --force
docker compose exec web php artisan optimize:clear
```

## Validação

- Teste de comportamento do frontend com Chromium/Playwright em ambiente isolado (bebidas → lavagem+adicional → SUV → volta ao PDV sem perder bebidas, pagamentos e botão Salvar e novo).
- Verificação de sintaxe PHP e do JavaScript de criação de OS.
- Cenários de backend adicionados em `tests/Feature/PointOfSaleAndOperatorAccessTest.php`: tipo inferido automaticamente, pedido misto, redirecionamento para próximo registro, placa obrigatória com lavagem, produtos de outras empresas.
- PHPUnit requer `vendor/` instalado e banco de testes configurado; não foi executado no ambiente de montagem.
