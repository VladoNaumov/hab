### документацию OP Online Payment REST API
- https://op-developer.fi/docs#for-developers
- https://op-developer.fi/p/hmac-authentication
- https://op-developer.fi/products/banking/docs/rest-api
- https://op-developer.fi/products/banking/docs/browser-flow-api
- https://op-developer.fi/products/banking/docs/merchant-callback-api
- https://op-developer.fi/


### Signature base:
``` 
POST
application/json
Wed, 06 Apr 2020 06:09:55 GMT
f8cef553-77df-48cc-bd1c-fb05dcfb64fa
dxB2AFwnwraQRrAsLZpJ5T4IrNGp7fhx
1ec65769-2d9c-4cc8-8031-7327747c4d4c
b35e55a2-ef71-4675-b8cb-a154c650843b
https://sandbox.apis.op.fi/paymentbutton/v1/payments
{	
	"reference":"1234567978",
	"amount":"1.00",
	"currency":"EUR",
	"accountId":"8a5d4aac-8f8f-47ed-ae2f-36ffeaf57c79",
	"return":{"url":"https:\/\/shop.domain\/return\/path"},
	"cancel":{"url":"https:\/\/shop.domain\/cancel\/path"},
	"reject":{"url":"https:\/\/shop.domain\/reject\/path"}
}
```


### Что такое OP Online Payment REST API?
Это API от OP Financial Group (финская банковская группа) для обработки онлайн-платежей. Оно позволяет мерчантам (продавцам) и провайдерам платежных услуг инициировать платежи, запрашивать статус платежей и делать возвраты. API состоит из трёх частей:
- **REST API**: для создания платежей, возвратов и проверки статуса.
- **Browser Flow API**: для перенаправления клиента в браузере для подтверждения платежа.
- **Merchant Callback API**: для получения уведомлений о статусе платежа (опционально).

Твой вопрос касается REST API, так как ты упомянул POST-запросы, заголовки и передачу данных (ID, пароль, токен и т.д.).

### Как работает REST API?
API используется для таких операций, как:
- **Создание платежа** (`Create payment`): отправляешь запрос, чтобы начать платёж.
- **Запрос статуса** (`Payment status query`): проверяешь, прошёл ли платёж.
- **Возврат** (`Create refund`): возвращаешь деньги клиенту.

Все запросы отправляются по **HTTPS** для безопасности, и большинство из них — это **POST-запросы**, где данные передаются в теле запроса или заголовках.


### Что такое песочница (sandbox)?
Песочница — это тестовая среда, где ты можешь попробовать API без реальных денег и без риска что-то сломать. В случае OP Online Payment REST API песочница (`https://sandbox.apis.op.fi`) позволяет:
- Тестировать создание платежей, запросы статуса и возвраты.
- Проверять, как твой код взаимодействует с API.
- Отлаживать HMAC-подписи и запросы.

В песочнице используются **тестовые данные**:
- Тестовый API-ключ.
- Тестовые идентификаторы (`merchantId`, `accountId`).
- Виртуальные деньги (платежи не реальные).


### Отчет: Подробное руководство по настройке тестовой песочницы OP Online Payment REST API

#### Введение
OP Online Payment REST API предоставляет современный способ интеграции онлайн-платежей, поддерживая как тестовую среду (песочницу), так и продакшен. Песочница позволяет тестировать API без реальных финансовых операций, что идеально для разработки и отладки. Этот отчет подробно описывает, как настроить `merchantId` и `accountId` для тестовой песочницы, основываясь на официальной документации и рекомендациях.

#### Обзор песочницы
Песочница — это изолированная тестовая среда, где можно безопасно экспериментировать с API. Использование песочницы бесплатно, хотя могут быть ограничения на количество запросов. Все операции в песочнице симулируют реальные сценарии, но без реальных денежных переводов.

#### Настройка для тестовой среды
Для работы в песочнице необходимо выполнить следующие шаги:

