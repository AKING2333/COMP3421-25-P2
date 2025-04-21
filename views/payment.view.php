<?php require_once __DIR__ . '/header.php'; ?>
<link rel="stylesheet" href="/assets/css/payment.css">
<div class="payment-container">
    <h1 class="payment-title">Payment</h1>
    <p>Please complete your payment to finalize the order.</p>
    <form action="/cart/payment" method="POST" onsubmit="return simulatePayment();">
        <button type="submit" class="btn btn-success">Complete Payment</button>
    </form>
</div>

<script>
function simulatePayment() {
    alert('Payment successful! Redirecting to homepage...');
    window.location.href = '/';
    return false; // Prevent actual form submission
}
</script>
<?php require_once __DIR__ . '/footer.php'; ?> 