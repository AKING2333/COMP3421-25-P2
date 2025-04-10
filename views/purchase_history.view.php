<?php require_once __DIR__ . '/header.php'; ?>
<link rel="stylesheet" href="/assets/css/purchase_history.css">
<div class="purchase-history-container">
    <h1 class="purchase-history-title">Purchase History</h1>
    <?php if (empty($orders)): ?>
        <p>You have no purchase history.</p>
    <?php else: ?>
        <table class="purchase-history-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= number_format($order['total_amount'], 2) ?>$</td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td><?= htmlspecialchars($order['created_at']) ?></td>
                        <td>
                            <?php foreach (explode(', ', $order['items']) as $item): list($productName, $quantity) = explode(':', $item); ?>
                                <?= htmlspecialchars($productName) ?> x <?= htmlspecialchars($quantity) ?><br>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php if ($order['status'] === 'pending'): ?>
                                <button onclick="window.location.href='/order/repay/<?= $order['id'] ?>'" class="btn btn-warning">Repay</button>
                                <button onclick="window.location.href='/order/cancel/<?= $order['id'] ?>'" class="btn btn-danger">Cancel</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/footer.php'; ?> 