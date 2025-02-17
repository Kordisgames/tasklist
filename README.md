## Installation:
php artisan migrate

- Если не работает Sanctum:
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
или
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# API:
В заголовках прописать: Accept: application/json , чтобы при ошибках валидации корректно прилетала информация с ответом, а не переадресовывало на страницу входа

## Start server:
php artisan serve

## Настройки

### Примечания:
- С токеном авторизации не возился, поэто сделал его хардкодом, чтобы все заработало - необходимо получить токен через API во время авторизации (http://localhost/api/v1/auth/login) [login: login, password: password], затем вставить его на странице resources/views/tasks.blade.php в разделе скриптов - TOKEN (63 строка)


### Инструкция Google Sheets
- Создаем проект в Google Cloud

Заходим на Google Cloud Console.
Создаем новый проект (например, "TaskBoard").
Включаем Google Sheets API

- В меню "API и сервисы" -> "Библиотека".
Ищем Google Sheets API и включаем.
Создаем сервисный аккаунт, либо самоятотельно, либо сверху плашка, когда предложит

- В меню "API и сервисы" -> "Учетные данные".
Нажимаем "Создать учетные данные" -> "Сервисный аккаунт".
Даём имя (например, taskboard-bot) и создаем.

- В разделе "Учетные данные" -> "Сервисные аккаунты".
Открываем созданный аккаунт, вкладка "Ключи".
Нажимаем "Добавить ключ" -> "Создать новый ключ" (JSON).
Загружается файл taskboard-bot.json.
Добавляем сервисный аккаунт в Google Таблицу

Открываем Google Sheets (https://docs.google.com/spreadsheets/).
Создаем таблицу (например, "TaskBoard").
Нажимаем "Файл" -> "Настройки доступа".
Добавляем email сервисного аккаунта (из JSON-файла) с правами "Редактор".

Ссылка на текущую рабочую таблицу: https://docs.google.com/spreadsheets/d/1dGsEMmgVE0LD4-App_iILaRPdUb6K0cZTz7EDA8JmDw/edit?usp=sharing

Ключ скачиваем и помещаем в storage/app/google-sheets.json
