# app-crypto-news

### 1. Run docker compose

```
docker compose up -d
```

### 2. Run docker shell

```
docker compose exec -it app sh
```
```
php artisan migrate

php artisan db:seed

php artisan app:fetch-data-news

php artisan app:redis-data-sync-command

php artisan schedule:work
```

### 3. Run Laravel App
```
https://localhost
or 
https://crypto-news.test/
```

### 4. Run PhpMyAdmin
```
http://localhost:8080/
```

