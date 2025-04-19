import requests
import random
import time
import json
from faker import Faker
import uuid
from concurrent.futures import ThreadPoolExecutor

# 配置
BASE_URL = "http://localhost"  # 替换为您的服务地址
PUSHGATEWAY_URL = "http://localhost:9091/metrics/job/web_traffic"
fake = Faker()

# 浏览器类型
BROWSERS = ["Chrome", "Firefox", "Safari", "Edge", "Opera"]

# 设备类型
DEVICE_TYPES = ["desktop", "mobile", "tablet"]

# 产品分类
PRODUCT_CATEGORIES = ["Accessories Series", "Study Essentials", "Commemorative Gifts", "Clothes", "Books"]

# 产品信息（ID, 名称, 价格, 分类）
PRODUCTS = [
    (1, "PolyU T-Shirt", 199, "Clothes"),
    (2, "PolyU Notebook", 49, "Study Essentials"),
    (3, "PolyU Water Bottle", 89, "Accessories Series"),
    (4, "PolyU Graduation Frame", 299, "Commemorative Gifts"),
    (5, "PolyU Backpack", 399, "Accessories Series"),
    (6, "PolyU Pen Set", 129, "Study Essentials"),
    (7, "PolyU Hoodie", 299, "Clothes"),
    (8, "PolyU History Book", 249, "Books"),
    (9, "PolyU Keychain", 39, "Accessories Series"),
    (10, "PolyU Calendar", 79, "Study Essentials")
]

# 生成会话ID
def generate_session_id():
    return str(uuid.uuid4())

# 从Pushgateway获取当前指标值
def get_current_metric_value(metric_name, labels):
    try:
        # 构建标签字符串（用于匹配响应中的指标）
        label_str = ",".join([f'{key}="{value}"' for key, value in labels.items()])
        
        # 确定要查找的指标完整名称
        metric_full_name = metric_name
        if metric_name == "pageview_total":
            metric_full_name = "pageview_total_counter"
        elif metric_name == "event_total" and labels.get("event_type") == "add_to_cart":
            metric_full_name = "cart_add_total_counter"
        elif metric_name == "event_total":
            metric_full_name = "event_total_counter"
        
        # 发送GET请求获取所有指标
        response = requests.get(PUSHGATEWAY_URL)
        if response.status_code != 200:
            print(f"获取当前指标值失败: {response.status_code} - {response.text}")
            return 0
        
        # 解析响应，查找匹配的指标
        import re
        metric_pattern = f"{metric_full_name}\\{{{re.escape(label_str)}\\}} ([0-9.]+)"
        match = re.search(metric_pattern, response.text)
        if match:
            return float(match.group(1))
        
        return 0  # 未找到指标时返回0
    except Exception as e:
        print(f"获取指标值异常: {str(e)}")
        return 0

# 向Prometheus推送指标
def push_metric(metric_name, value, labels):
    try:
        # 构建标签字符串
        label_str = ",".join([f'{key}="{value}"' for key, value in labels.items()])
        
        # 确定指标完整名称
        metric_full_name = metric_name
        if metric_name == "pageview_total":
            metric_full_name = "pageview_total_counter"
        elif metric_name == "event_total" and labels.get("event_type") == "add_to_cart":
            metric_full_name = "cart_add_total_counter"
        elif metric_name == "event_total":
            metric_full_name = "event_total_counter"
        
        # 获取当前值并累加
        current_value = 0
        if metric_name in ["pageview_total", "event_total"]:
            current_value = get_current_metric_value(metric_name, labels)
            value += current_value  # 累加当前值
            print(f"指标 {metric_full_name} 累加：当前值 {current_value} + 新增 {value-current_value} = {value}")
        
        # 构建指标字符串
        metric = f"{metric_full_name}{{{label_str}}} {value}\n"

        # 发送指标到Pushgateway
        response = requests.post(PUSHGATEWAY_URL, data=metric.encode('utf-8'))
        if response.status_code != 200:
            print(f"Error pushing metric: {response.status_code} - {response.text}")
            return False
        return True
    except Exception as e:
        print(f"Error pushing metric: {str(e)}")
        return False

# 模拟用户注册
def simulate_user_registration(session_data):
    username = fake.user_name()
    email = fake.email()
    password = "password123"
    
    try:
        response = requests.post(f"{BASE_URL}/register", data={
            "username": username,
            "email": email,
            "password": password,
            "confirm_password": password
        })
        
        # 更新会话数据
        if response.status_code == 200:
            print(f"用户注册成功: {username}")
            
            # 推送事件指标
            push_metric("event_total_counter", 1, {
                "category": "User",
                "action": "register", 
                "label": username,
                "browser": session_data["browser"],
                "deviceType": session_data["device_type"]
            })
            
            return username, password
        else:
            print(f"用户注册失败: {response.text}")
            return None, None
    except Exception as e:
        print(f"注册异常: {str(e)}")
        return None, None

