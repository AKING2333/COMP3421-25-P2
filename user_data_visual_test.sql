-- 首先确保分析表存在（如果不存在则创建）
CREATE TABLE IF NOT EXISTS analytics_pageviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    user_id INT NULL,
    url VARCHAR(255) NOT NULL,
    page_title VARCHAR(255),
    referrer VARCHAR(255),
    user_agent TEXT,
    ip_address VARCHAR(45),
    country VARCHAR(50),
    city VARCHAR(50),
    device_type VARCHAR(20),
    browser VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (created_at),
    INDEX (url),
    INDEX (session_id)
);

CREATE TABLE IF NOT EXISTS analytics_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    user_id INT NULL,
    event_category VARCHAR(50) NOT NULL,
    event_action VARCHAR(50) NOT NULL,
    event_label VARCHAR(255),
    event_value INT,
    page_url VARCHAR(255),
    additional_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    INDEX (event_category, event_action),
    INDEX (created_at),
    INDEX (session_id)
);

CREATE TABLE IF NOT EXISTS analytics_performance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(100),
    page_url VARCHAR(255),
    load_time FLOAT,
    dom_content_loaded FLOAT,
    first_contentful_paint FLOAT,
    ttfb FLOAT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (created_at),
    INDEX (session_id),
    INDEX (page_url)
);

-- 添加示例用户数据（如果还没有用户）
INSERT INTO users (username, email, password_hash, created_at)
VALUES 
('testuser1', 'test1@example.com', '$2y$10$Abcdefghijklmnopqrstu.vwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', NOW() - INTERVAL 30 DAY),
('testuser2', 'test2@example.com', '$2y$10$Abcdefghijklmnopqrstu.vwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', NOW() - INTERVAL 25 DAY),
('testuser3', 'test3@example.com', '$2y$10$Abcdefghijklmnopqrstu.vwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', NOW() - INTERVAL 20 DAY),
('testuser4', 'test4@example.com', '$2y$10$Abcdefghijklmnopqrstu.vwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', NOW() - INTERVAL 15 DAY),
('testuser5', 'test5@example.com', '$2y$10$Abcdefghijklmnopqrstu.vwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', NOW() - INTERVAL 10 DAY);

