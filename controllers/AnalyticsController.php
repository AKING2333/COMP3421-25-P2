<?php
require_once __DIR__ . '/../class/Database.php';
require_once __DIR__ . '/../class/SessionController.php';

class AnalyticsController {
    private static $pushgatewayUrl = "http://pushgateway:9091/metrics/job/web_traffic";

    /**
     * Get current metric value from Pushgateway
     * @param string $metricName metric name
     * @param array $labels label set
     * @return float current value, returns 0 if not found
     */
    private static function getCurrentMetricValue($metricName, $labels) {
        try {
            // Build label string (for matching metrics in response)
            $labelPairs = [];
            foreach ($labels as $key => $val) {
                $labelPairs[] = $key . '="' . str_replace('"', '\\"', $val) . '"';
            }
            $labelString = implode(',', $labelPairs);
            
            // Determine full metric name to look for
            $metricFullName = $metricName;
            if ($metricName === "pageview_total") {
                $metricFullName = "pageview_total_counter";
            } elseif ($metricName === "event_total" && isset($labels['event_type']) && $labels['event_type'] === 'add_to_cart') {
                $metricFullName = "cart_add_total_counter";
            } elseif ($metricName === "product_cart_add") {
                $metricFullName = "product_cart_add_counter";
            } elseif ($metricName === "event_total") {
                $metricFullName = "event_total_counter";
            } elseif ($metricName === "pageview_total_simple") {
                $metricFullName = "pageview_total_simple";
            } elseif ($metricName === "product_cart_add_simple") {
                $metricFullName = "product_cart_add_simple";
            }
            
            // Send GET request to get all metrics
            $ch = curl_init(self::$pushgatewayUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("Failed to get current metric value: $httpCode - $response");
                return 0; // Return 0 on failure
            }
            
            // Parse response to find matching metric - use a more robust approach
            error_log("Looking for metric: {$metricFullName} with labels: {$labelString}");
            
            // First try exact match with all labels
            $metricPattern = "/{$metricFullName}\\{" . preg_quote($labelString, '/') . "\\} ([0-9.]+)/";
            if (preg_match($metricPattern, $response, $matches)) {
                error_log("Found exact match with value: " . $matches[1]);
                return floatval($matches[1]);
            }
            
            // If exact match fails, try to find metric with simplified matching
            // For simple metrics with no labels, try matching just by metric name
            if (in_array($metricName, ["pageview_total_simple", "product_cart_add_simple", "product_cart_add_total"])) {
                // Special handling for simple metrics - match just by metric name (without labels)
                $simplePattern = "/{$metricFullName}(\\{[^}]*\\})? ([0-9.]+)/";
                if (preg_match($simplePattern, $response, $matches)) {
                    error_log("Found simple match with value: " . $matches[2]);
                    return floatval($matches[2]);
                }
            }
            
            // For metrics that track totals, try matching just by metric name with any labels
            if (in_array($metricName, ["pageview_total", "event_total", "product_cart_add"])) {
                // Simplified pattern - match by metric name with any labels
                $simplePattern = "/{$metricFullName}\\{[^}]*\\} ([0-9.]+)/";
                if (preg_match_all($simplePattern, $response, $matches)) {
                    // Get all values and sum them up
                    $sum = 0;
                    foreach ($matches[1] as $value) {
                        $sum += floatval($value);
                    }
                    error_log("Found multiple matches, sum: " . $sum);
                    return $sum;
                }
            }
            
            // For product-specific metrics, try to match by product_id or product_name
            if ($metricName === "product_cart_add" && (isset($labels['product_id']) || isset($labels['product_name']))) {
                $productIdentifier = isset($labels['product_id']) ? "product_id=\"" . $labels['product_id'] . "\"" : 
                                    (isset($labels['product_name']) ? "product_name=\"" . $labels['product_name'] . "\"" : "");
                
                if (!empty($productIdentifier)) {
                    $productPattern = "/{$metricFullName}\\{[^}]*" . preg_quote($productIdentifier, '/') . "[^}]*\\} ([0-9.]+)/";
                    if (preg_match($productPattern, $response, $matches)) {
                        error_log("Found product specific match with value: " . $matches[1]);
                        return floatval($matches[1]);
                    }
                }
            }
            
            error_log("No matching metric found.");
            return 0; // Return 0 if metric not found
        } catch (Exception $e) {
            error_log("Exception getting metric value: " . $e->getMessage());
            return 0; // Return 0 on exception
        }
    }