# 模拟用户登录
def simulate_user_login(username, password, session_data):
    try:
        response = requests.post(f"{BASE_URL}/login", data={
            "username": username,
            "password": password
        })
        
        if response.status_code == 200:
            print(f"用户登录成功: {username}")
            
            # 推送事件指标
            push_metric("event_total", 1, {
                "category": "User",
                "action": "login", 
                "label": username,
                "browser": session_data["browser"],
                "deviceType": session_data["device_type"]
            })
            
            return response.cookies
        else:
            print(f"用户登录失败: {response.text}")
            return None
    except Exception as e:
        print(f"登录异常: {str(e)}")
        return None

# 模拟页面访问
def simulate_pageview(cookies, url, session_data):
    try:
        start_time = time.time()
        response = requests.get(f"{BASE_URL}{url}", cookies=cookies)
        load_time = int((time.time() - start_time) * 1000)  # 毫秒
        
        if response.status_code == 200:
            print(f"页面访问: {url}")
            
            # 推送页面访问指标
            push_metric("pageview_total", 1, {
                "url": url,
                "referrer": session_data.get("last_url", "direct"),
                "deviceType": session_data["device_type"],
                "browser": session_data["browser"]
            })
            
            # 推送性能指标
            push_metric("performance_metrics", 1, {
                "pageUrl": url,
                "loadTime": str(load_time),
                "deviceType": session_data["device_type"],
                "browser": session_data["browser"]
            })
            
            # 更新最后访问的URL
            session_data["last_url"] = url
            return True
        else:
            print(f"页面访问失败: {response.text}")
            return False
    except Exception as e:
        print(f"页面访问异常: {str(e)}")
        return False

# 模拟浏览产品
def simulate_browse_products(cookies, session_data):
    # 模拟访问产品列表页
    if not simulate_pageview(cookies, "/products", session_data):
        return
    
    time.sleep(random.uniform(0.1, 0.5))
    
    # 模拟浏览分类
    category = random.choice(PRODUCT_CATEGORIES)
    print(f"浏览产品分类: {category}")
    
    # 推送事件指标 - 点击分类
    push_metric("event_total", 1, {
        "category": "Button",
        "action": "click", 
        "label": category,
        "browser": session_data["browser"],
        "deviceType": session_data["device_type"]
    })
    
    time.sleep(random.uniform(0.1, 0.5))
    
    # 模拟随机浏览2-5个产品
    browse_count = random.randint(2, 5)
    viewed_products = []
    
    for _ in range(browse_count):
        product = random.choice(PRODUCTS)
        product_id, product_name, price, product_category = product
        
        if product_category == category or random.random() < 0.3:  # 30%概率浏览其他分类产品
            viewed_products.append(product)
            simulate_pageview(cookies, f"/product/{product_id}", session_data)
            
            # 推送事件指标 - 查看产品
            push_metric("event_total", 1, {
                "category": "Product",
                "action": "view", 
                "label": product_name,
                "value": str(price),
                "browser": session_data["browser"],
                "deviceType": session_data["device_type"]
            })
            
            time.sleep(random.uniform(0.1, 0.5))
    
    return viewed_products

# 模拟添加到购物车
def simulate_add_to_cart(cookies, products, session_data):
    try:
        if not products:
            print("No products to add to cart")
            return
            
        # 随机选择1-3个产品添加到购物车，但不超过可用产品数量
        num_products = min(random.randint(1, 3), len(products))
        selected_products = random.sample(products, num_products)
        
        for product in selected_products:
            product_id, product_name, price, category = product
            
            # 构建添加到购物车的数据
            cart_data = {
                "product_id": product_id,
                "quantity": random.randint(1, 3)
            }
            
            # 发送添加到购物车的请求
            response = requests.post(
                f"{BASE_URL}/cart/add",
                json=cart_data,
                cookies=cookies
            )
            
            if response.status_code == 200:
                # 推送购物车添加事件指标
                push_metric("event_total", 1, {
                    "event_type": "add_to_cart",
                    "product_name": product_name,
                    "product_category": category,
                    "price": str(price),
                    "session_id": session_data["session_id"],
                    "device_type": session_data["device_type"],
                    "browser": session_data["browser"]
                })
                
                print(f"Added product {product_name} to cart")
                time.sleep(random.uniform(1, 3))
            else:
                print(f"Failed to add product {product_name} to cart")
    except Exception as e:
        print(f"Error in simulate_add_to_cart: {str(e)}")

