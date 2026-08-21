# Planboard API

![CI](https://github.com/ItamarJuniorDEV/planboard-api/actions/workflows/ci.yml/badge.svg)
![License](https://img.shields.io/badge/License-MIT-green)

API REST para gerenciamento de projetos em estrutura kanban, com autenticação, autorização por recurso, operações em lote e estatísticas por projeto.

## Funcionalidades

- gerenciamento de projetos, quadros e colunas;
- tarefas com prioridade, status e movimentação entre colunas;
- subtarefas, comentários, marcos e etiquetas;
- operações em lote para tarefas, subtarefas e comentários;
- estatísticas de tarefas, subtarefas e marcos por projeto;
- autenticação com Laravel Sanctum;
- gerenciamento de usuários com operações administrativas.

## Stack

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.3+, Laravel 12 |
| Autenticação | Laravel Sanctum |
| Banco | MySQL 8 / SQLite |
| Documentação | Dedoc Scramble / OpenAPI |
| Testes | PHPUnit 11 |
| Infra | Docker, GitHub Actions |

## Como rodar

O projeto vem configurado para usar SQLite localmente por padrão.

```bash
git clone https://github.com/ItamarJuniorDEV/planboard-api.git
cd planboard-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

A API fica disponível em `http://localhost:8000/api`.

A documentação interativa fica em:

```text
http://localhost:8000/docs/api
```

### MySQL com Docker

O `docker-compose.yml` disponibiliza MySQL 8 na porta `3309` e phpMyAdmin na porta `8888`.

```bash
docker compose up -d
```

Para usar o MySQL, ajuste no `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3309
DB_DATABASE=planboard
DB_USERNAME=planboard
DB_PASSWORD=<sua_senha>
DB_ROOT_PASSWORD=<senha_root>
```

## Autenticação e autorização

O login utiliza Laravel Sanctum e possui rate limiting específico. As demais rotas da API exigem autenticação.

A listagem de projetos é restrita ao usuário autenticado. Operações sobre recursos individuais passam por Policies, e as rotas aninhadas utilizam `scopeBindings()` para garantir que quadros, colunas, tarefas e outros recursos sejam resolvidos dentro do projeto informado.

Usuários com papel administrativo podem gerenciar usuários pelas rotas protegidas correspondentes.

## Estatísticas

O endpoint de estatísticas agrega tarefas por status e prioridade, progresso de subtarefas e marcos vencidos.

O resultado é armazenado em cache por 60 segundos. Alterações em tarefas, subtarefas e marcos invalidam a chave de cache do projeto por meio de Observer.

## Testes

```bash
php artisan test
```

A suíte de Feature Tests cobre autenticação, autorização, isolamento entre usuários, projetos, quadros, colunas, tarefas, subtarefas, comentários, marcos, etiquetas, usuários e comportamentos de segurança.

O CI executa os testes em PHP 8.3 e 8.4. O workflow de segurança executa auditoria das dependências do Composer e varredura de segredos com Gitleaks.

## Decisões técnicas

- **Isolamento por usuário:** consultas de projetos são filtradas pelo usuário autenticado e operações individuais passam pelas Policies.
- **Rotas aninhadas:** `scopeBindings()` impede que um recurso de outro projeto seja resolvido por uma URL aninhada incompatível.
- **Cache das estatísticas:** a resposta de estatísticas usa cache curto e invalidação automática quando os dados relevantes mudam.

## Segurança

Além da autenticação e autorização, a API possui rate limiting para login e rotas autenticadas. O repositório também mantém verificações automatizadas de dependências e segredos no GitHub Actions.

A política para reporte de vulnerabilidades está em [SECURITY.md](SECURITY.md).

## Licença

MIT.
