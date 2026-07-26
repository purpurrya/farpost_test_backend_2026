# Rebalance API

Тестовое задание для практики 2026 года.

Проект представляет собой API-сервис на Symfony 7 и Doctrine ORM, который хранит список машин и процессов, назначает процессы на машины с учетом доступных ресурсов (RAM и CPU) и выполняет ребалансировку при изменении состава машин или процессов.

Прошлогоднее тестовое задание: https://github.com/purpurrya/farpost_test_backend_2025

## Технологии

- PHP 8.2+ (Docker — PHP 8.3)
- Symfony 7
- Doctrine ORM
- MySQL 8
- Docker Compose (nginx + php-fpm)
- NelmioApiDocBundle (OpenAPI / Swagger)

## Развёртывание

### Быстрый запуск

```bash
./scripts/docker-up.sh
```

### Ручной запуск

```bash
cp .env.example .env

docker compose up -d --build

# Дождаться завершения composer install
docker compose logs -f app

docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

API: http://127.0.0.1:8080

## Документация API

- Swagger UI: http://127.0.0.1:8080/api/doc
- OpenAPI Schema: http://127.0.0.1:8080/api/doc.json

Пример запроса:

```bash
curl -X POST http://127.0.0.1:8080/api/machines \
  -H "Content-Type: application/json" \
  -d '{"totalMemory":1024,"totalCpu":2}'
```

## Запуск тестов

В `phpunit.dist.xml` задан `DATABASE_URL` для Docker-окружения.

```bash
docker compose exec app php vendor/bin/phpunit
```

## API

Все методы доступны с префиксом `/api`.

### Машины (`/api/machines`)

- `POST /machines` — добавить машину
- `GET /machines/{id}` — получить информацию о машине
- `DELETE /machines/{id}` — удалить машину

### Процессы (`/api/processes`)

- `POST /processes` — добавить процесс
- `GET /processes` — список процессов
- `GET /processes/unallocated` — список нераспределённых процессов
- `DELETE /processes/{id}` — удалить процесс

### Статус (`/api/status`)

- `GET /status` — текущее состояние сервиса
- `GET /status/machines` — список машин с метриками
- `GET /status/processes` — процессы и их размещение

## Особенности реализации

- Реализована автоматическая ребалансировка процессов.
- Если процесс невозможно разместить, он сохраняется как нераспределённый (`machine_id = NULL`) и автоматически участвует в следующем ребалансе после появления свободных ресурсов.
- Добавлены функциональные тесты.
- Тесты выполняются с использованием реальной базы данных MySQL через Doctrine без использования моков.
