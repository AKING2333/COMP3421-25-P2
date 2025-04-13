// 自定义分析跟踪工具
class Analytics {
    constructor() {
        this.sessionId = this.generateSessionId();
        this.pageLoadTime = new Date().getTime();
        this.trackPageview();
        this.setupEventListeners();
        this.trackPerformance();
    }
    
    // 生成会话ID
    generateSessionId() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }
    
    // 发送数据到服务器
    async sendData(endpoint, data) {
        try {
            const response = await fetch(`/analytics/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
                credentials: 'include'
            });
            return await response.json();
        } catch (error) {
            console.error(`Analytics error (${endpoint}):`, error);
            return { success: false, error: error.message };
        }
    }
    
    // 跟踪页面访问
    trackPageview() {
        const data = {
            url: window.location.href,
            title: document.title,
            referrer: document.referrer,
            deviceType: this.getDeviceType(),
            browser: this.getBrowser(),
            timestamp: new Date().toISOString()
        };
        
        this.sendData('pageview', data);
    }
    
    // 跟踪事件
    trackEvent(category, action, label = null, value = null, additionalData = {}) {
        const data = {
            category,
            action,
            label,
            value,
            pageUrl: window.location.href,
            additionalData,
            timestamp: new Date().toISOString()
        };
        
        this.sendData('event', data);
    }
    
    // 跟踪性能指标
    trackPerformance() {
        window.addEventListener('load', () => {
            setTimeout(() => {
                const perfData = window.performance.timing;
                const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
                const domContentLoaded = perfData.domContentLoadedEventEnd - perfData.navigationStart;
                const ttfb = perfData.responseStart - perfData.navigationStart;
                
                // 获取First Contentful Paint (FCP)
                let firstContentfulPaint = 0;
                const paintMetrics = performance.getEntriesByType('paint');
                if (paintMetrics && paintMetrics.length) {
                    for (let paint of paintMetrics) {
                        if (paint.name === 'first-contentful-paint') {
                            firstContentfulPaint = paint.startTime;
                            break;
                        }
                    }
                }
                
                const data = {
                    pageUrl: window.location.href,
                    loadTime: pageLoadTime,
                    domContentLoaded: domContentLoaded,
                    firstContentfulPaint: firstContentfulPaint,
                    ttfb: ttfb
                };
                
                this.sendData('performance', data);
            }, 0);
        });
    }
    
    // 设置事件监听器
    setupEventListeners() {
        // 监听点击事件
        document.addEventListener('click', (e) => {
            const target = e.target;
            
            // 跟踪链接点击
            if (target.tagName === 'A' || target.closest('a')) {
                const link = target.tagName === 'A' ? target : target.closest('a');
                const linkText = link.textContent.trim();
                const linkUrl = link.href;
                
                this.trackEvent('Link', 'click', linkText, null, { 
                    linkUrl: linkUrl, 
                    linkId: link.id || null, 
                    linkClass: link.className || null 
                });
            }
            
            // 跟踪按钮点击
            if (target.tagName === 'BUTTON' || target.closest('button')) {
                const button = target.tagName === 'BUTTON' ? target : target.closest('button');
                const buttonText = button.textContent.trim();
                
                this.trackEvent('Button', 'click', buttonText, null, { 
                    buttonId: button.id || null, 
                    buttonClass: button.className || null 
                });
            }
            
            // 跟踪产品点击
            if (target.closest('.product-card')) {
                const productCard = target.closest('.product-card');
                const productId = productCard.dataset.productId;
                const productName = productCard.querySelector('.product-title')?.textContent || 'Unknown Product';
                
                this.trackEvent('Product', 'view', productName, null, { productId });
            }
        });
        
        // 监听表单提交
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const formId = form.id || form.action;
            
            this.trackEvent('Form', 'submit', formId);
        });
        
        // 跟踪添加到购物车事件
        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const productId = btn.dataset.productId;
                const productName = btn.dataset.productName || 'Product';
                const price = btn.dataset.price || 0;
                
                this.trackEvent('Ecommerce', 'add_to_cart', productName, price, { productId });
            });
        });
        
        // 跟踪结账流程
        if (window.location.pathname.includes('/cart/confirm')) {
            this.trackEvent('Ecommerce', 'begin_checkout');
        }
        
        // 跟踪订单完成
        if (window.location.pathname.includes('/order/confirmation')) {
            const orderId = window.location.pathname.split('/').pop();
            this.trackEvent('Ecommerce', 'purchase', 'Order Completed', null, { orderId });
        }
    }
    
    // 获取设备类型
    getDeviceType() {
        const userAgent = navigator.userAgent;
        if (/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i.test(userAgent)) {
            return 'tablet';
        }
        if (/Mobile|Android|iP(hone|od)|IEMobile|BlackBerry|Kindle|Silk-Accelerated|(hpw|web)OS|Opera M(obi|ini)/.test(userAgent)) {
            return 'mobile';
        }
        return 'desktop';
    }
    
    // 获取浏览器信息
    getBrowser() {
        const userAgent = navigator.userAgent;
        let browserName;
        
        if (userAgent.match(/chrome|chromium|crios/i)) {
            browserName = "Chrome";
        } else if (userAgent.match(/firefox|fxios/i)) {
            browserName = "Firefox";
        } else if (userAgent.match(/safari/i)) {
            browserName = "Safari";
        } else if (userAgent.match(/opr\//i)) {
            browserName = "Opera";
        } else if (userAgent.match(/edg/i)) {
            browserName = "Edge";
        } else if (userAgent.match(/trident/i)) {
            browserName = "IE";
        } else {
            browserName = "Unknown";
        }
        
        return browserName;
    }
}

// 初始化分析工具
document.addEventListener('DOMContentLoaded', () => {
    window.analyticsTracker = new Analytics();
});