##  Rebalance API

Проект выполнен как тестовое задание для практики 2026 года, представляет собой API‑сервис на Symfony 7.4 и Doctrine ORM, который хранит список машин и процессов, назначает процессы на машины по доступным ресурсам (RAM/CPU) и выполняет ребалансировку при изменении состава машин/процессов.

Прошлогоднее тестовое задание: https://github.com/purpurrya/farpost_test_backend_2025.

### Стек

- PHP 8.2+ (в Docker — 8.3), Symfony 7.x  
- Doctrine ORM
- MySQL 8, Docker Compose (nginx + php-fpm)  
- Документация API: Nelmio / OpenAPI (`config/packages/nelmio_api_doc.yaml`)

### Развёртывание

Скрипт для быстрого запуска:

```bash
./scripts/docker-up.sh
```

---

Вручную, поэтапно:

```bash
cp .env.example .env
```

Переменные MYSQL_* и DEFAULT_URI в .env.example уже согласованы с Docker, для первого запуска можно ничего не менять, если задаёте свои пароли — сделайте это до создания тома MySQL.

Папка vendor в репозитории не хранится: при первом старте контейнера app выполняется composer install  (bind-mount ./ перекрывает vendor из образа). Подождите пару минут, пока установка завершится.

```bash
docker compose up -d --build
docker compose logs -f app
```

После этого выполните миграции:

```bash
docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

API: **http://127.0.0.1:8080**

#### Проверка API

- **Swagger (POST и GET):** UI - **http://127.0.0.1:8080/api/doc**, схема - **http://127.0.0.1:8080/api/doc.json**.
- **curl:**

```bash
curl -sS -X POST http://127.0.0.1:8080/api/machines \
  -H 'Content-Type: application/json' \
  -d '{"totalMemory":1024,"totalCpu":2}'
```

Тесты: в **`phpunit.dist.xml`** задан `DATABASE_URL` для окружения с Docker; пароль root должен совпадать с **`MYSQL_ROOT_PASSWORD`** в вашем `.env`.

```bash
docker compose exec app php vendor/bin/phpunit
```

### API

Префикс у методов: `/api`.

Машины, базовый путь `/api/machines`:

- `POST /api/machines` — добавить машину  
- `GET /api/machines/{id}` — информация о конкретной машине  
- `DELETE /api/machines/{id}` — удалить машину  

Процессы, базовый путь `/api/processes`:

- `POST /api/processes` — добавить процесс  
- `GET /api/processes` — список всех процессов  
- `GET /api/processes/unallocated` — нераспределенные процессы
- `DELETE /api/processes/{id}` — удалить процесс  

Статус, базовый путь `/api/status`:

- `GET /api/status` — текущее состояние сервиса 
- `GET /api/status/machines` — список машин с метриками  
- `GET /api/status/processes` — процессы и пих размещение 

Разделение бизнес-логики выполнено согласно принципам SOLID, к каждой сущности обственный контроллер (+контроллер для распределения). Обработка бизнес-логики делегирована сервисам, работа с БД - репозиториям, в качестве прослойки-абстракции добавлены интерфейсы.
Добавлены функциональные тесты, в качестве подхода выбран REST API, хотя хотелось бы RPC, но операции просты и используют множество методов. 
Также решена проблема нераспределенных процессов: они добавляются в ожидание (фильтр по null просто) и остаются там до ребаланса и появления свободной машины. 
Тесты завязаны на реальной бд, ходят через Doctrine к живому MySQL.
