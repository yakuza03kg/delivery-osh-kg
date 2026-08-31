# Публикация в Railway

Проект разворачивается из корневого `Dockerfile`. Railway сам распознаёт этот файл; локальный `docker-compose.yml` в Railway не используется, потому что MySQL создаётся как отдельный сервис Railway.

## 1. Загрузите код в GitHub

Создайте пустой приватный репозиторий, например `courier-kg`, затем выполните в папке проекта:

```powershell
git remote add origin https://github.com/ВАШ-ЛОГИН/courier-kg.git
git branch -M main
git push -u origin main
```

Файл `.env` уже исключён из Git, поэтому API-ключи и пароли в репозиторий не попадут.

## 2. Создайте сервисы Railway

1. В Railway создайте проект.
2. Нажмите **New → Database → MySQL**.
3. Нажмите **New → GitHub Repo** и выберите репозиторий приложения.
4. Для сервиса приложения в **Settings** создайте публичный домен и укажите **Healthcheck Path**: `/health`.

## 3. Переменные приложения

В сервисе приложения, во вкладке **Variables**, добавьте:

```dotenv
APP_NAME=Курьер KG
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:СГЕНЕРИРУЙТЕ_ОТДЕЛЬНЫЙ_КЛЮЧ
APP_URL=https://ВАШ-ДОМЕН.railway.app

LOG_CHANNEL=stderr
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

ROUTE_PROVIDER=2gis
MAPS_API_KEY=ВАШ_КЛЮЧ_2GIS
DELIVERY_CURRENCY=KGS
ROUTE_HTTP_TIMEOUT=8
TWO_GIS_LOCALE=ru_KG
TWO_GIS_ROUTING_LOCALE=en
TWO_GIS_SEARCH_RADIUS=50000
NOMINATIM_USER_AGENT=Courier KG delivery calculator/1.0
```

Ссылки `${{MySQL.…}}` — это reference variables Railway. Если сервис базы называется не `MySQL`, замените namespace на его точное имя.

Для нового ключа приложения выполните локально и вставьте результат только в переменные Railway:

```powershell
php artisan key:generate --show
```

После создания домена обновите `APP_URL` на фактический HTTPS-адрес Railway и выполните redeploy. При старте контейнер сам запускает миграции и добавляет стартовые учётные записи.

## 4. Проверка

Откройте `https://ВАШ-ДОМЕН.railway.app/health`. Ответ должен быть:

```json
{"status":"ok"}
```

Затем зайдите через `/login`, смените тестовые пароли и создайте реальные учётные записи сотрудников.
