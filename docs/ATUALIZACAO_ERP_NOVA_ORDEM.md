# CarWash Express — Nova ordem estilo ERP

## Implementação

- Tela de criação de OS refeita em tema claro, layout responsivo em duas colunas, painel de resumo fixo no desktop, foco imediato na placa e atalhos de teclado (`/` pesquisa; `Ctrl+Enter` registra).
- Serviços de lavagem selecionáveis por um clique; exige exatamente uma lavagem por ordem.
- Produtos adicionais (bebidas, lanches e outros) com busca, filtro, botões de quantidade `+` / `−` de 0 a 100 e preço dependente do veículo.
- O total é calculado em **centavos** no navegador, sem requisições de rede a cada interação.
- Pagamento opcional ou dividido, com campos inline; não usa `prompt`/`alert`.
- Backend utiliza `items[ID]=quantidade`, valida a empresa, situação ativo, quantidade, uma única lavagem, preços por categoria do veículo e total de pagamentos. O total da OS e os subtotais são **calculados pelo servidor**, não pelo browser.
- Persistência usando `service_order_items.quantity`, `unit_price` e `total_price`, **sem alterar tabelas existentes**.
- Recibo e listagens de ordens e entregas mostram as quantidades de produtos (ex.: `3× Água`) e os subtotais corretos.
- Diretórios operacionais necessários em `storage/framework` restaurados com placeholders, pois alguns arquivos ZIP não os incluem.

## Como aplicar

1. Faça backup do código e do banco.
2. Substitua os arquivos do projeto pelo conteúdo do ZIP, **sem sobrescrever seu `.env`**.
3. Na pasta do Laravel:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan optimize:clear
php artisan view:cache
php artisan route:cache
```

Não é necessário executar novas migrations para habilitar as quantidades: o schema antigo já contém `service_order_items.quantity`.

## Teste operacional

1. Acesse `/operator/create-order`, selecione o veículo e a lavagem.
2. Adicione **3 águas** e **2 refrigerantes**. Verifique `3×` e `2×` no resumo.
3. Alterne Carro/SUV/Picape; confira o preço atualizado na tela.
4. Lance um Pix parcial e dinheiro como saldo restante; salve.
5. Confira a OS na listagem, na impressão do recibo e no painel de entregas.
6. Confira no banco:

```sql
SELECT soi.product_id, p.name, soi.quantity, soi.unit_price, soi.total_price
FROM service_order_items soi
JOIN products p ON p.id = soi.product_id
WHERE soi.service_order_id = <ID_DA_ORDEM>;
```

## Observações

- **O sistema atualmente não possui controle quantitativo de estoque físico na tabela `products`**, então esta alteração registra quantidades vendidas e subtotais sem fazer baixas de estoque inexistentes.
- Se a instalação usa PHP sem a extensão `dom`, os comandos Artisan de cache formatado podem falhar; habilite `php-xml` / `php-dom` no ambiente de deploy.
- Fluxo existente dos veículos preservado: não fica em espera = `finished`; fica em espera = `waiting` e posterior entrega de chaves.
