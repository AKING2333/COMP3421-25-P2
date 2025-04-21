<?php require_once __DIR__ . '/header.php'; ?>
<link rel="stylesheet" href="/assets/css/order_confirmation.css">
<div class="order-confirmation-container">
    <h1 class="order-confirmation-title">Order Confirmation</h1>
    <?php if (empty($orderDetails)): ?>
        <p>Order details not available.</p>
    <?php else: ?>
        <table class="order-details-table">
            <thead>
                <tr>
                    <th>Product ID</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orderDetails as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_id']) ?></td>
                        <td><?= htmlspecialchars($item['quantity']) ?></td>
                        <td><?= number_format($item['unit_price'], 2) ?>$</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    <button onclick="window.location.href='/'" class="btn btn-primary">Return to Home</button>
</div>
<?php require_once __DIR__ . '/footer.php'; ?> 