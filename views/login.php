<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Athena Dorms Property Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-blue: #4F46E5;
            --primary-blue-dark: #4338CA;
            --primary-blue-light: #6366F1;
            --athena-pink: #e8788a;
            --athena-pink-light: #f5a3b0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #E0E7FF 0%, #C7D2FE 50%, #DDD6FE 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated Background Shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            overflow: hidden;
            z-index: 0;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.6;
            animation: float 20s ease-in-out infinite;
        }

        .bg-shape-1 {
            width: 600px;
            height: 600px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.15) 0%, rgba(99, 102, 241, 0.1) 100%);
            top: -200px;
            right: -200px;
            animation-delay: 0s;
        }

        .bg-shape-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, rgba(232, 120, 138, 0.15) 0%, rgba(245, 163, 176, 0.1) 100%);
            bottom: -100px;
            left: -100px;
            animation-delay: -5s;
        }

        .bg-shape-3 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(147, 197, 253, 0.08) 100%);
            top: 50%;
            left: 10%;
            animation-delay: -10s;
        }

        .bg-shape-4 {
            width: 200px;
            height: 200px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.12) 0%, rgba(129, 140, 248, 0.08) 100%);
            bottom: 20%;
            right: 15%;
            animation-delay: -15s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg) scale(1);
            }
            25% {
                transform: translateY(-20px) rotate(5deg) scale(1.02);
            }
            50% {
                transform: translateY(-10px) rotate(-3deg) scale(0.98);
            }
            75% {
                transform: translateY(-25px) rotate(3deg) scale(1.01);
            }
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15),
                        0 0 0 1px rgba(255, 255, 255, 0.5);
            padding: 48px 40px;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 32px;
        }

        .logo-container img {
            max-width: 160px;
            height: auto;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: #6B7280;
            margin-bottom: 32px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-label {
            display: block;
            font-weight: 600;
            font-size: 0.875rem;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 1.1rem;
            transition: color 0.2s ease;
        }

        .form-control {
            width: 100%;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 14px 16px 14px 48px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: #F9FAFB;
        }

        .form-control.password-input {
            padding-right: 48px;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
            background: #ffffff;
        }

        .form-control:focus + i,
        .input-wrapper:focus-within i {
            color: var(--primary-blue);
        }

        .form-control::placeholder {
            color: #9CA3AF;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
            border: none;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(79, 70, 229, 0.45);
            background: linear-gradient(135deg, var(--primary-blue-dark) 0%, #3730A3 100%);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login i {
            font-size: 1.1rem;
        }

        .alert {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 24px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            text-align: left;
        }

        .alert-danger {
            background-color: #FEF2F2;
            border: 1px solid #FECACA;
            color: #DC2626;
        }

        .alert-danger i {
            font-size: 1.1rem;
        }

        .footer-text {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #E5E7EB;
        }

        .footer-text p {
            font-size: 0.8rem;
            color: #9CA3AF;
            margin-bottom: 12px;
        }

        .powered-by {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 0.75rem;
            color: #9CA3AF;
        }

        .avax-logo {
            max-width: 36px;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }

        .avax-logo:hover {
            opacity: 1;
        }

        /* Password toggle */
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #6B7280;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: var(--primary-blue);
            background: rgba(79, 70, 229, 0.1);
        }

        .password-toggle i {
            font-size: 1.1rem;
        }

        /* Loading state */
        .btn-login.loading {
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-login.loading .btn-text {
            display: none;
        }

        .btn-login .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        .btn-login.loading .spinner {
            display: block;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 36px 24px;
                border-radius: 20px;
            }

            .logo-container img {
                max-width: 130px;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .form-control {
                padding: 12px 14px 12px 44px;
            }

            .form-control.password-input {
                padding-right: 44px;
            }

            .btn-login {
                padding: 14px;
            }
        }

        /* Focus visible for accessibility */
        .form-control:focus-visible,
        .btn-login:focus-visible {
            outline: 2px solid var(--primary-blue);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <!-- Animated Background Shapes -->
    <div class="bg-shapes">
        <div class="bg-shape bg-shape-1"></div>
        <div class="bg-shape bg-shape-2"></div>
        <div class="bg-shape bg-shape-3"></div>
        <div class="bg-shape bg-shape-4"></div>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="logo-container">
                <img src="/public/assets/images/athena_logo.png" alt="Athena Dorms">
            </div>

            <h1 class="login-title">Welcome Back</h1>
            <p class="login-subtitle">Sign in to manage your properties</p>

            <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-circle"></i>
                <span><?php echo e($error); ?></span>
            </div>
            <?php endif; ?>

            <form action="index.php?action=auth.do.login" method="POST" id="loginForm">
                <?php echo csrfField(); ?>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <div class="input-wrapper">
                        <input type="email" class="form-control" id="email" name="email"
                               placeholder="Enter your email" required autofocus>
                        <i class="bi bi-envelope"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input type="password" class="form-control password-input" id="password" name="password"
                               placeholder="Enter your password" required>
                        <i class="bi bi-lock"></i>
                        <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign In
                    </span>
                    <div class="spinner"></div>
                </button>
            </form>

            <div class="footer-text">
                <p>Athena Dorms Property Management System</p>
                <div class="powered-by">
                    <span>Powered by</span>
                    <img src="/public/assets/images/avax_logo.png" alt="AvaxTech Solutions" class="avax-logo">
                    <span>AvaxTech Solutions</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        // Form submission loading state
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('loginBtn');
            btn.classList.add('loading');
        });
    </script>
</body>
</html>
