// public/js/cart.js


/**
 * public/js/cart.js
 * JavaScript для управления корзиной: удаление товаров и изменение количества.
 * Использует AJAX-запросы для обновления данных на сервере.
 */

// Функция для показа уведомлений (например, "Товар удален" или "Ошибка")
function showAlert(type, message) {
    // Находим контейнер для уведомлений
    const container = document.querySelector('.container');
    if (!container) {
        console.warn('Container element not found.');
        return;
    }
    // Создаем элемент уведомления
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`; // Тип: success, danger, warning
    // Санитизируем сообщение для защиты от XSS (заменяем < и >)
    alert.textContent = String(message).replace(/</g, '&lt;').replace(/>/g, '&gt;');
    container.prepend(alert); // Добавляем уведомление в начало контейнера
    // Удаляем уведомление через 3 секунды
    setTimeout(() => alert.remove(), 3000);
}

// Функция для отправки AJAX-запросов
function sendRequest(url, options) {
    // Проверяем, что URL начинается с текущего домена (защита от внешних запросов)
    if (!url.startsWith(window.location.origin)) {
        throw new Error('Invalid request domain.');
    }
    return fetch(url, options)
        .then(response => {
            // Если ответ не успешен, пытаемся разобрать JSON ошибки
            if (!response.ok) {
                return response.text().then(text => {
                    try {
                        throw JSON.parse(text);
                    } catch {
                        throw { status: 'danger', message: 'Invalid server response.' };
                    }
                });
            }
            return response.json(); // Возвращаем JSON при успешном ответе
        });
}

// Функция для проверки, является ли значение положительным целым числом
function isPositiveInt(value) {
    const n = Number(value);
    return Number.isSafeInteger(n) && n > 0;
}

// Обработка удаления товара из корзины
document.querySelectorAll('.remove-form').forEach(form => {
    form.addEventListener('submit', function (e) {
        e.preventDefault(); // Отменяем стандартную отправку формы

        // Защита от двойных отправок
        if (form.dataset.locked === '1') return;
        form.dataset.locked = '1';

        // Проверяем наличие CSRF-токена
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showAlert('danger', 'CSRF token is missing.');
            form.dataset.locked = '0';
            return;
        }

        // Собираем данные формы
        const formData = new FormData(this);
        const itemId = this.action.split('/').pop(); // Извлекаем ID товара из URL формы

        // Проверяем, что ID товара валидный
        if (!isPositiveInt(itemId)) {
            showAlert('danger', 'Invalid item ID.');
            form.dataset.locked = '0';
            return;
        }

        // Отправляем запрос на удаление товара
        sendRequest(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
            .then(data => {
                // Проверяем, что ответ сервера содержит нужные поля
                if (!data.status || !data.message || typeof data.cart_total === 'undefined') {
                    throw { status: 'danger', message: 'Invalid server response.' };
                }
                showAlert(data.status, data.message); // Показываем уведомление

                // Если удаление успешно
                if (data.status === 'success') {
                    // Удаляем строку товара из таблицы
                    const row = document.querySelector(`tr[data-id="${itemId}"]`);
                    row?.remove();

                    // Обновляем общую сумму корзины
                    const cartTotal = document.getElementById('cart-total');
                    if (cartTotal) {
                        cartTotal.textContent = `Total: ${data.cart_total} EUR`;
                    } else {
                        console.warn('Cart total element not found.');
                    }

                    // Обновляем счетчик товаров в корзине
                    const badge = document.querySelector('.cart-count-badge');
                    if (badge) {
                        badge.textContent = data.cart_count;
                        // Если корзина пуста, показываем сообщение
                        if (data.cart_count === 0) {
                            badge.remove();
                            const cartContent = document.getElementById('cart-content');
                            if (cartContent) {
                                cartContent.innerHTML = ''; // Очищаем содержимое
                                const emptyCartDiv = document.createElement('div');
                                emptyCartDiv.className = 'empty-cart';
                                const p = document.createElement('p');
                                p.textContent = 'Your cart is empty.';
                                const a = document.createElement('a');
                                a.href = window.routes.productsIndex; // Ссылка на страницу товаров
                                a.className = 'btn btn-outline-secondary';
                                a.textContent = 'Back to Products';
                                emptyCartDiv.append(p, a);
                                cartContent.append(emptyCartDiv);
                            } else {
                                console.warn('Cart content element not found.');
                            }
                        }
                    }
                }
            })
            .catch(error => {
                // Показываем сообщение об ошибке
                showAlert(error.status || 'danger', error.message || 'An error occurred. Please try again.');
            })
            .finally(() => {
                form.dataset.locked = '0'; // Разблокируем форму
            });
    });
});

// Обработка изменения количества товара
document.querySelectorAll('.increment-btn, .decrement-btn').forEach(button => {
    button.addEventListener('click', function () {
        // Защита от двойных кликов
        if (button.dataset.locked === '1') return;
        button.dataset.locked = '1';

        // Проверяем CSRF-токен
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            showAlert('danger', 'CSRF token is missing.');
            button.dataset.locked = '0';
            return;
        }

        // Получаем ID товара из атрибута data-id
        const id = this.dataset.id;
        if (!isPositiveInt(id)) {
            showAlert('danger', 'Invalid item ID.');
            button.dataset.locked = '0';
            return;
        }

        // Находим поле ввода количества
        const input = this.closest('td').querySelector('.quantity-input');
        if (!input) {
            showAlert('danger', 'Quantity input not found.');
            button.dataset.locked = '0';
            return;
        }

        // Получаем текущее количество
        let quantity = parseInt(input.value, 10);
        if (isNaN(quantity)) {
            showAlert('warning', 'Invalid quantity.');
            button.dataset.locked = '0';
            return;
        }

        // Увеличиваем или уменьшаем количество
        quantity += this.classList.contains('increment-btn') ? 1 : -1;
        // Проверяем минимальное количество (1)
        if (quantity < 1) {
            showAlert('warning', 'Quantity cannot be less than 1.');
            button.dataset.locked = '0';
            return;
        }
        // Проверяем максимальное количество (10)
        const MAX_QUANTITY = 10;
        if (quantity > MAX_QUANTITY) {
            showAlert('warning', `Quantity cannot exceed ${MAX_QUANTITY}.`);
            button.dataset.locked = '0';
            return;
        }

        // Отключаем кнопку во время запроса
        button.disabled = true;

        // Отправляем запрос на обновление количества
        sendRequest(window.routes.cartUpdate.replace(':id', id), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ quantity })
        })
            .then(data => {
                // Проверяем, что ответ сервера содержит нужные поля
                if (!data.status || !data.message || typeof data.cart_total === 'undefined') {
                    throw { status: 'danger', message: 'Invalid server response.' };
                }
                showAlert(data.status, data.message); // Показываем уведомление

                // Если обновление успешно
                if (data.status === 'success') {
                    input.value = quantity; // Обновляем поле ввода

                    // Обновляем сумму для товара
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        const itemTotal = row.querySelector('.item-total');
                        if (itemTotal) {
                            itemTotal.textContent = `${data.item_total} EUR`;
                        }
                    }

                    // Обновляем общую сумму корзины
                    const cartTotal = document.getElementById('cart-total');
                    if (cartTotal) {
                        cartTotal.textContent = `Total: ${data.cart_total} EUR`;
                    }

                    // Обновляем счетчик товаров
                    const badge = document.querySelector('.cart-count-badge');
                    if (badge) {
                        badge.textContent = data.cart_count;
                    }
                }
            })
            .catch(error => {
                showAlert(error.status || 'danger', error.message || 'An error occurred. Please try again.');
            })
            .finally(() => {
                button.disabled = false; // Включаем кнопку
                button.dataset.locked = '0'; // Разблокируем кнопку
            });
    });
});
