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
        <button class="shop__category <?= $category['id'] === 1 ? 'active' : '' ?>" 
                data-category-id="<?= $category['id'] ?>">
            <?= htmlspecialchars($category['name']) ?>
        </button>
        <?php endforeach; ?>
    </div>

    <div class="row" id="products-container">
        <?php require __DIR__ . '/partials/product_list.php'; ?>
    </div>
</div>

<script>
document.querySelectorAll('.shop__category').forEach(button => {
    button.addEventListener('click', function() {
        // Remove active class from all buttons
        document.querySelectorAll('.shop__category').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        
        const categoryId = this.dataset.categoryId;
        const xhr = new XMLHttpRequest();
        
        xhr.open('GET', `/products/category/${categoryId}`, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 300) {
                document.getElementById('products-container').innerHTML = xhr.responseText;
            } else {
                console.error('Request failed with status:', xhr.status);
                document.getElementById('products-container').innerHTML = 
                    '<div class="alert alert-danger">Error loading products (HTTP '+xhr.status+')</div>';
            }
        };
        
        xhr.onerror = function() {
            console.error('Network Error');
            document.getElementById('products-container').innerHTML = 
                '<div class="alert alert-danger">Network error occurred</div>';
        };
        
        xhr.send();

        // 自动滚动到可见区域
        const container = this.parentElement;
        const containerWidth = container.offsetWidth;
        const buttonLeft = this.offsetLeft;
        const buttonWidth = this.offsetWidth;
        
        container.scrollTo({
            left: buttonLeft - (containerWidth - buttonWidth)/2,
            behavior: 'smooth'
        });
    });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>