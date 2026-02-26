<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../config/db.php";

/* ===============================
   VALIDATE RESUME ID
   =============================== */
if (!isset($_GET['rid']) || !is_numeric($_GET['rid'])) {
    die("Invalid analysis request");
}

$user_id   = $_SESSION['user_id'];
$resume_id = (int) $_GET['rid'];

/* ===============================
   FETCH ANALYSIS DATA
   =============================== */
$db   = new Database();
$conn = $db->connect();

$stmt = $conn->prepare(
    "SELECT job_role, ai_result
     FROM resume_analysis
     WHERE resume_id = ? AND user_id = ?
     LIMIT 1"
);
$stmt->bind_param("ii", $resume_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$analysis = $result->fetch_assoc();

if (!$analysis) {
    die("Analysis not found");
}

/* ===============================
   DECODE AI JSON
   =============================== */
$roadmap = json_decode($analysis['ai_result'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analysis Result | SkillLens</title>
    <link rel="stylesheet" href="/resume-skill-analyzer/assets/css/style.css">
</head>

<body>

<?php include '../includes/header.php'; ?>

<div class="app-bg analysis-page">
    <div class="app-card">

        <!-- TITLE -->
        <h2 class="dashboard-title">📊 Resume Skill Analysis</h2>
        <p class="dashboard-subtitle">
            AI-powered roadmap based on your resume
        </p>

        <!-- JOB ROLE CARD (EXACT MATCH) -->
<div class="card-box job-role-card" style="
    display:flex;
    align-items:center;
    justify-content:space-between;
    padding:22px 26px;
">

    <!-- LEFT SIDE -->
    <div>
        <h3 class="card-title" style="margin-bottom:4px;">
            🎯 Job Role
        </h3>
        <p class="card-text" style="margin:0;">
            <?= htmlspecialchars($analysis['job_role']); ?>
        </p>
    </div>

    <!-- RIGHT SIDE -->
    <a href="download_roadmap.php?rid=<?= (int)$resume_id; ?>"
       class="dashboard-cta"
       style="
           width:auto;
           padding:10px 22px;
           font-size:14px;
           border-radius:12px;
           box-shadow:none;
       ">
        ⬇ Download
    </a>

</div>

        <!-- ROADMAP -->
        <?php if (!empty($roadmap['subjects'])): ?>
            <?php foreach ($roadmap['subjects'] as $subject => $days): ?>
                <div class="card-box roadmap-week">
                    <h3>📘 <?= htmlspecialchars($subject); ?></h3>
                    <ul class="roadmap-list">
                        <?php foreach ($days as $day => $task): ?>
                            <li>
                                <strong><?= htmlspecialchars($day); ?>:</strong>
                                <?= htmlspecialchars($task); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="card-text">No roadmap data available.</p>
        <?php endif; ?>

        <!-- ACTION -->
        <div class="analysis-actions">
            <a href="dashboard.php" class="dashboard-cta"
            style="
           width:auto;
           padding:10px 22px;
           font-size:14px;
           border-radius:12px;
           box-shadow:none;
       ">
            Back to Dashboard
            </a>
        </div>

    </div>
</div>
</body>
</html>