<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | SkillLens</title>
    <link rel="stylesheet" href="/resume-skill-analyzer/assets/css/style.css">
</head>

<body class="auth-page has-fixed-footer">

<!-- HEADER -->
<div class="app-header">
    <div class="logo">
        <span>◆</span> SkillLens
    </div>
</div>

<!-- AUTH MAIN -->
<div class="auth-main">
    <div class="auth-wrapper">

        <!-- LEFT DIAGONAL PANEL -->
        <div class="auth-left">
            <h1>WELCOME!</h1>
        </div>

        <!-- RIGHT FORM PANEL -->
        <div class="auth-right">
            <h2>Login</h2>

            <form method="POST" action="../controllers/AuthController.php">

                <div class="auth-input">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>

                <div class="auth-input">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>

                <button type="submit" name="login" class="auth-btn">
                    Login
                </button>

                <p class="auth-switch">
                    Don’t have an account?
                    <a href="signup.php">Register</a>
                </p>

            </form>
        </div>

    </div>
</div>

<!-- FOOTER -->
<div class="app-footer">
    © <?= date('Y'); ?> SkillLens. All rights reserved.
</div>

</body>
</html>