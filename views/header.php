<head>

    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Polyu Store</title>

    <!--  CSS  -->

    <!-- <link href="/assets/css/style.css" rel="stylesheet"> -->
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> -->

    <link rel="stylesheet" href="/../assets/css/colors.css">
    <link rel="stylesheet" href="/../assets/css/general.css">
    <link rel="stylesheet" href="/../assets/css/navbar.css">
    <link rel="stylesheet" href="/../assets/css/header.css">
    <link rel="stylesheet" href="/../assets/css/about.css">
    <link rel="stylesheet" href="/../assets/css/shop.css">
    <link rel="stylesheet" href="/../assets/css/faq.css">
    <link rel="stylesheet" href="/../assets/css/reviews.css">
    <link rel="stylesheet" href="/../assets/css/footer.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">



</head>


<div class="section__navbar">

    <div class="navbar">

        <div class="navbar__header">
            <a href="/" class="navbar__brand">
                PolyU Store
            </a>

            <div class="navbar__menu" onclick="navbar()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="14" viewBox="0 0 20 14">
                    <g id="menu" transform="translate(-2 -5)">
                        <line id="Line_1" data-name="Line 1" x2="15" transform="translate(6 12)" fill="none"
                                stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        <line id="Line_2" data-name="Line 2" x2="18" transform="translate(3 6)" fill="none"
                                stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                        <line id="Line_3" data-name="Line 3" x2="18" transform="translate(3 18)" fill="none"
                                stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    </g>
                </svg>
            </div>
        </div>

        <div class="navbar__nav" id="navbarNav">

            <ul>
                <a href="/" class="navbar__href">
                    <li class="navbar__link">Home</li>
                </a>
                <a href="/products" class="navbar__href">
                    <li class="navbar__link">Shop</li>
                </a>
                <a href="/cart" class="navbar__href">
                    <li class="navbar__link">Cart</li>
                </a>
                <a href="/login" class="navbar__href">
                    <li class="navbar__link">Login</li>
                </a>
                <a href="/register" class="navbar__href">
                    <li class="navbar__link">Register</li>
                </a>
                <a href="/logout" class="navbar__href">
                    <li class="navbar__link">Log out</li>
                </a>
                <a href="/about" class="navbar__href">
                    <li class="navbar__link">About</li>
                </a>
                <a href="/order/history" class="navbar__href">
                    <li class="navbar__link">View Purchase History</li>
                </a>
            </ul>

        </div>

    </div>

</div>

<script>
// 获取浏览器和设备信息
function getBrowserInfo() {
    const ua = navigator.userAgent;
    let browser = 'unknown';
    
    if (ua.includes('Chrome')) browser = 'Chrome';
    else if (ua.includes('Firefox')) browser = 'Firefox';
    else if (ua.includes('Safari')) browser = 'Safari';
    else if (ua.includes('Edge')) browser = 'Edge';
    else if (ua.includes('Opera')) browser = 'Opera';
    
    return browser;
}

function getDeviceType() {
    const ua = navigator.userAgent;
    if (/mobile/i.test(ua)) return 'mobile';
    if (/tablet/i.test(ua)) return 'tablet';
    return 'desktop';
}

// 跟踪页面访问
function trackPageview() {
    const data = {
        url: window.location.pathname,
        referrer: document.referrer,
        device_type: getDeviceType(),
        browser: getBrowserInfo()
    };
    
    fetch('/analytics/pageview', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    }).catch(error => console.error('Error tracking pageview:', error));
}

// 跟踪事件
function trackEvent(category, action, label = '', value = '') {
    const data = {
        category: category,
        action: action,
        label: label,
        value: value,
        device_type: getDeviceType(),
        browser: getBrowserInfo()
    };
    
    fetch('/analytics/event', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    }).catch(error => console.error('Error tracking event:', error));
}

// 跟踪性能指标
function trackPerformance() {
    if (window.performance && window.performance.timing) {
        const timing = window.performance.timing;
        const loadTime = timing.loadEventEnd - timing.navigationStart;
        
        const data = {
            page_url: window.location.pathname,
            load_time: loadTime,
            device_type: getDeviceType(),
            browser: getBrowserInfo()
        };
        
        fetch('/analytics/performance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        }).catch(error => console.error('Error tracking performance:', error));
    }
}

// 页面加载完成后跟踪访问和性能
document.addEventListener('DOMContentLoaded', () => {
    trackPageview();
    // 等待页面完全加载后再跟踪性能
    window.addEventListener('load', () => {
        setTimeout(trackPerformance, 0);
    });
});

// 为所有按钮添加点击事件跟踪
document.addEventListener('click', (event) => {
    const target = event.target;
    if (target.tagName === 'BUTTON' || target.tagName === 'A') {
        trackEvent('Button', 'click', target.textContent || target.innerText);
    }
});

// 跟踪添加到购物车事件
window.trackAddToCart = function(productName, productCategory, price) {
    trackEvent('Product', 'add_to_cart', productName, price);
};
</script>

