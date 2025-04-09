<?php foreach ($products as $product): ?>
<div class="col-lg-4 mb-4">
    <div class="shop__card">
        <img src="<?= 
            (!empty($product['image_url']) && file_exists($_SERVER['DOCUMENT_ROOT'].$product['image_url'])) 
            ? htmlspecialchars($product['image_url']) 
            : '/assets/image/default.jpg' ?>" 
             alt="<?= htmlspecialchars($product['name']) ?>"
             class="shop__card__image">
        <h2 class="shop__card__title"><?= htmlspecialchars($product['name']) ?></h2>
        <p class="shop__card__description"><?= htmlspecialchars($product['description']) ?></p>
        <p class="shop__card__price">Price <span><?= number_format($product['price'], 2) ?>$</span></p>
        <div class="shop__card__actions">
            <form action="/cart/add" method="POST" class="d-inline">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i>
                    Add to Cart
                </button>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?> 