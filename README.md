# COMP3421-25-P2

为了使用Grafana，采用docker部署

```docker

docker-compose up -d

```

网站: http://localhost

PHPMyAdmin: http://localhost:8080

Grafana: http://localhost:3000


# Grafana数据可视化创建指南

## 前期准备

1. 登录Grafana（http://localhost:3000）
    用户名：admin
    密码：admin
2. 首先添加MySQL数据源：
   - 点击左侧菜单的"Configuration" > "Data Sources"
   - 点击"Add data source"
   - 选择"MySQL"
   - 填写配置信息：
     - Host: db:3306（Docker环境）或localhost:3306（本地环境）
     - Database: online_store
     - User: root
     - Password: root_password（或您设置的密码）
   - 点击"Save & Test"确认连接成功

## 1. 每日访问量趋势图

1. 创建新仪表板：点击"+"图标 > "Dashboard"
2. 添加新面板：点击"Add panel"
3. 选择数据源为MySQL
4. 在查询编辑器中输入SQL：
   ```sql
   SELECT 
     DATE(created_at) as time,
     COUNT(*) as "访问量"
   FROM analytics_pageviews
   WHERE created_at >= $__timeFrom() AND created_at <= $__timeTo()
   GROUP BY DATE(created_at)
   ORDER BY time
   ```
5. 在"Visualization"选项卡中，选择"Time series"（时间序列）
6. 设置标题为"每日访问量趋势"
7. 保存面板

## 2. 用户地理分布热图

1. 添加新面板
2. 选择数据源为MySQL
3. 在查询编辑器中输入SQL：
   ```sql
   SELECT 
     country as "国家",
     COUNT(*) as "访问量"
   FROM analytics_pageviews
   WHERE created_at >= $__timeFrom() AND created_at <= $__timeTo()
   GROUP BY country
   ORDER BY "访问量" DESC
   ```
4. 在"Visualization"选项卡中，选择"Geomap"（地图）
5. 在地图设置中：
   - 选择"Countries"层
   - 将"Location Field"设为"国家"
   - 将"Value Field"设为"访问量"
6. 设置标题为"用户地理分布"
7. 保存面板

## 3. 设备类型分布饼图

1. 添加新面板
2. 选择数据源为MySQL
3. 在查询编辑器中输入SQL：
   ```sql
   SELECT 
     device_type as "设备类型",
     COUNT(*) as "访问量"
   FROM analytics_pageviews
   WHERE created_at >= $__timeFrom() AND created_at <= $__timeTo()
   GROUP BY device_type
   ```
4. 在"Visualization"选项卡中，选择"Pie chart"（饼图）
5. 在饼图设置中：
   - 将"Labels"设置为"Value"或"Name"
   - 启用"Legend"（图例）
6. 设置标题为"设备类型分布"
7. 保存面板

## 4. 浏览器使用情况条形图

1. 添加新面板
2. 选择数据源为MySQL
3. 在查询编辑器中输入SQL：
   ```sql
   SELECT 
     browser as "浏览器",
     COUNT(*) as "访问量"
   FROM analytics_pageviews
   WHERE created_at >= $__timeFrom() AND created_at <= $__timeTo()
   GROUP BY browser
   ORDER BY "访问量" DESC
   ```
4. 在"Visualization"选项卡中，选择"Bar chart"（条形图）
5. 设置标题为"浏览器使用情况"
6. 保存面板

## 5. 页面性能对比图表

1. 添加新面板
2. 选择数据源为MySQL
3. 在查询编辑器中输入SQL：
   ```sql
   SELECT 
     page_url as "页面",
     AVG(load_time) as "页面加载时间",
     AVG(dom_content_loaded) as "DOM加载时间",
     AVG(first_contentful_paint) as "首次内容绘制",
     AVG(ttfb) as "首字节时间"
   FROM analytics_performance
   WHERE created_at >= $__timeFrom() AND created_at <= $__timeTo()
   GROUP BY page_url
   ORDER BY "页面加载时间" DESC
   ```
4. 在"Visualization"选项卡中，选择"Bar chart"
5. 在设置中将"Orientation"设为"horizontal"
6. 设置标题为"页面性能对比"
7. 保存面板

