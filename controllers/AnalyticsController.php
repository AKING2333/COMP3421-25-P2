<?php
class AnalyticsController {
    private static $pushgatewayUrl = "http://pushgateway:9091/metrics/job/web_traffic";

    /**
     * 从Pushgateway获取当前指标值
     * @param string $metricName 指标名称
     * @param array $labels 标签集
     * @return float 当前值，如果未找到则返回0
     */
    private static function getCurrentMetricValue($metricName, $labels) {
        try {
            // 构建标签字符串（用于匹配响应中的指标）
            $labelPairs = [];
            foreach ($labels as $key => $val) {
                $labelPairs[] = $key . '="' . str_replace('"', '\\"', $val) . '"';
            }
            $labelString = implode(',', $labelPairs);
            
            // 确定要查找的指标完整名称
            $metricFullName = $metricName;
            if ($metricName === "pageview_total") {
                $metricFullName = "pageview_total_counter";
            } elseif ($metricName === "event_total" && isset($labels['event_type']) && $labels['event_type'] === 'add_to_cart') {
                $metricFullName = "cart_add_total_counter";
            } elseif ($metricName === "event_total") {
                $metricFullName = "event_total_counter";
            }
            
            // 发送GET请求获取所有指标
            $ch = curl_init(self::$pushgatewayUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("获取当前指标值失败: $httpCode - $response");
                return 0; // 失败时返回0
            }
            
            // 解析响应，查找匹配的指标
            $metricPattern = "/{$metricFullName}\\{" . preg_quote($labelString, '/') . "\\} ([0-9.]+)/";
            if (preg_match($metricPattern, $response, $matches)) {
                return floatval($matches[1]);
            }
            
            return 0; // 未找到指标时返回0
        } catch (Exception $e) {
            error_log("获取指标值异常: " . $e->getMessage());
            return 0; // 发生异常时返回0
        }
    }

    // 推送指标到Prometheus
    private static function pushMetric($metricName, $value, $labels) {
        try {
            // 构建标签字符串
            $labelStr = [];
            foreach ($labels as $key => $val) {
                $labelStr[] = $key . '="' . str_replace('"', '\\"', $val) . '"';
            }
            $labelString = implode(',', $labelStr);
            
            // 确定指标完整名称
            $metricFullName = $metricName;
            if ($metricName === "pageview_total") {
                $metricFullName = "pageview_total_counter";
            } elseif ($metricName === "event_total" && isset($labels['event_type']) && $labels['event_type'] === 'add_to_cart') {
                $metricFullName = "cart_add_total_counter";
            } elseif ($metricName === "event_total") {
                $metricFullName = "event_total_counter";
            }
            
            // 获取当前值并累加
            $currentValue = 0;
            if (in_array($metricName, ["pageview_total", "event_total"])) {
                $currentValue = self::getCurrentMetricValue($metricName, $labels);
                $value += $currentValue; // 累加当前值
                error_log("指标 {$metricFullName} 累加：当前值 {$currentValue} + 新增 1 = {$value}");
            }
            
            // 构建指标字符串
            $metric = "{$metricFullName}{{$labelString}} {$value}\n";
            
            // 发送到Pushgateway
            $ch = curl_init(self::$pushgatewayUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $metric);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("推送指标失败: $httpCode - $response");
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("推送指标异常: " . $e->getMessage());
            return false;
        }
    }

    // 记录页面访问
    public static function trackPageview() {
        header('Content-Type: application/json');
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                error_log("无效的JSON数据: " . $input);
                echo json_encode(['success' => false, 'error' => '无效的JSON数据']);
                return;
            }
            
            $session = SessionController::getInstance();
            $userId = $session->getUser() ? $session->getUser()->id : null;
            
            // 推送页面访问指标
            self::pushMetric("pageview_total", 1, [
                'url' => $data['url'] ?? $_SERVER['REQUEST_URI'],
                'referrer' => $data['referrer'] ?? ($_SERVER['HTTP_REFERER'] ?? 'direct'),
                'device_type' => $data['device_type'] ?? 'unknown',
                'browser' => $data['browser'] ?? $_SERVER['HTTP_USER_AGENT'],
                'user_id' => $userId ?? 'anonymous'
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("页面访问跟踪异常: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => '内部服务器错误']);
        }
    }

    // 记录事件
    public static function trackEvent() {
        header('Content-Type: application/json');
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                error_log("无效的事件数据: " . $input);
                echo json_encode(['success' => false, 'error' => '无效的JSON数据']);
                return;
            }

            // 推送事件指标
            self::pushMetric("event_total", 1, [
                'event_type' => $data['event_type'] ?? 'unknown',
                'event_category' => $data['category'] ?? 'unknown',
                'event_action' => $data['action'] ?? 'unknown',
                'event_label' => $data['label'] ?? '',
                'device_type' => $data['device_type'] ?? 'unknown',
                'browser' => $data['browser'] ?? $_SERVER['HTTP_USER_AGENT']
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("事件跟踪异常: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => '内部服务器错误']);
        }
    }

    // 记录性能指标
    public static function trackPerformance() {
        header('Content-Type: application/json');
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                error_log("无效的性能数据: " . $input);
                echo json_encode(['success' => false, 'error' => '无效的JSON数据']);
                return;
            }

            // 推送性能指标 (性能指标通常是瞬时值而非累加，所以不需要累加)
            self::pushMetric("page_load_time", $data['load_time'] ?? 0, [
                'page_url' => $data['page_url'] ?? $_SERVER['REQUEST_URI'],
                'device_type' => $data['device_type'] ?? 'unknown',
                'browser' => $data['browser'] ?? $_SERVER['HTTP_USER_AGENT']
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("性能跟踪异常: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => '内部服务器错误']);
        }
    }
}