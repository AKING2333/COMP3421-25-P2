<?php
class AnalyticsController {
    // 记录页面访问
    public static function trackPageview() {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = SessionController::getSessionUserId();
        $sessionId = session_id();
        
        $db = Database::getInstance()->getPDO();
        $stmt = $db->prepare("INSERT INTO analytics_pageviews (session_id, user_id, url, page_title, 
                                referrer, user_agent, ip_address, country, city, device_type, browser) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $sessionId,
            $userId,
            $data['url'] ?? $_SERVER['REQUEST_URI'],
            $data['title'] ?? '',
            $data['referrer'] ?? ($_SERVER['HTTP_REFERER'] ?? ''),
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['REMOTE_ADDR'] ?? '',
            $data['country'] ?? '',
            $data['city'] ?? '',
            $data['deviceType'] ?? '',
            $data['browser'] ?? ''
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
    }
    
    // 记录用户事件
    public static function trackEvent() {
        $data = json_decode(file_get_contents('php://input'), true);
        $userId = SessionController::getSessionUserId();
        $sessionId = session_id();
        
        $db = Database::getInstance()->getPDO();
        $stmt = $db->prepare("INSERT INTO analytics_events (session_id, user_id, event_category, 
                                event_action, event_label, event_value, page_url, additional_data) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $sessionId,
            $userId,
            $data['category'],
            $data['action'],
            $data['label'] ?? null,
            $data['value'] ?? null,
            $data['pageUrl'] ?? $_SERVER['REQUEST_URI'],
            isset($data['additionalData']) ? json_encode($data['additionalData']) : null
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
    }
    
    // 记录性能指标
    public static function trackPerformance() {
        $data = json_decode(file_get_contents('php://input'), true);
        $sessionId = session_id();
        
        $db = Database::getInstance()->getPDO();
        $stmt = $db->prepare("INSERT INTO analytics_performance (session_id, page_url, load_time, 
                                dom_content_loaded, first_contentful_paint, ttfb) 
                            VALUES (?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $sessionId,
            $data['pageUrl'] ?? $_SERVER['REQUEST_URI'],
            $data['loadTime'] ?? null,
            $data['domContentLoaded'] ?? null,
            $data['firstContentfulPaint'] ?? null,
            $data['ttfb'] ?? null
        ]);
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
    }
}