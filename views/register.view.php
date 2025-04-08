<?php require_once __DIR__ . '/header.php'; ?>

<link rel="stylesheet" href="/../assets/css/register.css">

<div class="register-container">
    <div class="register-card">
        <h1 class="register-title">创建新账户</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register" class="register-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="username">用户名</label>
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" 
                           class="register-input" 
                           id="username" 
                           name="username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">电子邮箱</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" 
                           class="register-input" 
                           id="email" 
                           name="email" 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                           required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">密码</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           class="register-input" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="6">
                </div>
                <div class="password-requirements">密码长度至少6个字符</div>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">确认密码</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           class="register-input" 
                           id="confirm_password" 
                           name="confirm_password" 
                           required 
                           minlength="6">
                </div>
            </div>

            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> 注册账户
            </button>
        </form>

        <div class="login-link">
            已有账户？<a href="/login">点击这里登录</a>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    const form = document.querySelector('.register-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    form.addEventListener('submit', function(event) {
        let isValid = true;

        // 清除之前的错误提示
        document.querySelectorAll('.error-message').forEach(el => el.remove());

        // 检查必填字段
        form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                showError(input, '此字段不能为空');
            }
        });

        // 验证邮箱格式
        const email = document.getElementById('email');
        if (email.value && !isValidEmail(email.value)) {
            isValid = false;
            showError(email, '请输入有效的电子邮箱地址');
        }

        // 检查密码长度
        if (password.value.length < 6) {
            isValid = false;
            showError(password, '密码长度至少6个字符');
        }

        // 检查密码匹配
        if (password.value !== confirmPassword.value) {
            isValid = false;
            showError(confirmPassword, '两次输入的密码不匹配');
        }

        if (!isValid) {
            event.preventDefault();
        }
    });

    function showError(input, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '0.25rem';
        errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
        input.parentNode.appendChild(errorDiv);
        input.classList.add('error');
    }

    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>