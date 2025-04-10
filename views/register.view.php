<?php require_once __DIR__ . '/header.php'; ?>

<link rel="stylesheet" href="/../assets/css/register.css">

<div class="register-container">
    <div class="register-card">
        <h1 class="register-title">create an account</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="/register" class="register-form" novalidate>
            <div class="form-group">
                <label class="form-label" for="username">username</label>
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
                <label class="form-label" for="email">email</label>
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

            <div id="email-error" style="color: red;"></div>

            <div class="form-group">
                <label class="form-label" for="password">password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           class="register-input" 
                           id="password" 
                           name="password" 
                           required 
                           minlength="6">
                </div>
                
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm_password">confirm password</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" 
                           class="register-input" 
                           id="confirm_password" 
                           name="confirm_password" >
                </div>
            </div>

            <button type="submit" class="register-btn">
                <i class="fas fa-user-plus"></i> register
            </button>
        </form>

        <div class="login-link">
            have an account?<a href="/login">click here to login</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
(function() {
    'use strict';
    const form = document.querySelector('.register-form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    $('#email').on('blur', function() {
        var email = $(this).val();
        $.ajax({
            url: '/check-email',
            method: 'POST',
            data: { email: email },
            success: function(response) {
                if (response.exists) {
                    $('#email-error').text('This email is already registered. Please use a different email.');
                } else {
                    $('#email-error').text('');
                }
            }
        });
    });

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

        // 验证邮箱格式
        const email = document.getElementById('email');
        if (email.value && !isValidEmail(email.value)) {
            isValid = false;
            showError(email, 'please enter a valid email address');
        }

        // 检查密码长度
        if (password.value.length < 4) {
            isValid = false;
            showError(password, 'password must be at least 4 characters long');
        }

        // 检查密码匹配
        if (password.value !== confirmPassword.value) {
            isValid = false;
            showError(confirmPassword, 'not match');
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