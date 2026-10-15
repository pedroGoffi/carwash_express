# CarWash Express — atualização Laravel (09/10/2026)

## Correções confirmadas no arquivo de logs

- `SQLSTATE[42P01]`: faltava `payroll_histories`; resolvido com migration aditiva/condicional.
- `SQLSTATE[42703]`: faltava `service_orders.delivered_at`; migration adiciona `queued_at`, `delivered_at` e `finished_at` apenas quando ausentes.
- `Redis` (extensão PHP ausente): cache resiliente agora não tenta phpredis quando a extensão está ausente, usando o cache de arquivos.
- `Vite manifest not found`: requer `npm ci && npm run build` no host de produção; não criar um manifest falso.

## Fluxos alterados

1. Cadastro de funcionário em `/manager/users/create` ou `/manager/users/{id}/edit` agora reúne identificação, contato, função, vínculo, admissão, salário mensal, diária, dias trabalhados, chave PIX, acesso e observações. O `name` continua sendo o login do Laravel; não há coluna `username` separada.
2. `/manager/payroll` centraliza folha, vales, consumos, despesas extras e filtro exclusivo de funcionário. Acesse o extrato por funcionário em `?month=2026-10&user_id=ID&tab=movements`.
3. Despesa extra (`employee_expenses`) **não desconta** salário; o custo estimado da empresa é salário base + despesas extras lançadas, sem encargos automáticos. Vales e consumos reduzem o salário líquido. Regras trabalhistas, impostos e provisões não são calculados automaticamente.
4. O histórico de folha só é carregado ao abrir a aba Histórico, e extratos são paginados.
5. Nova ordem oferece duas opções: **Concluído** (para veículos que não ficarão em espera) e **Em espera** (chaves retidas). Ordens em espera podem ser concluídas; concluídas aguardam a ação de entrega, que muda o estado para Entregue. Antigas ordens `queued` continuam compatíveis.
6. Página de relatórios: bug do `event` global corrigido, gráficos Chart.js carregados sob demanda, dados da tabela exibidos sem depender da biblioteca de gráficos.
7. Layout branco, menu claro, componentes e formulários padronizados, aviso de navegação imediato e carregamento de imagens abaixo da dobra sob demanda.

## Instalação segura

**Fazer backup do PostgreSQL e da pasta do projeto antes de atualizar!**

```bash
# No diretório do projeto atualizado
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan view:cache
php artisan route:cache
```

`npm ci`/`npm run build` são indispensáveis em produção para gerar `public/build/manifest.json`. Em desenvolvimento, alternativamente use `npm run dev` em um terminal separado (Vite).

Não execute `php artisan migrate:fresh` nem `db:wipe`. O novo cadastro contém dados pessoais: garanta HTTPS, permissões de acesso e backups.

## Testes feitos neste pacote

- `php -l` para controllers, modelos, rotas e migrations: sem erros.
- `php artisan route:list --json`: rotas Laravel registradas e nomes verificados contra os templates.
- Compilação estática de Blade: templates de aplicação novos compilam; templates de autenticação requerem a extensão PHP `mbstring`, ausente neste ambiente.
- Sintaxe do JavaScript de relatórios, dashboard, entrega e ordem verificada pelo Node.

**Limitação:** não houve teste integrado com o PostgreSQL do usuário nem teste visual no navegador porque este ambiente não dispõe da conexão e das extensões PHP (`pdo_pgsql`, `mbstring`, `dom`). O `npm ci` não concluiu aqui por acesso a pacotes, então os assets Vite não foram gerados. Não é possível garantir a execução de ponta a ponta sem rodar as migrations, compilar os assets e validar os fluxos em sua instalação.

## Checklist funcional após deploy

1. Gerente acessa `/manager/payroll` sem erro 500.
2. Cadastrar e editar colaborador, remuneração e status ativo.
3. Lançar vale, consumo e despesa extra em um funcionário; conferir filtro de extrato e totais.
4. Fechar competência e consultar histórico.
5. Criar ordem marcada Concluído e localizar na lista de entregas.
6. Criar ordem Em espera, concluir, entregar chaves e verificar status Entregue.
7. Abrir `/manager/reports` e alternar períodos; conferir gráficos.
8. Conferir navegação e responsividade em celular.
