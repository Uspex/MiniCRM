# Развёртывание MiniCRM на локальном Linux-сервере

Инструкция для **Ubuntu 22.04 LTS** в закрытой локальной сети.
Доступ к приложению — по IP-адресу, без доменного имени.
Все команды выполняются от пользователя **root**.
На сервере используется только PHP + Blade — сборка JS не требуется.

---

## 0. Установка Ubuntu 22.04

### 0.1 Скачать образ

Скачать ISO: https://ubuntu.com/download/server
Выбрать **Ubuntu Server 22.04 LTS**.

### 0.2 Записать на флешку

На Windows использовать **Rufus**: https://rufus.ie/
- Устройство: USB-флешка (минимум 4 ГБ)
- Образ: скачанный `.iso`
- Схема разделов: GPT
- Нажать **Старт**

### 0.3 Установка

1. Загрузиться с флешки (в BIOS/UEFI выставить USB первым)
2. Язык: English
3. Тип установки: **Ubuntu Server**
4. Сеть: настроится через DHCP — **запомнить IP-адрес** (он понадобится)
5. Диск: **Use entire disk** → подтвердить
6. Профиль пользователя:
   - Server name: `mini-crm`
   - Username: любой (можно `deploy`)
   - Password: надёжный пароль
7. OpenSSH: **включить** (для удалённого доступа по SSH)
8. Дополнительные пакеты: ничего не выбирать → Done
9. Дождаться окончания → **Reboot Now** → вытащить флешку

---

## 1. Войти под root

```bash
sudo -i
```

Все дальнейшие команды выполняются от `root`.

---

## 2. Обновление системы

```bash
apt update && apt upgrade -y
```

---

## 3. Установка PHP 8.2

```bash
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl \
  php8.2-zip php8.2-tokenizer php8.2-ctype php8.2-fileinfo
```

> `openssl` не устанавливается отдельно — он встроен в пакет `php8.2`.

Проверка:
```bash
php -v
php -m | grep openssl
```

---

## 4. Установка MySQL

```bash
apt install -y mysql-server
mysql_secure_installation
```

Создать базу данных и пользователя:
```bash
mysql -u root
```
```sql
CREATE DATABASE mini_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'mini_crm'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON mini_crm.* TO 'mini_crm'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 5. Установка Nginx

```bash
apt install -y nginx
```

---

## 6. Установка Composer

```bash
apt install -y curl
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer
```

Проверка:
```bash
composer --version
```

---

## 7. Установка Git

```bash
apt install -y git
```

---

## 8. Перенос кода на сервер

### Вариант А: сервер имеет доступ к интернету

```bash
mkdir -p /var/www
cd /var/www
git clone https://github.com/Uspex/MiniCRM mini-crm
cd mini-crm
```

### Вариант Б: сервер в закрытой сети (нет интернета)

Выполнить на **локальной машине** (Windows, Git Bash или WSL):

```bash
# Собрать архив проекта с vendor/ (зависимости уже установлены локально)
cd C:/OSPanel/domains/mini-crm.local
tar --exclude='.git' --exclude='node_modules' --exclude='storage/logs/*' \
    -czf /tmp/mini-crm.tar.gz .

# Скопировать на сервер (заменить IP)
scp /tmp/mini-crm.tar.gz root@192.168.x.x:/var/www/

# Распаковать на сервере
ssh root@192.168.x.x
mkdir -p /var/www/mini-crm
tar -xzf /var/www/mini-crm.tar.gz -C /var/www/mini-crm
cd /var/www/mini-crm
```

> При этом варианте шаг 10 (`composer install`) **пропустить** — `vendor/` уже в архиве.

---

## 9. Настройка окружения

```bash
cp .env.example .env
nano .env
```

Обязательные параметры (заменить `192.168.x.x` на реальный IP сервера):
```env
APP_NAME=MiniCRM
APP_ENV=production
APP_DEBUG=false
APP_URL=http://192.168.x.x

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mini_crm
DB_USERNAME=mini_crm
DB_PASSWORD=strong_password
```

```bash
chmod 600 .env
```

---

## 10. Установка зависимостей и инициализация

Если сервер имеет доступ к интернету:
```bash
composer install --no-dev --optimize-autoloader
```

Если сервер **без интернета** и `vendor/` уже скопирован из архива — этот шаг пропустить.

```bash
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan passport:install
```

---

## 11. Права на директории

```bash
chown -R www-data:www-data /var/www/mini-crm
find /var/www/mini-crm/storage /var/www/mini-crm/bootstrap/cache -type d -exec chmod 775 {} \;
find /var/www/mini-crm/storage /var/www/mini-crm/bootstrap/cache -type f -exec chmod 664 {} \;
chmod 660 /var/www/mini-crm/storage/oauth-*.key
```

---

## 12. Кеширование конфигурации

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 13. Настройка Nginx

```bash
nano /etc/nginx/sites-available/mini-crm
```

Содержимое (доступ по любому IP, без домена):
```nginx
server {
    listen 80;
    server_name _;
    root /var/www/mini-crm/public;

    index index.php;
    charset utf-8;

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

Включить конфиг:
```bash
ln -s /etc/nginx/sites-available/mini-crm /etc/nginx/sites-enabled/
rm /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx
```

---

## 14. Настройка воркера очереди (Supervisor)

```bash
apt install -y supervisor
nano /etc/supervisor/conf.d/laravel-queue.conf
```

Содержимое:
```ini
[program:laravel-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/mini-crm/artisan queue:listen --tries=1
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/mini-crm/storage/logs/queue.log
stopwaitsecs=3600
```

Применить:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start laravel-queue:*
```

---

## 15. Проверка

Открыть в браузере с любого компьютера в сети: `http://192.168.x.x`

Данные admin-пользователя задаются в `config/add_user.php` и применяются через `AddAdminUserSeeder`.

---

## Полезные команды

```bash
# Статус воркера очереди
supervisorctl status

# Перезапуск воркера (после обновления кода)
supervisorctl restart laravel-queue:*

# Логи приложения
tail -f /var/www/mini-crm/storage/logs/laravel.log

# Сбросить все кеши
php artisan optimize:clear
```

---

## Возможные проблемы

| Проблема | Решение |
|----------|---------|
| `openssl` не установлен | Встроен в `php8.2` — отдельный пакет не нужен. Проверить: `php -m \| grep openssl` |
| `composer install` падает с HTTP error | Сервер без интернета — использовать Вариант Б из шага 8 (архив с `vendor/`) |
| `ext-xml` / `ext-curl` missing | `apt install php8.2-xml php8.2-curl` |
| 500 Internal Server Error | Проверить `storage/logs/laravel.log`, права на `storage/` и `bootstrap/cache/` |
| `php-fpm` socket not found | `systemctl status php8.2-fpm` — убедиться что запущен |
| Миграции не применяются | Проверить данные БД в `.env`, доступность MySQL |
| Очередь не работает | `supervisorctl status` → перезапустить `laravel-queue` |
| OAuth ключи отсутствуют | `php artisan passport:install` + проверить права на `storage/oauth-*.key` |
