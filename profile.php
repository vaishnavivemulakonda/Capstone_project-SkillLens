<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../config/db.php";

$db = new Database();
$conn = $db->connect();

$resume_id = $_SESSION['active_resume_id'] ?? 0;
$user_id = $_SESSION['user_id'];

if ($resume_id > 0) {
    $totalDays = 30;

    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS completed
         FROM daily_progress
         WHERE user_id = ? AND resume_id = ? AND completed = 1"
    );
    $stmt->bind_param("ii", $user_id, $resume_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $completedDays = (int)($row['completed'] ?? 0);
    $progressPercent = round(($completedDays / $totalDays) * 100);
} else {
    $completedDays = 0;
    $progressPercent = 0;
}

$roleStmt = $conn->prepare(
    "SELECT job_role
     FROM resume_analysis
     WHERE user_id = ?
     ORDER BY id DESC
     LIMIT 1"
);
$roleStmt->bind_param("i", $user_id);
$roleStmt->execute();
$jobRole = $roleStmt->get_result()->fetch_assoc()['job_role'] ?? 'Not selected';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | SkillLens</title>
    <link rel="stylesheet" href="/resume-skill-analyzer/assets/css/style.css">
</head>

<body class="has-fixed-footer">

<!-- HEADER -->
<?php include '../includes/dashboard_header.php'; ?>

<!-- MAIN -->
<div class="app-bg">
    <div class="app-card">

        <!-- PAGE TITLE -->
        <h2 class="dashboard-title">👤 My Profile</h2>
        <p class="dashboard-subtitle">Your learning & progress overview</p>

        <div class="dashboard-grid">

        <!-- BASIC INFORMATION CARD -->
<div class="card-box">
<div class="profile-grid"></div>
    <!-- BASIC INFORMATION CARD -->
    <div class="card-box">

        <h3 class="card-title">📄 Basic Information</h3>

        <div class="profile-info-row" style="display:flex;justify-content:space-between;">
            <span class="profile-label">Name</span>
            <span class="profile-value">
                <?= htmlspecialchars($_SESSION['user_name']); ?>
            </span>
        </div>

        <div class="profile-info-row" style="display:flex;justify-content:space-between;margin-top:12px;">
            <span class="profile-label">Target Role</span>
            <span class="profile-value accent">
                <?= htmlspecialchars($jobRole); ?>
            </span>
        </div>

    </div>

    <!-- UPSKILLING / LEARNING PROGRESS CARD (MOVED BELOW) -->
    <div class="card-box">

        <h3 class="card-title">📊 Learning Progress</h3>

        <p style="margin:6px 0;">
            <?= $progressPercent ?>% completed
        </p>

        <div style="
            background:#020617;
            border-radius:8px;
            overflow:hidden;
            height:10px;
            margin-top:10px;
        ">
            <div style="
                width: <?= $progressPercent ?>%;
                height: 100%;
                background: linear-gradient(90deg,#22c55e,#4ade80);
            "></div>
        </div>

        <p style="margin-top:8px;color:#9CA3AF;">
            <?= $completedDays ?> / <?= $totalDays ?> days completed
        </p>

    </div>

</div>

<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>

</body>
</html>