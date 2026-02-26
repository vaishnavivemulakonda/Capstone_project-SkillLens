<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once "../config/db.php";
require_once "../services/YouTubeService.php";

$user_id = (int) $_SESSION['user_id'];

/* ------------------------------------
   DB CONNECTION
------------------------------------ */
$db   = new Database();
$conn = $db->connect();

/* ------------------------------------
   RESUME ID (AUTO-FALLBACK)
------------------------------------ */
$resume_id = $_SESSION['active_resume_id'] ?? 0;

if ($resume_id <= 0) {
    $stmt = $conn->prepare(
        "SELECT resume_id
         FROM resume_analysis
         WHERE user_id = ?
         ORDER BY id DESC
         LIMIT 1"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        die("❌ Please analyze your resume first.");
    }

    $resume_id = (int)$row['resume_id'];
    $_SESSION['active_resume_id'] = $resume_id;
}

/* ------------------------------------
   DAY NAVIGATION
------------------------------------ */
$requestedDay = isset($_GET['day']) ? (int)$_GET['day'] : 0;

/* ------------------------------------
   LAST COMPLETED DAY
------------------------------------ */
$stmt = $conn->prepare(
    "SELECT MAX(day_number) AS last_day
     FROM daily_progress
     WHERE user_id = ? AND resume_id = ? AND completed = 1"
);
$stmt->bind_param("ii", $user_id, $resume_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

$lastCompletedDay = (int)($row['last_day'] ?? 0);

/* ------------------------------------
   CURRENT DAY LOGIC
------------------------------------ */
if ($requestedDay > 0 && $requestedDay <= $lastCompletedDay + 1) {
    $dayNumber = $requestedDay;
} else {
    $dayNumber = $lastCompletedDay + 1;
}

$alreadyCompleted = ($dayNumber <= $lastCompletedDay);

/* ------------------------------------
   FETCH AI ROADMAP
------------------------------------ */
$stmt = $conn->prepare(
    "SELECT ai_result
     FROM resume_analysis
     WHERE user_id = ? AND resume_id = ?
     ORDER BY id DESC
     LIMIT 1"
);
$stmt->bind_param("ii", $user_id, $resume_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row || empty($row['ai_result'])) {
    die("❌ Roadmap not found for this resume.");
}

$roadmap = json_decode($row['ai_result'], true);

if (!isset($roadmap['subjects']) || !is_array($roadmap['subjects'])) {
    die("❌ Invalid roadmap format.");
}

/* ------------------------------------
   BUILD DAY → TOPIC MAP (KEY FIX)
------------------------------------ */
$dayTopics = [];

foreach ($roadmap['subjects'] as $subject => $days) {
    if (!is_array($days)) continue;

    foreach ($days as $dayLabel => $task) {
        if (preg_match('/Day\s*(\d+)/i', $dayLabel, $m)) {
            $dayIndex = (int)$m[1];
            $dayTopics[$dayIndex] = trim($task);
        }
    }
}

ksort($dayTopics);

/* ------------------------------------
   RESOLVE TODAY TOPIC (100% DYNAMIC)
------------------------------------ */
if (!isset($dayTopics[$dayNumber])) {
    die("❌ No upskilling plan found for Day $dayNumber.");
}

$topic = $dayTopics[$dayNumber];
/* ------------------------------------
   YOUTUBE SEARCH (DYNAMIC & SAFE)
------------------------------------ */
$youtubeId = null; // ✅ FIX: prevent undefined variable

$yt = new YouTubeService();
$videos = $yt->search($topic . " tutorial for beginners");

if (is_array($videos) && isset($videos[0]['videoId'])) {
    $youtubeId = $videos[0]['videoId'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Day <?= $dayNumber ?> Progress | SkillLens</title>
<link rel="stylesheet" href="/resume-skill-analyzer/assets/css/style.css">

<style>
.video-box iframe { border-radius: 14px; }

.progress-bar {
    height: 10px;
    background: #020617;
    border-radius: 6px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-fill {
    height: 100%;
    width: 0%;
    background: linear-gradient(90deg,#22c55e,#4ade80);
}

.complete-btn {
    margin-top: 20px;
    width: 100%;
    padding: 14px;
    border-radius: 30px;
    border: none;
    font-weight: 600;
    background: #22c55e;
    color: #022c22;
    opacity: 0.5;
    cursor: not-allowed;
}

.complete-btn.enabled {
    opacity: 1;
    cursor: pointer;
}
</style>
</head>

<body>

<?php include "../includes/dashboard_header.php"; ?>

<div class="app-bg">
<div class="app-card">

<h2>🔥 Day <?= $dayNumber ?> – Upskilling Plan</h2>
<p class="subtitle"><?= htmlspecialchars($topic) ?></p>

<?php if ($youtubeId): ?>
<div class="video-box">
    <iframe
        id="player"
        width="100%"
        height="360"
        src="https://www.youtube.com/embed/<?= htmlspecialchars($youtubeId) ?>?enablejsapi=1"
        allowfullscreen>
    </iframe>
</div>

<?php else: ?>
<div class="card-box">❌ No video found for this topic.</div>
<?php endif; ?>

<?php if ($alreadyCompleted): ?>
<div class="card-box"
     style="border:1px solid #22c55e;display:flex;justify-content:space-between;align-items:center;">
    <span>🎉 Day <?= $dayNumber ?> completed</span>
    <a href="progress.php?day=<?= $dayNumber + 1 ?>"
       class="dashboard-cta"
       style="width:auto;padding:10px 24px;">
        👉 Day <?= $dayNumber + 1 ?>
    </a>
</div>
<?php else: ?>
<form method="POST" action="save_progress.php">
    <input type="hidden" name="day_number" value="<?= $dayNumber ?>">
    <input type="hidden" name="resume_id" value="<?= $resume_id ?>">
    <button
    id="completeBtn"
    class="daily-complete-btn"
    onclick="completeDay()"
>
    ✅ Mark Day <?= $dayNumber ?> As Completed
</button>
</form>
<?php endif; ?>

</div>
</div>

<script src="https://www.youtube.com/iframe_api"></script>
<script>
let player, duration = 0;

function onYouTubeIframeAPIReady() {
    player = new YT.Player('player', {
        events: { 'onStateChange': onPlayerStateChange }
    });
}

function onPlayerStateChange(e) {
    if (e.data === YT.PlayerState.PLAYING) {
        duration = player.getDuration();
        setInterval(trackProgress, 1000);
    }
}

function trackProgress() {
    if (!player || duration === 0) return;

    let watched = player.getCurrentTime();
    let percent = Math.floor((watched / duration) * 100);

    document.getElementById("progressFill").style.width = percent + "%";

    if (percent >= 70) {
        const btn = document.getElementById("completeBtn");
        btn.disabled = false;
        btn.classList.remove("disabled");
        btn.classList.add("enabled");
    }
}
</script>
<script>
function completeDay() {
    const btn = document.getElementById("completeBtn");

    if (btn.disabled) {
        alert("Please watch at least 70% of the video to mark this day as completed.");
        return;
    }

    // Submit the form or redirect
    document.getElementById("completeDayForm").submit();
}
</script>

</body>
</html>