// 自定义分析跟踪工具
class Analytics {
    constructor() {
        try {
            this.sessionId = this.generateSessionId();
            this.pageLoadTime = new Date().getTime();
            this.prometheusEnabled = true;
            
            // 检查 Prometheus 连接状态
            this.checkPrometheusConnection();
            
            // 初始化跟踪
            this.trackPageview();
            this.setupEventListeners();
            this.trackPerformance();
        } catch (error) {
            console.error('Analytics initialization error:', error);
            // 即使初始化失败，也不应该影响页面功能
        }
    }
    
    // 检查 Prometheus 连接
    async checkPrometheusConnection() {
        try {
            const result = await this.sendToPrometheus('prometheus_check', 1, {status: 'check'});
            this.prometheusEnabled = result;
            if (!result) {
                console.warn('Prometheus connection failed, falling back to server-side analytics only');
            }
        } catch (error) {
            this.prometheusEnabled = false;
            console.warn('Prometheus connection check error, falling back to server-side analytics only');
        }
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
            console.log(`准备发送数据到 ${endpoint}`, data);
            
            const response = await fetch(`/analytics/${endpoint}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
                credentials: 'include'
            });
            
            // 检查响应状态
            if (!response.ok) {
                console.error(`${endpoint} 请求失败, 状态: ${response.status} ${response.statusText}`);
                // 尝试读取响应内容
                const responseText = await response.text();
                console.error(`响应内容:`, responseText);
                try {
                    // 尝试解析JSON
                    return JSON.parse(responseText);
                } catch (e) {
                    console.error(`${endpoint} 响应不是有效的JSON:`, e);
                    return { success: false, error: `Invalid response: ${responseText.substring(0, 100)}...` };
                }
            }
            
            // 尝试解析JSON响应
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.includes("application/json")) {
                return await response.json();
            } else {
                const text = await response.text();
                console.error(`${endpoint} 响应不是JSON格式:`, text);
                return { success: false, error: `Server did not return JSON: ${text.substring(0, 100)}...` };
            }
        } catch (error) {
            console.error(`Analytics error (${endpoint}):`, error);
            return { success: false, error: error.message };
        }
    }
    
    // 发送数据到Prometheus
    async sendToPrometheus(metricName, value, labels = {}) {
        try {
            // 检查值是否有效
            if (value === undefined || value === null) {
                value = 1; // 默认值
            }
            
            // 处理标签，确保全部为字符串
            const processedLabels = {};
            for (const [key, val] of Object.entries(labels)) {
                // 跳过过长的值，这可能导致格式错误
                if (val && String(val).length > 100) {
                    processedLabels[key] = String(val).substring(0, 100) + '...';
                } else {
                    processedLabels[key] = val !== null && val !== undefined ? String(val) : '';
                }
                
                // 替换特殊字符
                processedLabels[key] = processedLabels[key].replace(/"/g, '\\"');
            }
            
            // 构建标签字符串
            const labelString = Object.entries(processedLabels)
                .map(([key, val]) => `${key}="${val}"`)
                .join(',');
            
            // 构建指标字符串，确保末尾有换行符
            const metric = `${metricName}{${labelString}} ${value}\n`;
            
            console.log("发送指标:", metric);
            console.log("发送到URL:", '/prometheus-proxy.php?job=web_traffic');
            
            // 使用 PHP 代理发送数据到 Prometheus
            const response = await fetch('/prometheus-proxy.php?job=web_traffic', {
                method: 'POST',
                headers: { 'Content-Type': 'text/plain' },
                body: metric,
            });

            console.log('Prometheus响应状态:', response.status, response.statusText);
            
            if (!response.ok) {
                let errorText = '';
                try {
                    // 尝试解析JSON响应
                    const contentType = response.headers.get("content-type");
                    console.log('响应Content-Type:', contentType);
                    
                    // 读取响应文本
                    const responseText = await response.text();
                    console.log('原始响应内容:', responseText);
                    
                    if (contentType && contentType.includes("application/json")) {
                        try {
                            const errorData = JSON.parse(responseText);
                            errorText = JSON.stringify(errorData);
                        } catch (jsonError) {
                            errorText = `JSON解析错误: ${jsonError.message}, 原始内容: ${responseText}`;
                        }
                    } else {
                        // 如果不是JSON，则获取文本
                        errorText = responseText;
                    }
                } catch (e) {
                    errorText = `解析响应失败: ${e.message}`;
                }
                console.error(`推送指标失败: ${response.status} ${response.statusText}`, errorText);
                return false;
            }
            
            return true;
        } catch (error) {
            console.error(`Prometheus错误 (${metricName}):`, error);
            return false;
        }
    }
    
    // 发送页面访问数据到 Prometheus
    async trackPageview() {
        const data = {
            url: window.location.href,
            title: document.title,
            referrer: document.referrer,
            deviceType: this.getDeviceType(),
            browser: this.getBrowser(),
            timestamp: new Date().toISOString()
        };

        try {
            // 尝试推送到 Prometheus
            const pushResult = await this.sendToPrometheus('pageview_total', 1, {
                url: data.url.split('?')[0], // 移除查询参数
                referrer: data.referrer ? new URL(data.referrer).hostname : 'direct',
                deviceType: data.deviceType,
                browser: data.browser
            });
            
            // 无论 Prometheus 是否成功，都发送到服务器
            await this.sendData('pageview', data);
        } catch (error) {
            console.error('Failed to track pageview:', error);
        }
    }
    
    // 发送用户事件到 Prometheus
    async trackEvent(category, action, label = null, value = null, additionalData = {}) {
        const data = {
            category,
            action,
            label,
            value,
            pageUrl: window.location.href,
            additionalData,
            timestamp: new Date().toISOString()
        };

        try {
            // 尝试推送到 Prometheus
            const pushResult = await this.sendToPrometheus('event_total', 1, {
                category: category,
                action: action,
                label: label || 'none',
                value: value || 0
            });
            
            // 无论 Prometheus 是否成功，都发送到服务器
            await this.sendData('event', data);
        } catch (error) {
            console.error('Failed to track event:', error);
        }
    }
    
    // 发送性能指标到 Prometheus
    trackPerformance() {
        window.addEventListener('load', () => {
            setTimeout(async () => {
                try {
                    const perfData = window.performance.timing;
                    const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
                    const domContentLoaded = perfData.domContentLoadedEventEnd - perfData.navigationStart;
                    const ttfb = perfData.responseStart - perfData.navigationStart;

                    // 获取 First Contentful Paint (FCP)
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

                    // 准备数据
                    const data = {
                        pageUrl: window.location.href,
                        loadTime: pageLoadTime,
                        domContentLoaded: domContentLoaded,
                        firstContentfulPaint: firstContentfulPaint,
                        ttfb: ttfb
                    };

                    // 尝试推送到 Prometheus
                    await this.sendToPrometheus('performance_metrics', 1, {
                        pageUrl: window.location.href.split('?')[0],
                        loadTime: pageLoadTime,
                        domContentLoaded: domContentLoaded,
                        firstContentfulPaint: firstContentfulPaint,
                        ttfb: ttfb
                    });

                    // 发送到服务器
                    await this.sendData('performance', data);
                } catch (error) {
                    console.error('Failed to track performance:', error);
                }
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