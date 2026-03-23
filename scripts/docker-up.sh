#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

if [[ ! -f .env ]]; then
  cp .env.example .env
  echo "Created .env from .env.example"
fi

echo "Building and starting project..."
docker compose up -d --build

echo "Waiting for app container and vendor..."
ready=0
for _ in $(seq 1 150); do
  if docker compose exec -T app test -f vendor/autoload_runtime.php 2>/dev/null; then
    ready=1
    break
  fi
  sleep 2
done

echo "Running migrations..."
docker compose exec -T app php bin/console doctrine:migrations:migrate --no-interaction

echo "Done. API: http://127.0.0.1:8080  Swagger: http://127.0.0.1:8080/api/doc"
