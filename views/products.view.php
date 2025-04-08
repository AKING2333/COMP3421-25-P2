<?php require_once __DIR__ . '/header.php'; ?>

<div id="shop" class="section__shop animation">
    <h1 class="shop__title">Shop</h1>
    <pre class="shop__description">
·Clothing: Show your PolyU pride with stylish and comfortable apparel for every occasion.

·Accessories: Explore unique items like bags, hats, and more, designed to complement your daily life.

·Stationery: Find practical and beautifully designed stationery to enhance your productivity.

·Souvenirs: Choose from a variety of memorable keepsakes to celebrate your PolyU connection.
</pre>

    <div class="shop__categories">
        <?php foreach ($categories as $category): ?>
        <div class="shop__category" data-category-id="<?= $category['id'] ?>">
            <?= htmlspecialchars($category['name']) ?>
        </div>
        <?php endforeach; ?>
    </div>

        <div class="row">
        <?php foreach ($products as $product): ?>
        <div class="col-lg-4 mb-4">
                <div class="shop__card">
                <!-- 商品图片 -->
                <img src="<?= 
                    (!empty($product['image_url']) && file_exists($_SERVER['DOCUMENT_ROOT'].$product['image_url'])) 
                    ? htmlspecialchars($product['image_url']) 
                    : '/assets/image/default.jpg' ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>"
                     class="shop__card__image">

                <h2 class="shop__card__title">
                    <?= htmlspecialchars($product['name']) ?>
                </h2>
                
                <p class="shop__card__description">
                    <?= htmlspecialchars($product['description']) ?>
                </p>
                
                <p class="shop__card__price">
                    Price <span><?= number_format($product['price'], 2) ?>$</span>
                </p>

                <!-- 操作按钮组 -->
                <div class="shop__card__actions">
                    <form action="/cart/add" method="POST" class="d-inline">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <button type="submit" class="btn btn-primary">
                            <svg class="shop__card__icon">[...]</svg>
                            Add to Cart
                        </button>
                    </form>
                    
                    <a href="/products/<?= $product['id'] ?>" 
                       class="btn btn-outline-secondary">
                        Details
                    </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>