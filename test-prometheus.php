<?php
// Prometheus 连接测试脚本

// 启用错误显示（仅开发环境）
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Prometheus 连接测试</h1>";

// 1. 测试环境变量
echo "<h2>1. 环境变量</h2>";
$host = getenv('PROMETHEUS_HOST') ?: 'pushgateway';
$port = getenv('PROMETHEUS_PORT') ?: '9091';
echo "PROMETHEUS_HOST: " . htmlspecialchars($host) . "<br>";
echo "PROMETHEUS_PORT: " . htmlspecialchars($port) . "<br>";

// 2. 测试 DNS 解析
echo "<h2>2. DNS 解析测试</h2>";
$ip = gethostbyname($host);
echo "解析 {$host} 到 IP: " . htmlspecialchars($ip) . "<br>";
if ($ip === $host) {
    echo "<span style='color:red'>DNS 解析失败!</span><br>";
} else {
    echo "<span style='color:green'>DNS 解析成功!</span><br>";
}

// 3. 测试 TCP 连接
echo "<h2>3. TCP 连接测试</h2>";
$socket = @fsockopen($host, $port, $errno, $errstr, 5);
if (!$socket) {
    echo "<span style='color:red'>TCP 连接失败: $errstr ($errno)</span><br>";
} else {
    echo "<span style='color:green'>TCP 连接成功!</span><br>";
    fclose($socket);
}

// 4. 测试 HTTP 连接
echo "<h2>4. HTTP 连接测试</h2>";
$url = "http://{$host}:{$port}/metrics";
echo "请求 URL: " . htmlspecialchars($url) . "<br>";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "状态码: " . htmlspecialchars($statusCode) . "<br>";
if ($error) {
    echo "<span style='color:red'>HTTP 错误: " . htmlspecialchars($error) . "</span><br>";
} else {
    echo "<span style='color:green'>HTTP 连接成功!</span><br>";
    echo "响应长度: " . strlen($response) . " 字节<br>";
    echo "<div style='background:#f0f0f0; padding:10px; max-height:200px; overflow:auto;'>";
    echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . (strlen($response) > 500 ? '...' : '') . "</pre>";
    echo "</div>";
}

// 5. 测试推送指标
echo "<h2>5. 测试推送指标</h2>";
$pushUrl = "http://{$host}:{$port}/metrics/job/test_job";
echo "推送 URL: " . htmlspecialchars($pushUrl) . "<br>";

// 格式化指标数据，确保结尾有换行符
$metric = "test_metric{instance=\"test\",job=\"test_job\"} " . time() . "\n";
echo "指标数据: " . htmlspecialchars($metric) . "<br>";

// 显示数据的十六进制值，用于调试
echo "指标数据(十六进制): ";
for ($i = 0; $i < strlen($metric); $i++) {
    echo sprintf("%02x ", ord($metric[$i]));
}
echo "<br>";

$ch = curl_init($pushUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $metric);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/plain']);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
// 添加详细的调试信息
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// 显示详细的请求信息
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
echo "<div style='background:#f8f8f8; padding:10px; margin:10px 0; font-size:12px; overflow:auto;'>";
echo "<strong>详细请求日志:</strong><br>";
echo "<pre>" . htmlspecialchars($verboseLog) . "</pre>";
echo "</div>";

echo "状态码: " . htmlspecialchars($statusCode) . "<br>";
if ($error) {
    echo "<span style='color:red'>推送错误: " . htmlspecialchars($error) . "</span><br>";
} elseif ($statusCode >= 200 && $statusCode < 300) {
    echo "<span style='color:green'>推送成功!</span><br>";
} else {
    echo "<span style='color:red'>推送失败! 状态码: " . htmlspecialchars($statusCode) . "</span><br>";
    echo "<div style='background:#f0f0f0; padding:10px; max-height:200px; overflow:auto;'>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    echo "</div>";
}

// 6. 显示 PHP 信息
echo "<h2>6. PHP 环境信息</h2>";
echo "PHP 版本: " . phpversion() . "<br>";
echo "cURL 版本: " . curl_version()['version'] . "<br>";
echo "服务器软件: " . $_SERVER['SERVER_SOFTWARE'] . "<br>";
?> 