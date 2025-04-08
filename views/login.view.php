<?php require_once __DIR__ . '/header.php'; ?>

<link rel="stylesheet" href="/assets/css/login.css">

<div class="login-container">
    <div class="login-card">
        <h1 class="login-title">login</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login" class="login-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="username">username</label>
                <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" 
                           class="login-input" 
                           id="username" 
                           name="username" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
                           required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           class="login-input" 
                           id="password" 
                           name="password" 
                           required>
                </div>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> login
            </button>
        </form>

        <div class="register-link">
            Don't have an account?<a href="/register">click here to register</a>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';
    const form = document.querySelector('.login-form');

    form.addEventListener('submit', function(event) {
        let isValid = true;

        // 清除之前的错误提示
        document.querySelectorAll('.error-message').forEach(el => el.remove());

        // 检查必填字段
        form.querySelectorAll('input[required]').forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                showError(input, 'this field cannot be empty');
            }
        });

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
})();
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