# 模拟结账流程
def simulate_checkout(cookies, session_data):
    # 访问确认页面
    simulate_pageview(cookies, "/cart/confirm", session_data)
    
    # 推送事件指标 - 开始结账
    push_metric("event_total", 1, {
        "category": "Ecommerce",
        "action": "begin_checkout",
        "browser": session_data["browser"],
        "deviceType": session_data["device_type"]
    })
    
    time.sleep(random.uniform(0.1, 0.5))
    
    # 80%的概率完成购买
    if random.random() < 0.8:
        order_id = fake.random_number(digits=8)
        simulate_pageview(cookies, f"/order/confirmation/{order_id}", session_data)
        
        # 推送事件指标 - 完成购买
        push_metric("event_total", 1, {
            "category": "Ecommerce",
            "action": "purchase",
            "label": f"Order #{order_id}",
            "browser": session_data["browser"],
            "deviceType": session_data["device_type"]
        })

# 模拟搜索行为
def simulate_search(cookies, session_data):
    search_terms = ["T-shirt", "Pen", "Book", "Bag", "Gift", "PolyU", "Graduation", "Student", "Academic"]
    search_term = random.choice(search_terms)
    
    print(f"搜索: {search_term}")
    
    # 推送事件指标 - 搜索
    push_metric("event_total", 1, {
        "category": "Search",
        "action": "query", 
        "label": search_term,
        "browser": session_data["browser"],
        "deviceType": session_data["device_type"]
    })
    
    # 假设搜索是通过GET请求，带查询参数
    simulate_pageview(cookies, f"/search?q={search_term}", session_data)
    
    time.sleep(random.uniform(0.1, 0.5))

# 模拟单个用户的整个行为流程
def simulate_user_journey():
    # 创建会话数据
    session_data = {
        "session_id": generate_session_id(),
        "browser": random.choice(BROWSERS),
        "device_type": random.choice(DEVICE_TYPES),
        "last_url": "direct"
    }
    
    print(f"开始模拟用户旅程: {session_data['browser']} on {session_data['device_type']}")
    
    # 首先访问首页
    simulate_pageview(None, "/", session_data)
    
    time.sleep(random.uniform(0.1, 0.5))
    
    # 有30%概率直接浏览产品，不注册/登录
    if random.random() < 0.3:
        print("游客模式浏览")
        viewed_products = simulate_browse_products(None, session_data)
        
        # 游客有10%概率会注册
        if random.random() < 0.1:
            username, password = simulate_user_registration(session_data)
            if username:
                cookies = simulate_user_login(username, password, session_data)
                if cookies and viewed_products:
                    simulate_add_to_cart(cookies, viewed_products, session_data)
        return
    
    # 70%的用户会注册或登录
    # 40%是新用户，60%是老用户(模拟)
    if random.random() < 0.4:
        username, password = simulate_user_registration(session_data)
    else:
        username = fake.user_name()
        password = "password123"
        print(f"模拟已存在用户: {username}")
    
    if not username:
        return
    
    cookies = simulate_user_login(username, password, session_data)
    if not cookies:
        return
    
    # 确定用户行为路径
    actions = []
    
    # 添加产品浏览行为
    actions.append(lambda: simulate_browse_products(cookies, session_data))
    
    # 添加搜索行为 (50%概率)
    if random.random() < 0.5:
        actions.append(lambda: simulate_search(cookies, session_data))
    
    # 随机排序行为顺序
    random.shuffle(actions)
    
    # 执行行为
    viewed_products = []
    for action in actions:
        result = action()
        if isinstance(result, list):
            viewed_products.extend(result)
        time.sleep(random.uniform(0.1, 0.5))
    
    # 如果浏览了产品，可能会添加到购物车
    if viewed_products and random.random() < 0.7:  # 70%概率添加到购物车
        simulate_add_to_cart(cookies, viewed_products, session_data)
    
    # 随机访问一些额外页面
    extra_pages = ["/about", "/contact", "/faq", "/", "/products"]
    for _ in range(random.randint(0, 2)):
        page = random.choice(extra_pages)
        simulate_pageview(cookies, page, session_data)
        time.sleep(random.uniform(0.1, 0.5))

# 使用多线程模拟多个并发用户
def simulate_multiple_users(user_count):
    print(f"开始模拟 {user_count} 个用户...")
    
    with ThreadPoolExecutor(max_workers=10) as executor:
        for _ in range(user_count):
            executor.submit(simulate_user_journey)
            # 稍微延迟以避免完全同步的请求
            time.sleep(random.uniform(0.1, 0.5))

# 主函数
def main():
    user_count = 50  # 增加模拟用户数量
    simulate_multiple_users(user_count)
    print("模拟完成！")

if __name__ == "__main__":
    main()