## 6. 用户浏览路径漏斗图

1. 添加新面板
2. 选择数据源为MySQL
3. 创建多个查询，每个代表漏斗的一个步骤：
   
   查询A（首页访问）:
   ```sql
   SELECT 
     COUNT(*) as "首页访问"
   FROM analytics_pageviews
   WHERE url = '/'
   AND created_at >= $__timeFrom() AND created_at <= $__timeTo()
   ```
   
   查询B（产品列表）:
   ```sql
   SELECT 
     COUNT(*) as "产品列表"
   FROM analytics_pageviews
   WHERE url = '/products'
   AND created_at >= $__timeFrom() AND created_at <= $__timeTo()
   ```
   
   查询C（产品详情）:
   ```sql
   SELECT 
     COUNT(*) as "产品详情"
   FROM analytics_pageviews
   WHERE url LIKE '/product/%'
   AND created_at >= $__timeFrom() AND created_at <= $__timeTo()
   ```
   
   查询D（购物车）:
   ```sql
   SELECT 
     COUNT(*) as "购物车"
   FROM analytics_pageviews
   WHERE url = '/cart'
   AND created_at >= $__timeFrom() AND created_at <= $__timeTo()
   ```
   
   查询E（结账）:
   ```sql
   SELECT 
     COUNT(*) as "结账"
   FROM analytics_pageviews
   WHERE url = '/cart/confirm'
   AND created_at >= $__timeFrom() AND created_at <= $__timeTo()
   ```
   
   查询F（完成订单）:
   ```sql
   SELECT 
     COUNT(*) as "完成订单"
   FROM analytics_pageviews
   WHERE url LIKE '/order/confirmation/%'
   AND created_at >= $__timeFrom() AND created_at <= $__timeTo()
   ```
   
4. 在"Visualization"选项卡中，选择"Bar gauge"或安装"Grafana Funnel插件"
5. 设置标题为"用户浏览路径漏斗"
6. 保存面板

## 7. 转化率分析

1. 添加新面板
2. 选择数据源为MySQL
3. 在查询编辑器中输入SQL：
   ```sql
   SELECT 
     'PV到购买转化率' as "指标",
     (
       SELECT COUNT(DISTINCT session_id)
       FROM analytics_events
       WHERE event_category = 'Ecommerce' AND event_action = 'purchase'
       AND created_at >= $__timeFrom() AND created_at <= $__timeTo()
     ) * 100.0 / 
     (
       SELECT COUNT(DISTINCT session_id)
       FROM analytics_pageviews
       WHERE created_at >= $__timeFrom() AND created_at <= $__timeTo()
     ) as "转化率"
   ```
4. 添加更多查询来计算不同阶段的转化率
5. 在"Visualization"选项卡中，选择"Stat"或"Gauge"
6. 设置标题为"转化率分析"
7. 保存面板

## 8. 搜索词云图

1. 添加新面板
2. 选择数据源为MySQL
3. 在查询编辑器中输入SQL：
   ```sql
   SELECT 
     event_label as "搜索词",
     COUNT(*) as "搜索次数"
   FROM analytics_events
   WHERE event_category = 'Search' AND event_action = 'search'
   AND created_at >= $__timeFrom() AND created_at <= $__timeTo()
   GROUP BY event_label
   ORDER BY "搜索次数" DESC
   ```
4. 在"Visualization"选项卡中，您需要安装"Word Cloud Plugin"插件：
   - 通过左侧菜单的"Configuration" > "Plugins"安装
   - 搜索并安装"Word Cloud"插件
5. 然后选择"Word Cloud"可视化
6. 设置标题为"热门搜索词"
7. 保存面板

## 最后步骤

1. 安排面板布局：拖动调整大小和位置
2. 设置仪表板时间范围：右上角选择适当的时间范围（如"Last 30 days"）
3. 保存仪表板：点击右上角的保存图标，命名为"电商网站分析仪表板"
4. 设置自动刷新：右上角设置适当的刷新间隔

这样您就完成了一个全面的电商网站分析仪表板，能够直观地展示网站的访问情况、用户行为和性能指标。
