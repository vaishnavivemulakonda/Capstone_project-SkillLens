<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | SkillLens</title>
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
            <h2>Register</h2>

            <form method="POST" action="../controllers/AuthController.php">

                <div class="auth-input">
                    <input type="text" name="name" required>
                    <label>Username</label>
                </div>

                <div class="auth-input">
                    <input type="email" name="email" required>
                    <label>Email</label>
                </div>

                <div class="auth-input">
                    <input type="password" name="password" required>
                    <label>Password</label>
                </div>

                <div class="auth-input">
                    <select name="role" required>
                        <option value="" disabled selected></option>
                        <option>Student</option>
                        <option>Software Developer</option>
                        <option>Data Analyst</option>
                        <option>Web Developer</option>
                    </select>
                    <label>Role</label>
                </div>

                <button type="submit" name="signup" class="auth-btn">
                    Register
                </button>

                <p class="auth-switch">
                    Already have an account?
                    <a href="login.php">Sign in</a>
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