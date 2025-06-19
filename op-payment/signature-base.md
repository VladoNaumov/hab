Судя по вашему сообщению, вы работаете с API OP Financial Group (Payment Button API) в тестовой среде 
(https://sandbox.apis.op.fi/paymentbutton/v1/payments), и вам нужно создать подпись (signature) для аутентификации запроса. 

Должна формироваться на основе следующих данных:

```
HTTP-метод: POST
Content-Type: application/json
Дата и время в формате GMT: Wed, 06 Apr 2020 06:09:55 GMT
Идентификатор запроса: f8cef553-77df-48cc-bd1c-fb05dcfb64fa
API-ключ: dxB2AFwnwraQRrAsLZpJ5T4IrNGp7fhx
Идентификатор мерчанта: 1ec65769-2d9c-4cc8-8031-7327747c4d4c
Идентификатор аккаунта: b35e55a2-ef71-4675-b8cb-a154c650843b
URL эндпоинта: https://sandbox.apis.op.fi/paymentbutton/v1/payments

Тело запроса (JSON):
json
{
    "reference": "1234567978",
    "amount": "1.00",
    "currency": "EUR",
    "accountId": "8a5d4aac-8f8f-47ed-ae2f-36ffeaf57c79",
    "return": {"url": "https://shop.domain/return/path"},
    "cancel": {"url": "https://shop.domain/cancel/path"},
    "reject": {"url": "https://shop.domain/reject/path"}
}
```

На основе типичных требований API OP, подпись формируется следующим образом:
Составляется строка для подписи:
Объединяются указанные параметры в определенном порядке (например, метод, Content-Type, дата, идентификаторы, URL, тело запроса).
Тело запроса обычно преобразуется в хэш (например, SHA256) или используется как есть (в зависимости от документации).
Применяется HMAC-SHA256:
Составленная строка подписывается с использованием приватного ключа.
Результат кодируется, например, в base64 или hex.
Подпись добавляется в заголовки:
Обычно в заголовок X-Signature или Authorization.


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