-- 添加页面访问数据（过去30天的数据）
INSERT INTO analytics_pageviews (session_id, user_id, url, page_title, referrer, user_agent, ip_address, country, city, device_type, browser, created_at)
VALUES
-- 用户1的访问数据
('sess_001', 1, '/', '首页', 'https://google.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.1', '中国', '北京', 'desktop', 'Chrome', NOW() - INTERVAL 29 DAY),
('sess_001', 1, '/products', '产品列表', '/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.1', '中国', '北京', 'desktop', 'Chrome', NOW() - INTERVAL 29 DAY),
('sess_001', 1, '/product/2', '产品详情', '/products', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.1', '中国', '北京', 'desktop', 'Chrome', NOW() - INTERVAL 29 DAY),
('sess_001', 1, '/cart', '购物车', '/product/2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.1', '中国', '北京', 'desktop', 'Chrome', NOW() - INTERVAL 29 DAY),
('sess_001', 1, '/cart/confirm', '确认订单', '/cart', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.1', '中国', '北京', 'desktop', 'Chrome', NOW() - INTERVAL 29 DAY),
('sess_001', 1, '/order/confirmation/1', '订单确认', '/cart/confirm', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.1', '中国', '北京', 'desktop', 'Chrome', NOW() - INTERVAL 29 DAY),

-- 用户2的访问数据（在多个日期）
('sess_002', 2, '/', '首页', 'https://baidu.com', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)', '192.168.1.2', '中国', '上海', 'mobile', 'Safari', NOW() - INTERVAL 25 DAY),
('sess_002', 2, '/products', '产品列表', '/', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)', '192.168.1.2', '中国', '上海', 'mobile', 'Safari', NOW() - INTERVAL 25 DAY),
('sess_002', 2, '/product/3', '产品详情', '/products', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)', '192.168.1.2', '中国', '上海', 'mobile', 'Safari', NOW() - INTERVAL 25 DAY),

('sess_003', 2, '/', '首页', 'https://bing.com', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)', '192.168.1.2', '中国', '上海', 'mobile', 'Safari', NOW() - INTERVAL 20 DAY),
('sess_003', 2, '/products', '产品列表', '/', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)', '192.168.1.2', '中国', '上海', 'mobile', 'Safari', NOW() - INTERVAL 20 DAY),
('sess_003', 2, '/product/1', '产品详情', '/products', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)', '192.168.1.2', '中国', '上海', 'mobile', 'Safari', NOW() - INTERVAL 20 DAY),
('sess_003', 2, '/cart', '购物车', '/product/1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0)', '192.168.1.2', '中国', '上海', 'mobile', 'Safari', NOW() - INTERVAL 20 DAY),

-- 用户3的访问数据
('sess_004', 3, '/', '首页', 'https://google.com', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15)', '192.168.1.3', '美国', '纽约', 'desktop', 'Firefox', NOW() - INTERVAL 15 DAY),
('sess_004', 3, '/about', '关于我们', '/', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15)', '192.168.1.3', '美国', '纽约', 'desktop', 'Firefox', NOW() - INTERVAL 15 DAY),
('sess_004', 3, '/products', '产品列表', '/about', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15)', '192.168.1.3', '美国', '纽约', 'desktop', 'Firefox', NOW() - INTERVAL 15 DAY),

-- 无登录用户的访问数据
('sess_005', NULL, '/', '首页', 'https://yahoo.com', 'Mozilla/5.0 (Linux; Android 10)', '192.168.1.4', '日本', '东京', 'mobile', 'Chrome', NOW() - INTERVAL 10 DAY),
('sess_005', NULL, '/products', '产品列表', '/', 'Mozilla/5.0 (Linux; Android 10)', '192.168.1.4', '日本', '东京', 'mobile', 'Chrome', NOW() - INTERVAL 10 DAY),
('sess_005', NULL, '/login', '登录', '/products', 'Mozilla/5.0 (Linux; Android 10)', '192.168.1.4', '日本', '东京', 'mobile', 'Chrome', NOW() - INTERVAL 10 DAY),

-- 更多最近的访问数据（过去7天）
('sess_006', 4, '/', '首页', 'https://google.com', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.5', '英国', '伦敦', 'desktop', 'Edge', NOW() - INTERVAL 7 DAY),
('sess_006', 4, '/products', '产品列表', '/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.5', '英国', '伦敦', 'desktop', 'Edge', NOW() - INTERVAL 7 DAY),
('sess_006', 4, '/product/5', '产品详情', '/products', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.5', '英国', '伦敦', 'desktop', 'Edge', NOW() - INTERVAL 7 DAY),
('sess_006', 4, '/cart', '购物车', '/product/5', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.5', '英国', '伦敦', 'desktop', 'Edge', NOW() - INTERVAL 7 DAY),
('sess_006', 4, '/cart/confirm', '确认订单', '/cart', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.5', '英国', '伦敦', 'desktop', 'Edge', NOW() - INTERVAL 7 DAY),
('sess_006', 4, '/order/confirmation/2', '订单确认', '/cart/confirm', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)', '192.168.1.5', '英国', '伦敦', 'desktop', 'Edge', NOW() - INTERVAL 7 DAY),

-- 最近3天的访问数据
('sess_007', 5, '/', '首页', 'https://instagram.com', 'Mozilla/5.0 (iPad; CPU OS 14_0)', '192.168.1.6', '澳大利亚', '悉尼', 'tablet', 'Safari', NOW() - INTERVAL 3 DAY),
('sess_007', 5, '/products', '产品列表', '/', 'Mozilla/5.0 (iPad; CPU OS 14_0)', '192.168.1.6', '澳大利亚', '悉尼', 'tablet', 'Safari', NOW() - INTERVAL 3 DAY),
('sess_007', 5, '/register', '注册', '/products', 'Mozilla/5.0 (iPad; CPU OS 14_0)', '192.168.1.6', '澳大利亚', '悉尼', 'tablet', 'Safari', NOW() - INTERVAL 3 DAY),
('sess_007', 5, '/login', '登录', '/register', 'Mozilla/5.0 (iPad; CPU OS 14_0)', '192.168.1.6', '澳大利亚', '悉尼', 'tablet', 'Safari', NOW() - INTERVAL 3 DAY),
('sess_007', 5, '/products', '产品列表', '/login', 'Mozilla/5.0 (iPad; CPU OS 14_0)', '192.168.1.6', '澳大利亚', '悉尼', 'tablet', 'Safari', NOW() - INTERVAL 3 DAY);

-- 添加用户事件数据
INSERT INTO analytics_events (session_id, user_id, event_category, event_action, event_label, event_value, page_url, additional_data, created_at)
VALUES
-- 产品点击事件
('sess_001', 1, 'Product', 'view', '高性能游戏笔记本', NULL, '/products', '{"productId": 2}', NOW() - INTERVAL 29 DAY),
('sess_001', 1, 'Button', 'click', '添加到购物车', NULL, '/product/2', '{"productId": 2}', NOW() - INTERVAL 29 DAY),
('sess_001', 1, 'Ecommerce', 'add_to_cart', '高性能游戏笔记本', 1299, '/product/2', '{"productId": 2, "quantity": 1}', NOW() - INTERVAL 29 DAY),
('sess_001', 1, 'Ecommerce', 'begin_checkout', NULL, NULL, '/cart', NULL, NOW() - INTERVAL 29 DAY),
('sess_001', 1, 'Ecommerce', 'purchase', '订单完成', 1299, '/order/confirmation/1', '{"orderId": 1}', NOW() - INTERVAL 29 DAY),

-- 用户2的事件
('sess_002', 2, 'Product', 'view', '智能手表', NULL, '/products', '{"productId": 3}', NOW() - INTERVAL 25 DAY),
('sess_003', 2, 'Product', 'view', '超薄笔记本电脑', NULL, '/products', '{"productId": 1}', NOW() - INTERVAL 20 DAY),
('sess_003', 2, 'Ecommerce', 'add_to_cart', '超薄笔记本电脑', 999, '/product/1', '{"productId": 1, "quantity": 1}', NOW() - INTERVAL 20 DAY),

-- 用户4的事件
('sess_006', 4, 'Product', 'view', '无线蓝牙耳机', NULL, '/products', '{"productId": 5}', NOW() - INTERVAL 7 DAY),
('sess_006', 4, 'Ecommerce', 'add_to_cart', '无线蓝牙耳机', 129, '/product/5', '{"productId": 5, "quantity": 1}', NOW() - INTERVAL 7 DAY),
('sess_006', 4, 'Ecommerce', 'begin_checkout', NULL, NULL, '/cart', NULL, NOW() - INTERVAL 7 DAY),
('sess_006', 4, 'Ecommerce', 'purchase', '订单完成', 129, '/order/confirmation/2', '{"orderId": 2}', NOW() - INTERVAL 7 DAY),

-- 搜索事件
('sess_007', 5, 'Search', 'search', '笔记本', NULL, '/products', '{"searchTerm": "笔记本"}', NOW() - INTERVAL 3 DAY),
('sess_004', 3, 'Search', 'search', '手机', NULL, '/products', '{"searchTerm": "手机"}', NOW() - INTERVAL 15 DAY),
('sess_005', NULL, 'Search', 'search', '耳机', NULL, '/products', '{"searchTerm": "耳机"}', NOW() - INTERVAL 10 DAY),

-- 表单提交事件
('sess_007', 5, 'Form', 'submit', 'register-form', NULL, '/register', NULL, NOW() - INTERVAL 3 DAY),
('sess_007', 5, 'Form', 'submit', 'login-form', NULL, '/login', NULL, NOW() - INTERVAL 3 DAY),
('sess_005', NULL, 'Form', 'submit', 'login-form', NULL, '/login', NULL, NOW() - INTERVAL 10 DAY);

-- 添加性能数据
INSERT INTO analytics_performance (session_id, page_url, load_time, dom_content_loaded, first_contentful_paint, ttfb, created_at)
VALUES
-- 桌面设备的性能数据
('sess_001', '/', 1250.5, 850.2, 600.3, 150.2, NOW() - INTERVAL 29 DAY),
('sess_001', '/products', 1500.8, 980.5, 720.4, 180.3, NOW() - INTERVAL 29 DAY),
('sess_001', '/product/2', 1350.2, 920.4, 680.6, 160.5, NOW() - INTERVAL 29 DAY),
('sess_001', '/cart', 1100.3, 800.2, 550.1, 140.2, NOW() - INTERVAL 29 DAY),
('sess_001', '/cart/confirm', 1200.4, 850.3, 600.2, 150.4, NOW() - INTERVAL 29 DAY),
('sess_001', '/order/confirmation/1', 950.2, 700.1, 480.3, 120.5, NOW() - INTERVAL 29 DAY),

-- 移动设备的性能数据（通常加载时间更长）
('sess_002', '/', 2500.5, 1850.2, 1200.3, 350.2, NOW() - INTERVAL 25 DAY),
('sess_002', '/products', 2800.8, 1980.5, 1420.4, 380.3, NOW() - INTERVAL 25 DAY),
('sess_002', '/product/3', 2350.2, 1720.4, 1180.6, 320.5, NOW() - INTERVAL 25 DAY),

-- 平板设备的性能数据
('sess_007', '/', 1800.5, 1250.2, 900.3, 250.2, NOW() - INTERVAL 3 DAY),
('sess_007', '/products', 2100.8, 1480.5, 1020.4, 280.3, NOW() - INTERVAL 3 DAY),
('sess_007', '/register', 1550.2, 1120.4, 780.6, 220.5, NOW() - INTERVAL 3 DAY),
('sess_007', '/login', 1300.3, 920.2, 650.1, 190.2, NOW() - INTERVAL 3 DAY),
('sess_007', '/products', 1900.4, 1350.3, 950.2, 260.4, NOW() - INTERVAL 3 DAY);