# CLAUDE.md

Этот файл содержит инструкции для Claude Code (claude.ai/code) при работе с данным репозиторием.

## О проекте

Voloshin CRM — приложение на Laravel 12 + Vue 3 для управления шаблонами ценников продуктов. Включает систему RBAC, OAuth 2.0 аутентификацию для API и редактор шаблонов с drag-and-drop.

## Команды

### Разработка

```bash
composer run dev        # Запустить все сервисы одновременно (Laravel + очередь + Vite)
php artisan serve       # Только Laravel dev-сервер
npm run dev             # Только Vite HMR dev-сервер
```

### Сборка

```bash
npm run build           # Сборка фронтенда (Vite + Rolldown)
```

### Тестирование

```bash
composer run test       # Очищает кеш конфига, затем запускает все Pest-тесты
php artisan test --filter=SomeTestClass        # Запуск одного тестового класса
php artisan test tests/Feature/SomeTest.php   # Запуск одного тестового файла
```

### Линтинг и стиль кода

```bash
npm run lint            # Проверка ESLint
npm run lint:fix        # Автоисправление ESLint
php artisan pint        # Laravel Pint (форматирование PHP-кода)
```

### База данных

```bash
php artisan migrate                          # Запустить миграции
php artisan db:seed                          # Засеять роли, права, admin-пользователя
php artisan db:seed --class=PermissionSeeder
php artisan passport:install                 # Сгенерировать OAuth-ключи (нужно при первом запуске)
```

## Первоначальная настройка

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan passport:install
```

## Архитектура

### Аутентификация и авторизация

- **Web-маршруты**: сессионная аутентификация (middleware `auth`)
- **API-маршруты**: Laravel Passport OAuth 2.0 (guard `auth:api`)
- **RBAC**: Spatie Permission — роли и права хранятся в БД, кешируются на 24 часа
- Admin-пользователь создаётся через `AddAdminUserSeeder`; root-роль — через `RoleRootSeeder`
- Защита маршрутов: `middleware('role:root')`

### Бэкенд

- **Сервисный слой**: `app/Services/` — например, `UserService` содержит бизнес-логику создания пользователей, отделённую от контроллеров
- **Валидация форм**: `app/Http/Requests/` — типизированные классы `FormRequest` на каждое действие
- **Базовая модель**: `app/Models/BaseModel.php` — общая логика для Eloquent-моделей
- **Middleware**: `setUserLanguage` устанавливает локаль из колонки `users.lang`; ролевой middleware через Spatie

### Фронтенд

- Vue 3 Composition API + TypeScript, сборка через Vite
- Tailwind CSS 4 + DaisyUI 5 для стилей
- Редактор шаблонов в `resources/editor/` — drag-and-drop (vue-draggable-resizable), поддержка элементов текста, штрихкода, изображений с предпросмотром в реальном времени
- Устаревший JS в `resources/assets/js/`

### Локализация

- Используется только украинский (`lang/uk/`); локаль задаётся для каждого пользователя из БД

### CI/CD

- GitLab CI (`.gitlab-ci.yml`) деплоит в окружения `dev` и `production`
- Деплой выполняет: composer install, yarn build, миграции, сидеры, настройку ключей Passport, перезапуск supervisor для воркеров очереди

## Ключевые настройки

| Параметр | Значение |
|----------|----------|
| База данных | MySQL, имя БД: `price_display` |
| Сессии | Хранятся в БД, время жизни: 120 минут |
| Кеш | Хранится в БД |
| Очередь | Хранится в БД (требуется запущенный воркер) |
| Guard'ы аутентификации | `web` (сессия), `api` (Passport) |
