<!DOCTYPE html>
<html>
<head>
    <title>OP Payment Test</title>
</head>
<body>
<h1>Тестовый платеж</h1>
<form action="initiate_payment.php" method="POST">
    <label>Сумма (EUR):</label>
    <input type="number" name="amount" value="10.00" step="0.01" required><br><br>
    <label>Номер заказа:</label>
    <input type="text" name="reference" value="ORDER<?php echo time(); ?>" required><br><br>
    <input type="submit" value="Оплатить">
</form>
</body>
</html>