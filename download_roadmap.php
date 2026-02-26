<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    exit("Unauthorized");
}

require_once "../config/db.php";

if (!isset($_GET['rid'])) {
    exit("Invalid request");
}

$user_id   = $_SESSION['user_id'];
$resume_id = (int) $_GET['rid'];

$db   = new Database();
$conn = $db->connect();

/* Fetch AI roadmap */
$stmt = $conn->prepare(
    "SELECT job_role, ai_result
     FROM resume_analysis
     WHERE resume_id = ? AND user_id = ?"
);
$stmt->bind_param("ii", $resume_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$analysis = $result->fetch_assoc();

if (!$analysis) {
    exit("No roadmap found");
}

$aiData = json_decode($analysis['ai_result'], true);

if (!isset($aiData['subjects'])) {
    exit("Invalid roadmap data");
}

/* Build text file */
$content  = "SkillLens – 30 Day Learning Roadmap\n";
$content .= "Job Role: " . $analysis['job_role'] . "\n\n";

foreach ($aiData['subjects'] as $subject => $days) {
    $content .= strtoupper($subject) . "\n";
    $content .= str_repeat("-", 40) . "\n";

    foreach ($days as $day => $task) {
        $content .= $day . ": " . $task . "\n";
    }
    $content .= "\n";
}

/* Force download */
$fileName = "SkillLens_Roadmap_" . preg_replace("/\s+/", "_", $analysis['job_role']) . ".txt";

header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=\"$fileName\"");
header("Content-Length: " . strlen($content));

echo $content;
exit;