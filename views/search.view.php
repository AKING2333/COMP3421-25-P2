<?php require_once __DIR__ . '/header.php'; ?>

<div class="section__search animation">
    <h1 class="search__title">Search Results</h1>
    <div class="search__query">
        Results for: "<?= htmlspecialchars($query) ?>"
    </div>

    <?php if (empty($products)): ?>
        <div class="search__empty">
            No products found matching your search.
        </div>
    <?php else: ?>
        <div class="row" id="products-container">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4">
                    <div class="product-card">
                        <img src="<?= htmlspecialchars($product['image_url'] ?? '/assets/image/products/default.jpg') ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>"
                             class="product-card__image">
                        
                        <div class="product-card__info">
                            <h3 class="product-card__title">
                                <?= htmlspecialchars($product['name']) ?>
                            </h3>
                            
                            <div class="product-card__category">
                                <?= htmlspecialchars($product['category']) ?>
                            </div>
                            
                            <div class="product-card__price">
                                HK$ <?= number_format($product['price'], 2) ?>
                            </div>
                            
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
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?> 