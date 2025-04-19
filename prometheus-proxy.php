<?php
// Prometheus 代理脚本，解决跨域问题

// 允许跨域请求
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 记录请求开始
error_log("Prometheus代理请求开始处理");

// 预检请求处理
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 只接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// 确保设置正确的Content-Type
header('Content-Type: text/plain');

try {
    // 获取传入的数据
    $data = file_get_contents("php://input");
    
    // 记录接收到的数据
    error_log("Prometheus代理收到的数据: " . $data);
    
    // 获取job名称，默认为web_traffic
    $job = isset($_GET['job']) ? $_GET['job'] : 'web_traffic';
    
    // 记录Pushgateway URL
    $pushgateway_url = "http://pushgateway:9091/metrics/job/" . $job;
    error_log("推送到Prometheus的URL: " . $pushgateway_url);
    
    // 设置cURL会话
    $ch = curl_init($pushgateway_url);
    
    // 设置cURL选项
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: text/plain',
        'Content-Length: ' . strlen($data)
    ));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10秒超时
    
    // 执行请求
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // 记录响应结果
    error_log("Pushgateway响应状态码: " . $httpCode);
    error_log("Pushgateway响应内容: " . $result);
    
    // 检查错误
    if (curl_errno($ch)) {
        throw new Exception('cURL错误: ' . curl_error($ch));
    }
    
    // 关闭cURL会话
    curl_close($ch);
    
    // 检查HTTP状态码
    if ($httpCode >= 200 && $httpCode < 300) {
        echo "数据成功推送到Prometheus";
    } else {
        throw new Exception('Pushgateway返回错误状态码: ' . $httpCode . ', 响应: ' . $result);
    }
} catch (Exception $e) {
    // 记录错误
    error_log("Prometheus代理错误: " . $e->getMessage());
    
    // 返回错误
    http_response_code(500);
    echo "推送到Prometheus时出错: " . $e->getMessage();
} 