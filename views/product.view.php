<?php require_once __DIR__ . '/header.php'; ?>

<div class="section__product animation">
    <div class="product__container">
        <div class="product__image">
            <img src="<?= htmlspecialchars($product['image_url'] ?? '/assets/image/products/default.jpg') ?>" 
                 alt="<?= htmlspecialchars($product['name']) ?>"
                 class="product__img">
        </div>
        
        <div class="product__info">
            <h1 class="product__title"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="product__category">
                Category: <?= htmlspecialchars($product['category']) ?>
            </div>
            
            <div class="product__price">
                HK$ <?= number_format($product['price'], 2) ?>
            </div>
            
            <div class="product__description">
                <?= nl2br(htmlspecialchars($product['description'] ?? '')) ?>
            </div>
            
            <div class="product__actions">
                <button onclick="addToCart(<?= $product['id'] ?>, 
                                        '<?= htmlspecialchars($product['name']) ?>', 
                                        '<?= htmlspecialchars($product['category']) ?>', 
                                        <?= $product['price'] ?>)" 
                        class="btn btn-primary">
                    Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function addToCart(productId, productName, productCategory, price) {
    fetch('/cart/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            product_id: productId,
            quantity: 1
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // 跟踪添加到购物车事件
            trackEvent('Product', 'add_to_cart', productName, price);
            alert('Product added to cart successfully!');
        } else {
            alert('Failed to add product: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add product, please try again later');
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?> 