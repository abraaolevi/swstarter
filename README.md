Arch decision
- Laravel for backend
  - Docker
  - PostgreSQL
  - Cache for search results and details
- React for frontend
  - CSS modules
  - TanStack
  - Search debounce and cache in frontend

In dev:
```
docker compose up -d
```

Test
```
docker-compose exec backend php artisan test
````

Versão de prod
```
docker compose -f docker-compose.prod.yaml up -d --build
```