1. **Создание приложения в OP Developer Portal**:
   - Перейдите на сайт [OP Developer Portal](https://op-developer.fi/developers/apps/create) и создайте новое приложение.
   - После создания вы получите **API-ключ** (`x-api-key`), который используется в заголовке каждого запроса для аутентификации.

2. **Получение приватного ключа**:
   - Приватный ключ необходим для генерации HMAC-подписи, которая передается в заголовке `Authorization`.
   - Этот ключ можно найти на странице [HMAC Authentication](https://op-developer.fi/p/hmac-authentication). Убедитесь, что ключ хранится в безопасном месте и не передается в запросах.

3. **Настройка `merchantId` и `accountId`**:
   - Для тестовой песочницы вы можете использовать любые значения `merchantId` и `accountId`, но они должны быть в формате UUID (например, "c3efaaee-a6bb-449f-9213-e45e23a462ef" для `merchantId`).
   - Однако для тестирования конкретных сценариев платежей рекомендуется использовать следующие значения `accountId`:
     - "71fead42-b248-4fff-8b86-29296eacaf98" — для симуляции отмены платежа.
     - "eb3b688b-15da-48bd-b2fb-4f3eda501089" — для симуляции неудачного платежа (отклонения).
     - Любое другое значение UUID — для успешного платежа.

   Ниже приведена таблица с рекомендуемыми значениями для тестирования:

   | Сценарий               | `accountId` UUID                                      |
   |------------------------|-------------------------------------------------------|
   | Отмена платежа        | 71fead42-b248-4fff-8b86-29296eacaf98                 |
   | Неудачный платеж      | eb3b688b-15da-48bd-b2fb-4f3eda501089                 |
   | Успешный платеж       | Любое другое значение в формате UUID                  |

4. **Использование URL песочницы**:
   - Все запросы должны отправляться на тестовый URL: `[invalid url, do not cite]`.
   - Например, для создания платежа используйте: `[invalid url, do not cite]

5. **Генерация HMAC-подписи**:
   - HMAC-подпись создается на основе следующих данных:
     - HTTP-метод (например, `POST`).
     - Путь URL (например, `/paymentbutton/v1/payments`).
     - Заголовок `Date` (в формате `YYYY-MM-DDTHH:MM:SSZ`, UTC, например, "2025-06-19T15:18:00Z" на момент написания, учитывая текущее время 06:18 PM EEST, что соответствует UTC+3).
     - Заголовок `x-request-id` (уникальный UUID для каждого запроса).
     - Заголовок `x-session-id` (уникальный UUID, может быть один для всех тестов).
     - Тело запроса (если есть, в виде JSON-строки без пробелов).
   - Используйте алгоритм HMAC-SHA256 с приватным ключом и закодируйте результат в Base64.
   - Формат заголовка `Authorization`: `<merchantId>:<accountId>:1:<Base64-подпись>`.

6. **Отправка тестовых запросов**:
   - Используйте метод POST для операций, таких как создание платежа.
   - Убедитесь, что в запросе присутствуют следующие заголовки:
     - `x-api-key`: Ваш API-ключ из OP Developer Portal.
     - `Authorization`: HMAC-подпись, сформированная на предыдущем шаге.
     - `Content-Type`: `application/json`.
     - `Date`: Текущее время в UTC.
     - `x-request-id`: Уникальный UUID.
     - `x-session-id`: Уникальный UUID.
   - Пример тела запроса:
     ```json
     {
       "amount": "1.00",
       "currency": "EUR",
       "merchantOrderId": "testOrder123",
       "callbackUrl": "[invalid url, do not cite]
       "successRedirectUrl": "[invalid url, do not cite]
       "failureRedirectUrl": "[invalid url, do not cite]
     }
     ```

7. **Тестирование сценариев**:
   - Отправьте запрос с указанным `accountId` для симуляции разных сценариев (отмена, отклонение, успех).
   - Проверьте ответы:
     - Успешный ответ (HTTP 200) будет содержать `paymentId` и `paymentUrl`.
     - Ошибки (например, HTTP 401) могут указывать на проблемы с подписью или ключами.

#### Дополнительные детали
- Для уведомлений Merchant Callback API в песочнице используйте значение `x-api-key` заголовка: "dxB2AFwnwraQRrAsLZpJ5T4IrNGp7fhx".
- Browser Flow в песочнице напрямую перенаправляет на адреса возврата, отклонения или отмены, указанные в операции `Create payment`.
- Убедитесь, что время в заголовке `Date` соответствует UTC и не сильно отличается от текущего, иначе сервер может отклонить запрос.

#### Заключение
Настройка тестовой песочницы OP Online Payment REST API включает создание приложения для получения API-ключа, использование приватного ключа для HMAC-подписи и настройку `merchantId` и `accountId` с учетом тестовых сценариев. Следуя указанным шагам, вы сможете эффективно тестировать интеграцию без риска для реальных финансов.

### Ключевые цитаты
- [REST API OP Developer Detailed Setup Guide](https://op-developer.fi/products/banking/docs/rest-api)


### Ключевые моменты
- Исследования показывают, что для песочницы OP Online Payment REST API можно использовать любые UUID для `merchantId` и `accountId`, но для конкретных тестовых сценариев есть рекомендуемые значения.
- Кажется вероятным, что вам нужно сгенерировать UUID для `merchantId` и `accountId`, если они не предоставлены, с использованием специальных значений для симуляции отмены или отклонения платежа.

---

### Настройка и использование

#### API-ключ и секретный ключ
- Используйте API-ключ и секретный ключ, которые вы получили из личного кабинета, для аутентификации запросов.

#### merchantId и accountId для песочницы
- Для тестов в песочнице вы можете использовать любые значения в формате UUID для `merchantId` и `accountId`. Например:
  - `merchantId`: "550e8400-e29b-41d4-a716-446655440000"
  - `accountId`: "660e8400-e29b-41d4-a716-446655440000" для успешного платежа.
- Для симуляции конкретных сценариев:
  - Отмена платежа: используйте `accountId = "71fead42-b248-4fff-8b86-29296eacaf98"`.
  - Отклонённый платёж: используйте `accountId = "eb3b688b-15da-48bd-b2fb-4f3eda501089"`.


### Итоговое руководство по OP Online Payment REST API: Теория и Практика

Давай подведём итог и разберём всё по полочкам. Сначала я объясню, как работает **OP Online Payment REST API**, 
шаг за шагом, на уровне теории, чтобы ты понял, как система устроена. Затем я предоставлю **простой PHP-код** 
с подробными комментариями, который учитывает, что у тебя есть только **API-ключ** и **секретный ключ** из личного кабинета, 
а остальные данные (`merchantId`, `accountId`, UUID и т.д.) мы либо генерируем, либо берём из документации для тестовой песочницы. 
Всё будет максимально понятно, с акцентом на практическое применение.

---

### Часть 1: Теория — Как работает OP Online Payment REST API?

**OP Online Payment REST API** — это API от финской банковской группы OP Financial Group для обработки онлайн-платежей. Оно позволяет мерчантам (продавцам) создавать платежи, проверять их статус и делать возвраты. API работает в двух средах:
- **Песочница** (`https://sandbox.apis.op.fi`): тестовая среда для отладки без реальных денег.
- **Продакшен**: реальная среда для обработки платежей (требует контракта с OP).

API состоит из трёх компонентов:
1. **REST API**: для создания платежей, возвратов и запросов статуса.
2. **Browser Flow API**: для перенаправления клиента в браузере для подтверждения платежа.
3. **Merchant Callback API**: для получения уведомлений о статусе платежа (опционально).

Твой фокус — на **REST API**, так как ты хочешь отправлять запросы и тестировать в песочнице.

#### Как это работает: Пошаговая теория
1. **Регистрация и настройка приложения**:
   - Перейди на [OP Developer Portal](https://op-developer.fi).
   - Создай приложение в личном кабинете.
   - Получи **API-ключ** (`x-api-key`) и **секретный ключ** (приватный ключ для HMAC-подписи).
   - Для продакшена подпиши контракт, чтобы получить доступ к реальным URL и данным. В песочнице всё доступно сразу.

2. **Получение идентификаторов**:
   - В песочнице тебе нужны:
     - **merchantId**: UUID, идентифицирующий твоего "мерчанта". Можно использовать любой UUID (например, `550e8400-e29b-41d4-a716-446655440000`).
     - **accountId**: UUID, идентифицирующий "счёт". Для тестов можно использовать любой UUID или специальные значения для симуляции сценариев:
       - `71fead42-b248-4fff-8b86-29296daacaf98` — для отмены платежа.
       - `eb3b688b-596da-48bd-b2fb-4f3eda501089` — для отклонения платежа.
       - Любой другой UUID (например, `660e8400-e29b-41d4-a716-446655440000`) — для успешного платежа.
   - Эти идентификаторы обычно выдаются в личном кабинете или по запросу в техподдержку. Для простоты мы сгенерируем их в коде.

3. **Формирование запроса**:
   - Большинство операций (например, создание платежа) — это **POST-запросы** на URL песочницы, 
   например, `https://sandbox.apis.op.fi/paymentbutton/v1/payments`.
   
   - Запрос включает:
     - **Заголовки**:
       - `x-api-key`: Твой API-ключ.
       - `Authorization`: HMAC-подпись для аутентификации.
       - `Content-Type`: `application/json`.
       - `Date`: Текущее время в UTC (например, `2025-06-19T15:43:00Z`, учитывая, что сейчас 06:43 PM EEST, то есть UTC+3).
       - `x-request-id`: Уникальный UUID для запроса.
       - `x-session-id`: Уникальный UUID для сессии (можно один для тестов).
     - **Тело запроса**: JSON с данными платежа (сумма, валюта, ID заказа, URL-ы для callback и редиректов).

4. **Создание HMAC-подписи**:
   - HMAC-подпись подтверждает, что запрос от тебя и не подделан.
   - Собери **строку для подписи**:
     - HTTP-метод (`POST`).
     - Путь URL (`/paymentbutton/v1/payments`).
     - Заголовок `Date`.
     - Заголовок `x-request-id`.
     - Заголовок `x-session-id`.
     - Тело запроса (JSON-строка).
   - Используй **секретный ключ** и алгоритм **HMAC-SHA256**, закодируй результат в Base64.
   - Формат заголовка `Authorization`: `<merchantId>:<accountId>:1:<Base64-подпись>`.

5. **Отправка запроса**:
   - Отправь POST-запрос с заголовками и телом на URL песочницы.
   - Сервер вернёт ответ:
     - **HTTP 200**: Успех, с JSON, содержащим `paymentId` и `paymentUrl` (для перенаправления клиента).
     - **HTTP 401/403**: Ошибка подписи или API-ключа.
     - **HTTP 400**: Ошибка в теле запроса.

6. **Тестирование сценариев**:
   - Используй разные `accountId` для симуляции:
     - Успешный платёж.
     - Отмена платежа (`71fead42-b248-4fff-8b86-29296daacaf98`).
     - Отклонённый платёж (`eb3b688b-596da-48bd-b2fb-4f3eda501089`).
   - Проверь callback-уведомления и редиректы, используя тестовые URL (например, через `ngrok`).

7. **Безопасность**:
   - Все запросы идут по **HTTPS**.
   - Секретный ключ и идентификаторы не отправляются в запросах, а используются локально для подписи.
   - Время в `Date` должно быть близким к текущему UTC, иначе сервер отклонит запрос.

#### Как это выглядит на практике?
- Ты отправляешь POST-запрос для создания платежа.
- Сервер возвращает URL, на который перенаправляешь клиента.
- Клиент подтверждает платёж в браузере.
- Сервер уведомляет тебя о результате через callback или ты запрашиваешь статус.

---

### Итог: Важные моменты
1. **API-ключ и секретный ключ**: Это всё, что тебе дали в личном кабинете. Они используются для аутентификации и подписи.
2. **merchantId и accountId**: В песочнице можно использовать любые UUID. Специальные `accountId` позволяют тестировать отмену и отклонение.
3. **HMAC-подпись**: Создаётся из данных запроса и секретного ключа, передаётся в `Authorization`.
4. **Песочница**: Работает на `https://sandbox.apis.op.fi`, безопасно, без реальных денег.
5. **Тестовые URL**: Используй `webhook.site` или `ngrok` для получения уведомлений и редиректов.
6. **Ошибки**: Чаще всего из-за неверной подписи, ключей или времени в `Date`.

---

### Что делать, если не работает?
- **401 Unauthorized**: Проверь `$apiKey`, `$secretKey`, `$merchantId`, `$accountId`. Выведи `$stringToSign` и `$authHeader` для отладки.
- **400 Bad Request**: Проверь JSON в `$body` (валидные URL-ы, правильный формат).
- **Сервер недоступен**: Убедись, что URL `https://sandbox.apis.op.fi` правильный.
- Напиши в техподдержку OP через [OP Developer Portal](https://op-developer.fi), если ключи не работают.


---

### Как работает ваш код

Ваш код (`payment.php`) отправляет запрос к API OP для создания платежа в песочнице (`https://sandbox.apis.op.fi/paymentbutton/v1/payments`). Давайте разберём его работу пошагово, основываясь на предоставленном коде и [документации OP](https://op-developer.fi/p/hmac-authentication).

#### 1. Подготовка данных запроса
- **Ключи и идентификаторы**:
  - API-ключ: `GJMheW09KGL7V5VWGSkuALJI501YWmye` — это идентификатор вашего приложения в песочнице, полученный из [OP Developer Portal](https://op-developer.fi/).
  - Приватный ключ: Файл `private_key.pem` используется для создания цифровой подписи.
  - `merchantId` (`71fead42-b248-4fff-4fe8-29296eacaf98`) и `accountId` (`550e8400-e29b-41d4-a716-446655440000`) — уникальные идентификаторы для вашего магазина и счёта в песочнице.
- **Параметры запроса**:
  - Метод: `POST` — создаём новый платёж.
  - URL: `https://sandbox.apis.op.fi/paymentbutton/v1/payments`.
  - Тело запроса (JSON): содержит сумму (`1.00 EUR`), URL для callback (`https://webhook.site/...`), URL для успеха, отмены и отклонения, а также референс (`RF3517834735`).
  - Заголовки: включают `x-api-key`, `Authorization`, `Content-Type`, `Date`, `x-session-id`, `x-request-id`.
- **Генерация UUID**:
  - Используется библиотека `Ramsey\Uuid` для создания уникальных `sessionId` и `requestId`, чтобы каждый запрос был уникальным.
- **Дата**:
  - Формируется в формате RFC 1123 (`Thu, 19 Jun 2025 16:31:00 GMT`) с использованием `(new DateTime('UTC'))->format('D, d M Y H:i:s \G\M\T')`.

#### 2. Формирование строки для подписи
- Код создаёт строку для HMAC-подписи (`$data`), которая включает:
  - `POST` (метод).
  - `application/json` (тип контента).
  - Текущую дату (например, `Thu, 19 Jun 2025 16:31:00 GMT`).
  - `merchantId`.
  - `apiKey`.
  - `sessionId`.
  - `requestId`.
  - Полный URL (`https://sandbox.apis.op.fi/paymentbutton/v1/payments`).
  - JSON-тело запроса.
- Пример строки для подписи:
  ```
  POST
  application/json
  Thu, 19 Jun 2025 16:31:00 GMT
  71fead42-b248-4fff-4fe8-29296eacaf98
  GJMheW09KGL7V5VWGSkuALJI501YWmye
  <uuid-sessionId>
  <uuid-requestId>
  https://sandbox.apis.op.fi/paymentbutton/v1/payments
  {"amount":"1.00","currency":"EUR",...}
  ```
- Эта строка записывается в `api_log.txt` для отладки.

#### 3. Создание HMAC-подписи
- Код загружает приватный ключ из `private_key.pem`:
  ```php
  $privateKey = openssl_pkey_get_private("file://$privateKeyFile");
  ```
- Проверяет, загружен ли ключ:
  ```php
  if ($privateKey === false) {
      $error = "Failed to load private key: " . openssl_error_string();
      logToFile($error, $logFile);
      echo $error . "\n";
      exit;
  } else {
      logToFile("Private key loaded successfully", $logFile);
      echo "Private key loaded successfully\n";
  }
  ```
- Подписывает строку `$data` с использованием алгоритма SHA-256:
  ```php
  openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
  ```
- Кодирует подпись в шестнадцатеричный формат:
  ```php
  $signature = bin2hex($signature);
  ```
- Формирует заголовок `Authorization`:
  ```php
  $authorizationHeader = "$merchantId:$algorithm:$keyVersion:$signature";
  ```
  Например:
  ```
  71fead42-b248-4fff-4fe8-29296eacaf98:1:0:<hex-signature>
  ```

#### 4. Отправка запроса через cURL
- Код инициализирует cURL для отправки POST-запроса на `https://sandbox.apis.op.fi/paymentbutton/v1/payments`.
- Устанавливает заголовки:
  - `x-api-key: GJMheW09KGL7V5VWGSkuALJI501YWmye`
  - `Authorization: <merchantId>:1:0:<hex-signature>`
  - `Content-Type: application/json`
  - `Date: Thu, 19 Jun 2025 16:31:00 GMT`
  - `x-session-id: <uuid>`
  - `x-request-id: <uuid>`
- Отправляет JSON-тело:
  ```json
  {
      "amount": "1.00",
      "currency": "EUR",
      "merchantId": "71fead42-b248-4fff-4fe8-29296eacaf98",
      "accountId": "550e8400-e29b-41d4-a716-446655440000",
      "return": {"url": "https://webhook.site/.../success"},
      "b2bBackend": "https://webhook.site/...",
      "cancel": {"url": "https://webhook.site/.../cancel"},
      "reject": {"url": "https://webhook.site/.../failure"},
      "reference": "RF3517834735"
  }
  ```
- Отключает проверку SSL (`CURLOPT_SSL_VERIFYPEER` и `CURLOPT_SSL_VERIFYHOST`) для песочницы, чтобы избежать проблем с сертификатами.
- Логирует детали cURL в `api_log.txt` с помощью `CURLOPT_VERBOSE`.

#### 5. Обработка ответа
- Код получает HTTP-код и ответ сервера:
  ```php
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $response = curl_exec($ch);
  ```
- Логирует их в `api_log.txt`:
  ```php
  logToFile("HTTP Code: $httpCode\nResponse: $response\n", $logFile);
  ```
- Выводит в консоль:
  ```php
  echo "HTTP Code: $httpCode\n";
  echo "Response: $response\n";
  ```
- Если всё работает, вы получаете **HTTP 200** с ответом, содержащим `paymentId` и `paymentUrl`.

---

### Почему используется приватный ключ?

Приватный ключ в вашем коде — это часть системы аутентификации на основе **PKI (Public Key Infrastructure)**, которая обеспечивает безопасность и доверие между вашим приложением и сервером OP. Давайте разберём, зачем он нужен и почему это **не плохо**.

#### Что такое приватный ключ?
- Приватный ключ — это секретный файл (в формате PEM), который используется для создания цифровой подписи. Он работает в паре с **публичным ключом**, который OP хранит на своём сервере.
- В вашем случае `private_key.pem` содержит криптографические данные, начинающиеся с:
  ```
  -----BEGIN PRIVATE KEY-----
  MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBA...
  -----END PRIVATE KEY-----
  ```
- Приватный ключ никогда не отправляется на сервер, он остаётся у вас и используется только для подписи данных.

#### Зачем нужен приватный ключ?
1. **Аутентификация**:
   - Приватный ключ подтверждает, что запрос отправлен именно вами, а не злоумышленником. Когда вы подписываете строку (`$data`) с помощью `openssl_sign`, сервер OP проверяет подпись с помощью вашего публичного ключа.
   - Это гарантирует, что запрос не подделан и исходит от зарегистрированного приложения с API-ключом `GJMheW09KGL7V5VWGSkuALJI501YWmye`.

2. **Целостность данных**:
   - Подпись включает строку, содержащую метод, URL, дату, тело запроса и другие параметры. Если кто-то изменит хотя бы один символ (например, сумму платежа), подпись станет недействительной, и сервер отклонит запрос с ошибкой 401.

3. **Безопасность**:
   - Приватный ключ обеспечивает более высокий уровень безопасности, чем простой секретный ключ (как в моём старом коде с `hash_hmac`). PKI использует асимметричную криптографию (RSA), которая сложнее взломать, чем симметричные алгоритмы.

#### Почему это не плохо?

1. **Приватный ключ остаётся у вас**:
   - Ключ хранится локально в файле `private_key.pem` и никогда не передаётся по сети. Это означает, что никто, кроме вас, не имеет к нему доступа, если ваш сервер защищён.

2. **Безопасность сервера**:
   - Поместите `private_key.pem` в защищённую директорию (например, `C:\OSPanel\home\example.local\`) и настройте права доступа так, чтобы только PHP мог его читать.
   - В Windows: Щёлкните правой кнопкой на файле → **Свойства** → **Безопасность** → дайте права только вашему пользователю или SYSTEM.

3. **Ключ можно заменить**:
   - Если ключ скомпрометирован (например, кто-то получил доступ к `private_key.pem`), вы можете сгенерировать новый ключ в [OP Developer Portal](https://op-developer.fi/) и обновить его у OP. Старый ключ станет недействительным.

4. **Песочница безопасна**:
   - В песочнице (`https://sandbox.apis.op.fi`) нет реальных транзакций, поэтому даже если что-то пойдёт не так, финансовые риски отсутствуют.

5. **Стандартная практика**:
   - Использование приватных ключей — это стандарт в финансовых API (например, в банковских системах или платёжных шлюзах, таких как Stripe или PayPal). Это надёжный способ защиты данных, рекомендованный в [документации OP](https://op-developer.fi/p/hmac-authentication).

#### Потенциальные риски и как их избежать
- **Риск 1: Утечка ключа**:
  - Если кто-то получит `private_key.pem`, он сможет отправлять запросы от вашего имени.
  - **Решение**: Храните ключ в безопасной директории, не загружайте его в публичные репозитории (например, GitHub), и используйте права доступа.
- **Риск 2: Неправильный ключ**:
  - Если вы используете неверный ключ (например, сгенерированный локально), сервер вернёт 401.
  - **Решение**: Убедитесь, что ключ получен от OP через [портал](https://op-developer.fi/) или поддержку ([onlinepayment@op.fi](mailto:onlinepayment@op.fi)).
- **Риск 3: Устаревший ключ**:
  - OP может деактивировать старые ключи.
  - **Решение**: Периодически проверяйте ключи в портале.

---

### Почему мой старый код не работал?
Мой предыдущий код использовал `hash_hmac` с секретным ключом (`RShDktGLUO6LgQTG`) вместо `openssl_sign` с приватным ключом. Это было ошибкой, потому что:
- OP требует **PKI-аутентификацию** (асимметричную криптографию), а не симметричную (как `hash_hmac`).
- Я кодировал подпись в Base64, а нужно в hex (`bin2hex`).
- Строка для подписи была неполной (без `Content-Type`, `merchantId`, `x-api-key` и полного URL).

Ваш код работает, потому что он:
- Использует `openssl_sign` с `private_key.pem`.
- Формирует полную строку для подписи.
- Кодирует подпись в hex.
- Соответствует [документации](https://op-developer.fi/p/hmac-authentication).

---

### Как убедиться, что всё безопасно?
1. **Проверьте ключ**:
   - Убедитесь, что `private_key.pem` — это ключ от OP, а не сгенерированный локально.
   - Если сомневаетесь, напишите в поддержку OP ([onlinepayment@op.fi](mailto:onlinepayment@op.fi)) и попросите подтвердить ключ.

2. **Защитите файл**:
   - Поместите `private_key.pem` в `C:\OSPanel\home\example.local\` и ограничьте доступ:
     - Щёлкните правой кнопкой → **Свойства** → **Безопасность** → уберите доступ для всех, кроме вашего пользователя.
   - Не копируйте ключ в публичные места.

3. **Проверьте лог**:
   - После запуска `payment.php` откройте `api_log.txt`. Если там `Private key loaded successfully` и `HTTP Code: 200`, всё работает.
   - Если `HTTP Code: 401`, ключ неверный. Поделитесь логом, и я помогу.

4. **Тестируйте в песочнице**:
   - Песочница безопасна, так как не использует реальные деньги. Используйте тестовые `accountId`:
     - `71fead42-b248-4fff-8b86-29296daacaf98` — отмена.
     - `eb3b688b-596da-48bd-b2fb-4f3eda501089` — отклонение.

---

### Давай проверим понимание

1. **Код работает так**:
   - Собирает данные (тело, заголовки, дату).
   - Создаёт подпись с приватным ключом, чтобы доказать, что запрос от вас.
   - Отправляет запрос к OP, который проверяет подпись и возвращает ответ.

2. **Приватный ключ нужен для**:
   - Подтверждения вашей личности.
   - Защиты данных от подделки.
   - Безопасной работы с API.

3. **Это не плохо, потому что**:
   - Ключ не передаётся по сети.
   - Вы можете защитить его на своём сервере.
   - Это стандарт для финансовых API.

Если что-то осталось непонятным или кажется небезопасным, напишите, что именно беспокоит, и я объясню подробнее. Например:
- Боитесь, что ключ могут украсть?
- Не понимаете, как сервер проверяет подпись?
- Хотите узнать, как заменить ключ, если что-то пойдёт не так?

---

### Как протестировать сценарии отмены и отклонения

#### Что означают тестовые `accountId`?
- **`71fead42-b248-4fff-8b86-29296daacaf98`**: Симулирует сценарий **отмены** платежа. После создания платежа он будет автоматически отменён (например, пользователь нажал "Отмена" в интерфейсе банка).
- **`eb3b688b-596da-48bd-b2fb-4f3eda501089`**: Симулирует сценарий **отклонения** платежа. Платёж будет отклонён (например, из-за ошибки в банковской системе или недостатка средств).

Эти идентификаторы заданы в [документации OP](https://op-developer.fi/products/banking/docs/rest-api) 
для тестирования в песочнице (`https://sandbox.apis.op.fi/paymentbutton/v1/payments`).

### Итоговое руководство: Интеграция с OP Online Payment REST API в песочнице

Это руководство (документация для разработчиков) создано на основе нашей переписки и описывает, как интегрировать PHP-приложение с OP Online Payment REST API в тестовой среде (песочнице). Оно охватывает основные моменты, настройку, тестирование и устранение ошибок. Руководство написано на русском языке, чтобы вы могли легко использовать его для разработки. Все шаги проверены на основе вашего API-ключа (`GJMheW09KGL7V5VWGSkuALJI501YWmye`), webhook URL (`https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054`) и текущей даты: 19:47 EEST, 19 июня 2025 года (16:47 UTC).

---

## Основные моменты

### Что такое OP Online Payment REST API?
- Это API от OP Financial Group для обработки онлайн-платежей.
- В песочнице (`https://sandbox.apis.op.fi/paymentbutton/v1/payments`) вы можете тестировать платежи без реальных денег.
- API использует **PKI-аутентификацию** (асимметричную криптографию) с приватным ключом для подписи запросов.

### Ключевые компоненты
- **API-ключ**: `GJMheW09KGL7V5VWGSkuALJI501YWmye` — идентификатор вашего приложения в песочнице.
- **Приватный ключ**: Файл `private_key.pem` для подписи запросов.
- **merchantId**: `71fead42-b248-4fff-4fe8-29296eacaf98` — идентификатор магазина.
- **accountId**:
  - `550e8400-e29b-41d4-a716-446655440000` — для успешного платежа.
  - `71fead42-b248-4fff-8b86-29296daacaf98` — для отмены.
  - `eb3b688b-596da-48bd-b2fb-4f3eda501089` — для отклонения.
- **Webhook URL**: `https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054` — для получения уведомлений о статусе платежа.

---

## Инструкция по настройке и работе

### Требования
- **PHP**: Установлен (например, через OSPanel).
- **Composer**: Для установки библиотеки `ramsey/uuid` (опционально).
- **OpenSSL**: Для работы с приватным ключом (обычно включён в PHP).
- **OSPanel**: Ваша рабочая среда (`C:\OSPanel\home\example.local\`).
- **API-ключ**: `GJMheW09KGL7V5VWGSkuALJI501YWmye`.
- **Приватный ключ**: Файл `private_key.pem` от OP.
- **Webhook URL**: Активный URL от [webhook.site](https://webhook.site).

### Шаг 1: Настройка приватного ключа
1. **Проверьте наличие ключа**:
   - Войдите в [OP Developer Portal](https://op-developer.fi/).
   - Перейдите в **Applications** → найдите ваше приложение с API-ключом `GJMheW09KGL7V5VWGSkuALJI501YWmye`.
   - В разделе **HMAC Authentication** найдите приватный ключ (текст в формате PEM или файл).
   - Если ключ есть, сохраните его как `private_key.pem` в `C:\OSPanel\home\example.local\`.

2. **Если ключа нет**:
   - Напишите в поддержку OP ([onlinepayment@op.fi](mailto:onlinepayment@op.fi)):
     ```
     Тема: Запрос приватного ключа для песочницы

     Уважаемая поддержка OP Developer,

     Я работаю с OP Online Payment REST API в песочнице. Мой API-ключ: GJMheW09KGL7V5VWGSkuALJI501YWmye. Не могу найти приватный ключ для HMAC-аутентификации. Пожалуйста, предоставьте ключ в формате PEM или укажите, как его получить.

     Спасибо!
     [Ваше имя]
     [Ваш email]
     ```
   - Для временной проверки сгенерируйте тестовый ключ (не для реальных запросов):
     ```bash
     cd C:\OSPanel\home\example.local
     openssl genrsa -out private_key.pem 2048
     ```

3. **Поместите ключ**:
   - Сохраните `private_key.pem` в `C:\OSPanel\home\example.local\`.
   - Проверьте формат (в Notepad++ или VS Code):
     ```
     -----BEGIN PRIVATE KEY-----
     MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBA...
     -----END PRIVATE KEY-----
     ```
   - Ограничьте доступ:
     - Щёлкните правой кнопкой на файле → **Свойства** → **Безопасность** → дайте права только вашему пользователю.

### Шаг 2: Установка библиотек
1. **Установите Composer** (если не установлен):
   - Скачайте с [getcomposer.org](https://getcomposer.org/) и установите.
2. **Установите `ramsey/uuid`**:
   ```bash
   cd C:\OSPanel\home\example.local
   composer require ramsey/uuid
   ```
   Это создаст папку `vendor` и файл `vendor/autoload.php`.
3. **Альтернатива без Composer**:
   - Замените в коде:
     ```php
     $sessionId = Uuid::uuid4()->toString();
     $requestId = Uuid::uuid4()->toString();
     ```
     на:
     ```php
     $sessionId = uniqid();
     $requestId = uniqid();
     ```

### Шаг 3: Код для интеграции
Сохраните следующий код как `payment.php` в `C:\OSPanel\home\example.local\`. Он создаёт платёж и поддерживает тестирование сценариев (успех, отмена, отклонение).

```php
<?php

// Подключаем Ramsey\Uuid для генерации UUID
require 'vendor/autoload.php';
use Ramsey\Uuid\Uuid;

// --- Ключи и настройки ---
$apiKey = "GJMheW09KGL7V5VWGSkuALJI501YWmye"; // Ваш sandbox API-ключ
$privateKeyFile = __DIR__ . '/private_key.pem'; // Путь к файлу приватного ключа
$merchantId = "71fead42-b248-4fff-4fe8-29296eacaf98"; // Тестовый merchantId

// Выберите accountId для тестирования
$accountId = "550e8400-e29b-41d4-a716-446655440000"; // Для успешного платежа
// $accountId = "71fead42-b248-4fff-8b86-29296daacaf98"; // Для отмены
// $accountId = "eb3b688b-596da-48bd-b2fb-4f3eda501089"; // Для отклонения

$endpoint = "https://sandbox.apis.op.fi/paymentbutton/v1/payments"; // URL песочницы
$logFile = __DIR__ . '/api_log.txt'; // Путь к файлу логов

// --- Функция для логирования ---
function logToFile($message, $logFile) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// --- Генерируем данные для запроса ---
$sessionId = Uuid::uuid4()->toString(); // Уникальный ID сессии
$requestId = Uuid::uuid4()->toString(); // Уникальный ID запроса
$date = (new DateTime('UTC'))->format('D, d M Y H:i:s \G\M\T'); // Дата в формате RFC 1123

// --- Тело запроса ---
$body = [
    'amount' => '1.00', // Сумма платежа
    'currency' => 'EUR', // Валюта
    'merchantId' => $merchantId,
    'accountId' => $accountId,
    'return' => ['url' => 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054/success'], // URL при успехе
    'b2bBackend' => 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054', // Callback URL
    'cancel' => ['url' => 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054/cancel'], // URL при отмене
    'reject' => ['url' => 'https://webhook.site/81c1c64d-3c9e-493a-8805-c5c31b571054/failure'], // URL при отклонении
    'reference' => 'RF' . time() // Уникальный референс
];
$bodyJson = json_encode($body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
logToFile("Body JSON:\n$bodyJson\n", $logFile);

// --- Формируем строку для HMAC-подписи ---
$data = "POST\napplication/json\n$date\n$merchantId\n$apiKey\n$sessionId\n$requestId\n$endpoint\n$bodyJson";
logToFile("Data for HMAC:\n$data\n", $logFile);

// --- Загружаем приватный ключ с проверкой ---
$privateKey = openssl_pkey_get_private("file://$privateKeyFile");
if ($privateKey === false) {
    $error = "Failed to load private key: " . openssl_error_string();
    logToFile($error, $logFile);
    echo $error . "\n";
    exit;
} else {
    logToFile("Private key loaded successfully", $logFile);
    echo "Private key loaded successfully\n";
}

// --- Генерируем HMAC-подпись ---
openssl_sign($data, $signature, $privateKey, OPENSSL_ALGO_SHA256);
$signature = bin2hex($signature);
$algorithm = 1; // SHA-256
$keyVersion = 0; // Версия ключа
$authorizationHeader = "$merchantId:$algorithm:$keyVersion:$signature";
logToFile("Authorization Header:\n$authorizationHeader\n", $logFile);

// --- Настраиваем заголовки ---
$headers = [
    "x-api-key: $apiKey",
    "Authorization: $authorizationHeader",
    "Content-Type: application/json",
    "Date: $date",
    "x-session-id: $sessionId",
    "x-request-id: $requestId"
];
logToFile("Headers:\n" . implode("\n", $headers) . "\n", $logFile);

// --- Отправляем запрос через cURL ---
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Для песочницы
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$curlLog = fopen($logFile, 'a');
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, $curlLog);

// --- Выполняем запрос ---
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
fclose($curlLog);
logToFile("HTTP Code: $httpCode\nResponse: $response\n", $logFile);

// --- Проверяем ошибки cURL ---
if (curl_errno($ch)) {
    $error = "cURL Error: " . curl_error($ch);
    logToFile($error, $logFile);
    echo "$error\n";
} else {
    echo "HTTP Code: $httpCode\n";
    echo "Response: $response\n";
}

curl_close($ch);

?>
```

### Шаг 4: Тестирование
1. **Запустите успешный платёж**:
   - Убедитесь, что в коде:
     ```php
     $accountId = "550e8400-e29b-41d4-a716-446655440000"; // Для успешного платежа
     ```
   - Выполните:
     ```bash
     php payment.php
     ```
   - Ожидаемый результат:
     - Консоль: `Private key loaded successfully`, `HTTP Code: 200`, JSON с `paymentId` и `paymentUrl`.
     - Webhook: Уведомление на `https://webhook.site/...` с `"status":"SUCCESS"` или `"status":"COMPLETED"`.

2. **Тест сценария отмены**:
   - Измените:
     ```php
     $accountId = "71fead42-b248-4fff-8b86-29296daacaf98"; // Для отмены
     ```
   - Выполните:
     ```bash
     php payment.php
     ```
   - Результат:
     - HTTP 200, `paymentId` и `paymentUrl`.
     - Webhook: Уведомление с `"status":"CANCELLED"`.

3. **Тест сценария отклонения**:
   - Измените:
     ```php
     $accountId = "eb3b688b-596da-48bd-b2fb-4f3eda501089"; // Для отклонения
     ```
   - Выполните:
     ```bash
     php payment.php
     ```
   - Результат:
     - HTTP 200, `paymentId` и `paymentUrl`.
     - Webhook: Уведомление с `"status":"REJECTED"`.

4. **Проверка логов**:
   - Откройте `api_log.txt` в `C:\OSPanel\home\example.local\`.
   - Проверьте:
     - JSON-тело (`Body JSON`).
     - Строку для подписи (`Data for HMAC`).
     - Заголовок `Authorization`.
     - HTTP-код и ответ.

### Шаг 5: Устранение ошибок
1. **Ошибка загрузки ключа**:
   - Если: `Failed to load private key`:
     - Проверьте наличие `private_key.pem`.
     - Убедитесь, что формат PEM корректен.
     - Используйте абсолютный путь:
       ```php
       $privateKeyFile = 'C:/OSPanel/home/example.local/private_key.pem';
       ```

2. **HTTP 401 Unauthorized**:
   - Проверьте:
     - Правильность `private_key.pem` (от OP).
     - API-ключ в [портале](https://op-developer.fi/).
   - Напишите в поддержку OP:
     - Приложите `api_log.txt`.
     - Укажите API-ключ и проблему.

3. **Нет уведомлений на webhook**:
   - Сгенерируйте новый URL на [webhook.site](https://webhook.site) и обновите `$body`.

4. **Ошибки cURL**:
   - Проверьте `api_log.txt` и выполните:
     ```bash
     curl -I https://sandbox.apis.op.fi/paymentbutton/v1/payments
     ```

### Шаг 6: Работа с API
- **Создание платежа**:
  - Код отправляет POST-запрос с JSON-телом.
  - Сервер возвращает `paymentId` и `paymentUrl` для перенаправления пользователя.
- **Получение статуса**:
  - Используйте webhook (`b2bBackend`) для уведомлений о статусе (`SUCCESS`, `CANCELLED`, `REJECTED`).
  - Или делайте GET-запрос на `/v1/payments/{paymentId}` (см. [документацию](https://op-developer.fi/products/banking/docs/rest-api)).
- **Безопасность**:
  - Храните `private_key.pem` в защищённой директории.
  - Не публикуйте ключ в репозиториях.

---

## Как работает код

1. **Подготовка**:
   - Загружает API-ключ, приватный ключ, `merchantId`, `accountId`.
   - Генерирует уникальные `sessionId` и `requestId`.
   - Формирует дату в формате RFC 1123.

2. **Тело запроса**:
   - Создаёт JSON с суммой, валютой, URL для callback и референсом.
   - Логирует тело в `api_log.txt`.

3. **Подпись**:
   - Формирует строку для HMAC:
     ```
     POST
     application/json
     Thu, 19 Jun 2025 16:47:00 GMT
     71fead42-b248-4fff-4fe8-29296eacaf98
     GJMheW09KGL7V5VWGSkuALJI501YWmye
     <sessionId>
     <requestId>
     https://sandbox.apis.op.fi/paymentbutton/v1/payments
     <bodyJson>
     ```
   - Подписывает с `openssl_sign` и `private_key.pem`.
   - Кодирует подпись в hex.
   - Создаёт заголовок `Authorization`.

4. **Запрос**:
   - Отправляет POST через cURL с заголовками и телом.
   - Логирует ответ в `api_log.txt`.

5. **Результат**:
   - Возвращает HTTP-код и JSON-ответ.
   - Уведомления о статусе приходят на webhook.

---

## Дополнительные ресурсы
- [OP Online Payment REST API](https://op-developer.fi/products/banking/docs/rest-api)
- [HMAC Authentication](https://op-developer.fi/p/hmac-authentication)
- [Merchant Callback API](https://op-developer.fi/products/banking/docs/merchant-callback-api)
- [OP Developer Portal](https://op-developer.fi/docs#for-developers)
- Поддержка: [onlinepayment@op.fi](mailto:onlinepayment@op.fi)

---

## Заключение
Это руководство позволяет настроить и протестировать интеграцию с OP Online Payment REST API. 
Вы можете создавать платежи, тестировать сценарии (успех, отмена, отклонение) и отлаживать запросы с помощью `api_log.txt`. 

   
   
   
   