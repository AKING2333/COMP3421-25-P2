<?php require_once __DIR__ . '/header.php'; ?>
<link rel="stylesheet" href="/assets/css/confirm.css">
<div class="confirm-container">
    <h1 class="confirm-title">Confirm Your Order</h1>
    <form action="/cart/payment" method="POST">
        <div class="form-group">
            <label for="address-line1">Address Line 1:</label>
            <input type="text" id="address-line1" name="address_line1" required>
        </div>
        <div class="form-group">
            <label for="address-line2">Address Line 2:</label>
            <input type="text" id="address-line2" name="address_line2">
        </div>
        <div class="form-group">
            <label for="address-line3">Address Line 3:</label>
            <input type="text" id="address-line3" name="address_line3">
        </div>
        <div class="form-group">
            <label for="contact">Contact Number:</label>
            <select name="country_code" id="country-code">
                <option value="+852">+852</option>
                <option value="+86">+86</option>
            </select>
            <input type="text" id="contact" name="contact" required>
        </div>
        <button type="submit" class="btn btn-primary">Proceed to Payment</button>
    </form>
</div>
<?php require_once __DIR__ . '/footer.php'; ?> 