    // Push metrics to Prometheus
    private static function pushMetric($metricName, $value, $labels) {
        try {
            // Build label string
            $labelStr = [];
            foreach ($labels as $key => $val) {
                $labelStr[] = $key . '="' . str_replace('"', '\\"', $val) . '"';
            }
            $labelString = implode(',', $labelStr);
            
            // Determine full metric name
            $metricFullName = $metricName;
            if ($metricName === "pageview_total") {
                $metricFullName = "pageview_total_counter";
            } elseif ($metricName === "event_total" && isset($labels['event_type']) && $labels['event_type'] === 'add_to_cart') {
                $metricFullName = "cart_add_total_counter";
            } elseif ($metricName === "product_cart_add") {
                $metricFullName = "product_cart_add_counter";
            } elseif ($metricName === "event_total") {
                $metricFullName = "event_total_counter";
            }
            
            // Get current value and add to it
            $currentValue = 0;
            if (in_array($metricName, ["pageview_total", "event_total", "product_cart_add", "pageview_total_simple", "product_cart_add_simple", "product_cart_add_total"])) {
                $currentValue = self::getCurrentMetricValue($metricName, $labels);
                $value += $currentValue; // Add current value
                error_log("Metric {$metricFullName} accumulated: current value {$currentValue} + new {$value} = " . ($currentValue + $value));
            }
            
            // Build metric string
            $metric = "{$metricFullName}{{$labelString}} {$value}\n";
            
            // Send to Pushgateway
            $ch = curl_init(self::$pushgatewayUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $metric);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                error_log("Failed to push metric: $httpCode - $response");
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Exception pushing metric: " . $e->getMessage());
            return false;
        }
    }

    // Track page views
    public static function trackPageview() {
        header('Content-Type: application/json');
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                error_log("Invalid JSON data: " . $input);
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                return;
            }
            
            $session = SessionController::getInstance();
            $userId = $session->getUser() ? $session->getUser()->id : null;
            
            // Push page view metric with all labels for detailed analysis
            self::pushMetric("pageview_total", 1, [
                'url' => $data['url'] ?? $_SERVER['REQUEST_URI'],
                'referrer' => $data['referrer'] ?? ($_SERVER['HTTP_REFERER'] ?? 'direct'),
                'device_type' => $data['device_type'] ?? 'unknown',
                'browser' => $data['browser'] ?? $_SERVER['HTTP_USER_AGENT'],
                'user_id' => $userId ?? 'anonymous'
            ]);
            
            // Push a simple metric with no labels for total count
            // This is the one used in the dashboard
            self::pushMetric("pageview_total_simple", 1, []);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("Page view tracking exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }

    // Track events
    public static function trackEvent() {
        header('Content-Type: application/json');
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                error_log("Invalid event data: " . $input);
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                return;
            }

            // Push event metric with all labels
            self::pushMetric("event_total", 1, [
                'event_type' => $data['event_type'] ?? 'unknown',
                'event_category' => $data['category'] ?? 'unknown',
                'event_action' => $data['action'] ?? 'unknown',
                'event_label' => $data['label'] ?? '',
                'device_type' => $data['device_type'] ?? 'unknown',
                'browser' => $data['browser'] ?? $_SERVER['HTTP_USER_AGENT']
            ]);
            
            // Push a simple metric by event type
            self::pushMetric("event_total_by_type", 1, [
                'event_type' => $data['event_type'] ?? 'unknown'
            ]);

            // Track specific product metrics for cart additions
            if (isset($data['event_type']) && $data['event_type'] === 'add_to_cart' && isset($data['label'])) {
                // 确保产品ID和类别ID总是有值
                $productId = isset($data['product_id']) ? $data['product_id'] : '0';
                $categoryId = isset($data['category_id']) ? $data['category_id'] : 'unknown';
                
                // Push product-specific metric with detailed labels
                self::pushMetric("product_cart_add", 1, [
                    'product_id' => $productId,
                    'product_name' => $data['label'],
                    'category_id' => $categoryId
                ]);
                
                // Push a simple product metric by name only
                // This is the one used in the "Most Popular Products" panel
                self::pushMetric("product_cart_add_simple", 1, [
                    'product_name' => $data['label']
                ]);
                
                // Push a total count for all cart additions
                self::pushMetric("product_cart_add_total", 1, []);
            }

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("Event tracking exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }

    // Track performance metrics
    public static function trackPerformance() {
        header('Content-Type: application/json');
        
        try {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            if (!$data) {
                error_log("Invalid performance data: " . $input);
                echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
                return;
            }

            // Push performance metric (performance metrics are usually instantaneous values rather than cumulative, so no need to add up)
            self::pushMetric("page_load_time", $data['load_time'] ?? 0, [
                'page_url' => $data['page_url'] ?? $_SERVER['REQUEST_URI'],
                'device_type' => $data['device_type'] ?? 'unknown',
                'browser' => $data['browser'] ?? $_SERVER['HTTP_USER_AGENT']
            ]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            error_log("Performance tracking exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }
    
    // Get analytics data for API
    public static function getAnalyticsData() {
        header('Content-Type: application/json');
        
        try {
            // Get the most popular products (most added to cart)
            $db = Database::getInstance()->getPDO();
            $stmt = $db->prepare("
                SELECT p.id, p.name, p.category_id, c.name as category_name, COUNT(*) as add_count
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN categories c ON p.category_id = c.id
                GROUP BY p.id
                ORDER BY add_count DESC
                LIMIT 10
            ");
            $stmt->execute();
            $popularProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return the data
            echo json_encode([
                'success' => true,
                'popular_products' => $popularProducts
            ]);
        } catch (Exception $e) {
            error_log("Analytics data exception: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Internal server error']);
        }
    }
}