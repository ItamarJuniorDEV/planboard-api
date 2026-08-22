# Planboard API

![CI](https://github.com/ItamarJuniorDEV/planboard-api/actions/workflows/ci.yml/badge.svg)
![License](https://img.shields.io/badge/License-MIT-green)

API REST em Laravel para gerenciamento de projetos em estrutura kanban, com autenticação, autorização por recurso, operações em lote e estatísticas por projeto.

## Funcionalidades

- projetos, quadros e colunas;
- tarefas com prioridade, status, filtros, ordenação e movimentação entre colunas;
- subtarefas, comentários, marcos e etiquetas;
- operações em lote para tarefas, subtarefas e comentários;
- estatísticas de tarefas, subtarefas e marcos por projeto;
- autenticação com Laravel Sanctum;
- administração de usuários restrita ao papel `admin`.

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.3+, Laravel 12 |
| Autenticação | Laravel Sanctum |
| Banco | MySQL 8 / SQLite |
| Documentação | Dedoc Scramble / OpenAPI |
| Testes | PHPUnit 11 |
| Qualidade | Laravel Pint, Larastan, Rector |
| Infra | Docker, GitHub Actions |

## Como rodar

O projeto usa SQLite localmente por padrão.

```bash
git clone https://github.com/ItamarJuniorDEV/planboard-api.git
cd planboard-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

API: `http://localhost:8000/api`

Documentação interativa: `http://localhost:8000/docs/api`

### MySQL com Docker

O `docker-compose.yml` disponibiliza MySQL 8 na porta `3309` e phpMyAdmin na porta `8888`.

```bash
docker compose up -d
```

Para usar MySQL, ajuste as variáveis `DB_*` do `.env` para o serviço configurado no compose.

## Autenticação e autorização

O login possui rate limiting específico e emite tokens Sanctum com validade de 8 horas. As demais rotas da API exigem autenticação e também possuem limitação de requisições.

Projetos são isolados pelo usuário autenticado. Operações sobre recursos individuais passam por Policies e as rotas aninhadas usam `scopeBindings()` para manter quadros, colunas, tarefas e recursos relacionados dentro do projeto informado.

A listagem e o gerenciamento de usuários são administrativos. Eventos de login e logout são registrados sem armazenar senha ou e-mail em claro nos logs de segurança adicionados pela aplicação.

## Consistência e desempenho

O endpoint de estatísticas agrega tarefas por status e prioridade, progresso de subtarefas e marcos vencidos. O cálculo e o cache ficam centralizados em `ProjectStatsService`.

O cache dura 60 segundos e é invalidado quando tarefas, subtarefas ou marcos mudam, inclusive nas operações em lote que não disparam eventos individuais do Eloquent.

As consultas mais frequentes possuem índices compostos para filtros por usuário/projeto, status, prioridade, deadline e data de marco. Paginação é limitada pelos Form Requests e os termos de busca aceitam no máximo 100 caracteres.

Operações em lote aceitam até 100 IDs por requisição. Descrições de projetos e tarefas são limitadas a 5.000 caracteres e comentários a 2.000.

## Testes e qualidade

```bash
php artisan test
```

A suíte de Feature Tests cobre autenticação, autorização, isolamento entre usuários, CRUDs principais, movimentação de tarefas, operações em lote, invalidação de cache e comportamentos de segurança.

O CI executa testes em PHP 8.3 e 8.4, valida estilo com Pint, análise estática com Larastan nível 6 e Rector em `dry-run`. O workflow de segurança executa auditoria de dependências do Composer e varredura de segredos com Gitleaks.

## Segurança

A API aplica autenticação, Policies, route model binding escopado, rate limiting, expiração de tokens, headers de segurança, CORS com origens configuráveis e validação de payloads. O repositório também mantém verificações automatizadas de dependências e segredos.

A política para reporte de vulnerabilidades está em [SECURITY.md](SECURITY.md).

## Licença

MIT.
