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

    <button id="view-more" class="btn btn-primary">View More</button>
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
                
                // Reset view-more button visibility
                document.getElementById('view-more').style.display = 'block';
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

        // Auto scroll to visible area
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

document.getElementById('view-more').addEventListener('click', function() {
    const container = document.getElementById('products-container');
    const offset = container.children.length; // Current product count

    // Get current category ID
    const activeCategoryButton = document.querySelector('.shop__category.active');
    const categoryId = activeCategoryButton ? activeCategoryButton.dataset.categoryId : 1;

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `/products/load-more/${categoryId}/${offset}`, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.responseType = 'json';

    xhr.onload = function() {
        if (xhr.status >= 200 && xhr.status < 300) {
            if (xhr.response) {
                const response = xhr.response;
                
                // Append new products
                container.innerHTML += response.html;
                
                // Hide "View More" button if no more products
                if (!response.hasMore) {
                    document.getElementById('view-more').style.display = 'none';
                }
                
                // If no products were returned, hide the button
                if (response.html.trim() === '') {
                    document.getElementById('view-more').style.display = 'none';
                }
            }
        } else {
            console.error('Request failed with status:', xhr.status);
        }
    };

    xhr.onerror = function() {
        console.error('Network Error');
    };

    xhr.send();
});

function addToCart(productId, productName, productCategory, price) {
    // Send request to add to cart
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
            // Track add to cart event
            trackEvent('Product', 'add_to_cart', productName, price);
            alert('Product has been added to cart!');
        } else {
            alert('Failed to add: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to add, please try again later');
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>