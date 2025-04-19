// Prometheus 连接测试脚本
async function testPrometheusConnection() {
    console.log('开始测试 Prometheus 连接...');
    
    try {
        const metric = 'prometheus_test{status="test"} 1';
        
        // 尝试使用容器名称
        let response = await fetch('http://pushgateway:9091/metrics/job/connection_test', {
            method: 'POST',
            headers: { 'Content-Type': 'text/plain' },
            body: metric,
        });
        
        if (response.ok) {
            console.log('✅ 使用容器名称连接成功!');
            return;
        }
        
        console.log('❌ 使用容器名称连接失败，状态码:', response.status);
        console.log('尝试使用 localhost...');
        
        // 尝试使用 localhost
        response = await fetch('http://localhost:9091/metrics/job/connection_test', {
            method: 'POST',
            headers: { 'Content-Type': 'text/plain' },
            body: metric,
        });
        
        if (response.ok) {
            console.log('✅ 使用 localhost 连接成功!');
            console.log('请在 analytics.js 中将 pushgateway 改为 localhost');
            return;
        }
        
        console.log('❌ 使用 localhost 连接也失败，状态码:', response.status);
        console.log('请检查 Prometheus pushgateway 是否正在运行');
        
    } catch (error) {
        console.error('❌ 连接测试出错:', error);
        console.log('请确保 docker-compose 已启动且配置正确');
    }
}

// 执行测试
testPrometheusConnection();
