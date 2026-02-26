<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="app-header">
    <div class="logo">
        <span>◆</span> SkillLens
    </div>

    <nav class="header-nav">
        <!-- LEFT NAV LINKS -->
        <div class="nav-links">
            <a href="/resume-skill-analyzer/public/progress.php">
                Progress
            </a>
        </div>

        <!-- PROFILE DROPDOWN -->
        <div class="profile-wrapper">
    <div class="profile-trigger" onclick="toggleProfileMenu(event)">
        <span class="profile-icon">👤</span>
        <span class="profile-name">
            <?= htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?>
        </span>
    </div>

    <div class="profile-menu" id="profileMenu">
        <a href="/resume-skill-analyzer/public/profile.php">My Profile</a>
        <a href="/resume-skill-analyzer/public/logout.php">🚪 Logout</a>
    </div>
</div>
    </nav>
</header>

<script>
function toggleProfileMenu(e) {
    e.stopPropagation();
    document.getElementById("profileMenu").classList.toggle("show");
}

document.addEventListener("click", function () {
    const menu = document.getElementById("profileMenu");
    if (menu) {
        menu.classList.remove("show");
    }
});
</script>