/**
 * public/js/product_show.js
 * JavaScript для управления количеством товара на странице просмотра.
 * Обновляет поле ввода количества без отправки запросов на сервер.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Находим элементы: поле ввода количества и кнопки +/-
    const quantityInput = document.querySelector('input[name="quantity"]');
    const incrementBtn = document.querySelector('.quantity-control .btn:last-child');
    const decrementBtn = document.querySelector('.quantity-control .btn:first-child');

    // Проверяем, что все элементы существуют
    if (!quantityInput || !incrementBtn || !decrementBtn) {
        console.warn('Quantity input or control buttons not found.');
        return;
    }

    // Функция для проверки, является ли значение валидным количеством
    function isValidQuantity(value) {
        const n = parseInt(value, 10);
        return !isNaN(n) && Number.isSafeInteger(n) && n >= 1;
    }

    // Обработчик кнопки увеличения количества
    incrementBtn.addEventListener('click', () => {
        // Защита от двойных кликов
        if (incrementBtn.dataset.locked === '1') return;
        incrementBtn.dataset.locked = '1';

        // Получаем максимальное количество (из атрибута max или 10)
        const max = parseInt(quantityInput.max) || 10;
        let value = parseInt(quantityInput.value) || 1;

        // Проверяем валидность текущего значения
        if (!isValidQuantity(value)) {
            console.warn('Invalid quantity value:', quantityInput.value);
            quantityInput.value = 1; // Сбрасываем на 1, если значение некорректное
            incrementBtn.dataset.locked = '0';
            return;
        }

        // Увеличиваем количество, если не достигнут максимум
        if (value < max) {
            quantityInput.value = value + 1;
        } else {
            console.warn('Maximum quantity reached:', max);
        }

        incrementBtn.dataset.locked = '0'; // Разблокируем кнопку
    });

    // Обработчик кнопки уменьшения количества
    decrementBtn.addEventListener('click', () => {
        // Защита от двойных кликов
        if (decrementBtn.dataset.locked === '1') return;
        decrementBtn.dataset.locked = '1';

        let value = parseInt(quantityInput.value) || 1;

        // Проверяем валидность текущего значения
        if (!isValidQuantity(value)) {
            console.warn('Invalid quantity value:', quantityInput.value);
            quantityInput.value = 1; // Сбрасываем на 1
            decrementBtn.dataset.locked = '0';
            return;
        }

        // Уменьшаем количество, если больше 1
        if (value > 1) {
            quantityInput.value = value - 1;
        }

        decrementBtn.dataset.locked = '0'; // Разблокируем кнопку
    });

    // Обработка ручного ввода в поле количества
    quantityInput.addEventListener('input', () => {
        let value = parseInt(quantityInput.value) || 1;
        const max = parseInt(quantityInput.max) || 10;

        // Корректируем значение, если оно вне диапазона
        if (!isValidQuantity(value) || value < 1) {
            quantityInput.value = 1;
        } else if (value > max) {
            quantityInput.value = max;
        }
    });
});
