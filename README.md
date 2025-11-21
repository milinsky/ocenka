# Ocenka - Интернет-магазин услуг оценки

Веб-приложение для заказа услуг оценки (автомобилей, квартир, бизнеса) на Symfony с REST API, административной панелью и документацией через OpenAPI/Swagger.

## Технологический стек

### Backend
- **PHP**: 8.4
- **Framework**: Symfony 7.x
- **БД**: MySQL 8.0
- **Docker**: PHP-FPM, Nginx, MySQL
- **Authentication**: JWT (lexik/jwt-authentication-bundle)
- **API Docs**: OpenAPI 3.0 + Swagger UI
- **Testing**: PHPUnit + WebTestCase

### Frontend
- **Template Engine**: Twig
- **CSS**: Bootstrap 5 (чистая вёрстка)
- **JavaScript**: Vanilla JS (ES6+)
- **HTTP Client**: Fetch API (без сторонних библиотек)
- **Asset Management**: Symfony AssetMapper
- **Build Tool**: Terser (JS minification)

## Требования

- Docker
- Docker Compose V2 (или Docker Desktop)

## Установка и запуск

### 1. Запуск Docker контейнеров

```bash
# Сборка и запуск контейнеров
docker compose up -d --build

# Проверка статуса контейнеров
docker compose ps
```

### 2. Установка зависимостей

```bash
# Вход в PHP контейнер
docker compose exec php-fpm bash

# Установка PHP зависимостей через Composer
composer install

# Генерация JWT ключей
php bin/console lexik:jwt:generate-keypair

# Установка Node.js зависимостей (для минификации JS)
npm install
```

### 3. Настройка базы данных

```bash
# Создание базы данных
docker compose exec php-fpm php bin/console doctrine:database:create

# Выполнение миграций
docker compose exec php-fpm php bin/console doctrine:migrations:migrate --no-interaction

# Загрузка фикстур (демо-данные)
docker compose exec php-fpm php bin/console doctrine:fixtures:load --no-interaction
```

### 4. Запуск тестов

```bash
# Создание тестовой БД
docker compose exec php-fpm php bin/console --env=test doctrine:database:create

# Запуск миграций для тестовой БД
docker compose exec php-fpm php bin/console --env=test doctrine:migrations:migrate --no-interaction

# Запуск тестов
docker compose exec php-fpm bin/phpunit
```

## Доступ к приложению

### Веб-интерфейс
- **Главная страница с формой заказа** (публичная): http://localhost
- **Страница входа для админов**: http://localhost/admin/login
- **Админ панель с заказами** (требует авторизацию): http://localhost/admin
- **Swagger UI** (только в dev режиме): http://localhost/api/doc

### Инфраструктура
- **MailHog Web UI** (просмотр отправленных писем): http://localhost:8025
- **База данных MySQL**: localhost:3306
  - User: symfony
  - Password: symfony
  - Database: symfony

### Учётные данные администраторов

- **Admin**: admin@example.com / password123

**Примечание:** Обычные пользователи (клиенты) НЕ могут регистрироваться и входить на сайт. Они используют только публичную форму заказа на главной странице.

## Полезные команды

```bash
# Остановка контейнеров
docker compose stop

# Запуск контейнеров
docker compose start

# Перезапуск контейнеров
docker compose restart

# Остановка и удаление контейнеров
docker compose down

# Остановка и удаление контейнеров с volumes
docker compose down -v

# Просмотр логов
docker compose logs -f

# Просмотр логов конкретного сервиса
docker compose logs -f php-fpm
docker compose logs -f nginx
docker compose logs -f db

# Вход в контейнер PHP
docker compose exec php-fpm bash

# Вход в контейнер MySQL
docker compose exec db mysql -u symfony -p

# Выполнение команд Symfony
docker compose exec php-fpm php bin/console cache:clear
docker compose exec php-fpm php bin/console debug:router

# Build assets для production
npm run build
```

## Troubleshooting

### Проблемы с правами доступа

```bash
# Изменить владельца файлов
docker compose exec php-fpm chown -R www-data:www-data /var/www/symfony/var
```

### Очистка кеша

```bash
docker compose exec php-fpm php bin/console cache:clear
docker compose exec php-fpm php bin/console cache:warmup
```

### Пересборка контейнеров

```bash
docker compose down
docker compose build --no-cache
docker compose up -d
```

## API Endpoints

### Публичные (без авторизации)
- `GET /api/services` - Список услуг
- `POST /api/orders` - Создание заказа (для клиентов)
- `POST /api/auth/login` - Авторизация админа (возвращает токен)

### Защищённые (требуется токен авторизации)
- `POST /api/auth/logout` - Выход
- `POST /api/auth/password-reset-request` - Запрос восстановления пароля
- `POST /api/auth/password-reset` - Восстановление пароля
- `GET /api/orders` - Список всех заказов
- `GET /api/orders/{id}` - Получение заказа
- `PUT /api/orders/{id}` - Обновление заказа
- `DELETE /api/orders/{id}` - Удаление заказа

**Авторизация:** Для защищённых endpoints используйте header `Authorization: Bearer {token}`

Полная документация доступна в Swagger UI: http://localhost/api/doc
