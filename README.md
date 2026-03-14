# MiniCRM

Система учёта производственной деятельности для небольших предприятий. Позволяет фиксировать проделанную работу каждого сотрудника, отслеживать затраченное время, контролировать выполнение задач и получать полную картину загруженности производства.

Предназначена для небольших производств, где важно понимать: кто, что и сколько времени делал — без лишней бюрократии.

## Возможности

- Учёт выполненных работ по каждому сотруднику
- Привязка задачи к типу работ (вид деятельности)
- Учёт количества обработанных товаров/единиц продукции
- Отслеживание времени выполнения (запланированное и фактическое)
- Контроль статуса задачи (выполнено / не выполнено)
- Администратор видит работу всех сотрудников с фильтрацией
- Каждый сотрудник ведёт только свой учёт
- Гибкая система ролей и прав доступа (RBAC)
- Интерфейс на русском языке

## Разделы

### Задачи
Основной рабочий раздел. Сотрудник фиксирует выполненную работу: тип работы, количество товаров, время начала и окончания, статус (выполнено / не выполнено). Поддерживает фильтрацию по сотруднику, типу работ и статусу.

### Типы работ
Справочник видов деятельности (Activity). Каждый тип имеет название и slug. Используется при создании задач.

### Пользователи
Управление учётными записями сотрудников. Доступно только администратору (root). Поддерживает назначение ролей и установку языка интерфейса для каждого пользователя.

### Роли
Управление ролями пользователей. При создании роли назначаются права доступа из списка. Доступно только администратору.

### Права доступа
Управление правами, сгруппированными по модулям (activity, task). Доступно только администратору.

### Дашборд
Аналитический раздел с графиком производительности сотрудников. Отображает количество произведённой продукции в разбивке по дням. Поддерживает фильтрацию по периоду (daterangepicker) и сотрудникам. Администратор (root) видит всех сотрудников и может фильтровать по любым из них; обычный сотрудник видит только свою статистику.

## Роли пользователей

| Роль | Возможности |
|------|-------------|
| **root** | Полный доступ: пользователи, роли, права, все задачи |
| **Обычный пользователь** | Доступ определяется назначенными правами; видит только свои задачи |

## Стек технологий

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Blade + Tailwind CSS 4 + DaisyUI 5
- **БД**: MySQL
- **Аутентификация**: сессии (web) + Laravel Passport OAuth 2.0 (API, зарезервировано)
- **RBAC**: Spatie Laravel Permission
- **Очереди/Кеш/Сессии**: хранятся в БД

---

## Локальная установка

```bash
# 1. Клонировать репозиторий
git clone <repo-url>
cd mini-crm.local

# 2. Установить зависимости
composer install

# 3. Настроить окружение
cp .env.example .env
php artisan key:generate
```

Настроить `.env`:

```env
APP_URL=http://mini-crm.local

DB_HOST=127.0.0.1
DB_DATABASE=mini_crm
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 4. Создать базу данных и запустить миграции
php artisan migrate
php artisan db:seed
php artisan passport:install
```

Данные администратора задаются в `config/add_user.php` перед запуском `db:seed`.

### Запуск для разработки

```bash
composer run dev   # Laravel + воркер очереди
```

---

## Деплой на VPS

### Требования к серверу

- Ubuntu 22.04+
- PHP 8.2+ с расширениями: `mbstring`, `xml`, `curl`, `zip`, `bcmath`, `pdo_mysql`, `intl`
- Composer
- MySQL 8+
- Nginx
- Supervisor (для воркера очереди)

### 1. Установка зависимостей (Ubuntu)

```bash
# PHP 8.2
sudo apt update
sudo apt install -y software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt install -y php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml \
    php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl php8.2-tokenizer

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# MySQL
sudo apt install -y mysql-server

# Nginx
sudo apt install -y nginx

# Supervisor
sudo apt install -y supervisor
```

### 2. База данных

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE mini_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'crm_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON mini_crm.* TO 'crm_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Код приложения

```bash
cd /var/www
git clone <repo-url> minicrm
cd minicrm

composer install --no-dev --optimize-autoloader

cp .env.example .env
php artisan key:generate
```

Настроить `/var/www/minicrm/.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_HOST=127.0.0.1
DB_DATABASE=mini_crm
DB_USERNAME=crm_user
DB_PASSWORD=strong_password

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
```

```bash
php artisan migrate --force
php artisan db:seed --force
php artisan passport:install
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Права на директории
sudo chown -R www-data:www-data /var/www/minicrm
sudo chmod -R 755 /var/www/minicrm/storage
sudo chmod -R 755 /var/www/minicrm/bootstrap/cache
```

### 4. Nginx

Создать файл `/etc/nginx/sites-available/minicrm`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/minicrm/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/minicrm /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. Supervisor (воркер очереди)

Создать файл `/etc/supervisor/conf.d/minicrm-worker.conf`:

```ini
[program:minicrm-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/minicrm/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/minicrm/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start minicrm-worker:*
```

### 6. Обновление на сервере

```bash
cd /var/www/minicrm

git pull origin master
composer install --no-dev --optimize-autoloader

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

sudo supervisorctl restart minicrm-worker:*
```

---

## Полезные команды

```bash
# Тесты
composer run test

# Линтинг PHP
php artisan pint

# Сброс кеша
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```
